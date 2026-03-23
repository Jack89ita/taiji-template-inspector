<?php

if (!defined('ABSPATH')) {
  exit;
}

function taiji_get_current_language() {
  if (defined('ICL_SITEPRESS_VERSION')) {
    return apply_filters('wpml_current_language', null);
  }

  if (function_exists('pll_current_language')) {
    return pll_current_language('slug');
  }

  return null;
}

function taiji_get_available_languages() {
  $languages = array();

  if (defined('ICL_SITEPRESS_VERSION')) {
    $wpml_languages = apply_filters('wpml_active_languages', null, array('skip_missing' => 0));

    if (!empty($wpml_languages) && is_array($wpml_languages)) {
      foreach ($wpml_languages as $lang) {
        if (empty($lang['code'])) {
          continue;
        }

        $languages[$lang['code']] = !empty($lang['native_name'])
          ? $lang['native_name']
          : $lang['code'];
      }
    }
  } elseif (function_exists('pll_the_languages')) {
    $pll_languages = pll_the_languages(array(
      'raw' => 1,
      'hide_if_empty' => 0,
    ));

    if (!empty($pll_languages) && is_array($pll_languages)) {
      foreach ($pll_languages as $lang) {
        if (empty($lang['slug'])) {
          continue;
        }

        $languages[$lang['slug']] = !empty($lang['name'])
          ? $lang['name']
          : $lang['slug'];
      }
    }
  }

  return $languages;
}

function taiji_get_templates() {
  $templates = wp_get_theme()->get_page_templates();
  $templates['default'] = __('Default Template', 'taiji-template-inspector');

  return $templates;
}

function taiji_get_cache_key($lang = null) {
  $blog_id = get_current_blog_id();
  $lang    = $lang ?: 'all';

  return 'taiji_template_usage_' . $blog_id . '_' . sanitize_key($lang);
}

function taiji_get_language_sql_parts($lang) {
  global $wpdb;

  $join  = '';
  $where = '';

  if (!$lang) {
    return array($join, $where);
  }

  if (defined('ICL_SITEPRESS_VERSION')) {
    $join = " INNER JOIN {$wpdb->prefix}icl_translations taiji_lang
                  ON p.ID = taiji_lang.element_id ";

    $where = $wpdb->prepare(
      " AND taiji_lang.language_code = %s ",
      $lang
    );
  } elseif (function_exists('pll_current_language')) {
    $join = " INNER JOIN {$wpdb->term_relationships} taiji_tr ON p.ID = taiji_tr.object_id
                  INNER JOIN {$wpdb->term_taxonomy} taiji_tt ON taiji_tr.term_taxonomy_id = taiji_tt.term_taxonomy_id
                  INNER JOIN {$wpdb->terms} taiji_t ON taiji_tt.term_id = taiji_t.term_id ";

    $where = $wpdb->prepare(
      " AND taiji_tt.taxonomy = 'language'
              AND taiji_t.slug = %s ",
      $lang
    );
  }

  return array($join, $where);
}

/**
 * Query ottimizzata:
 * - una sola query
 * - normalizza default template (NULL, '', 'default')
 * - conta per template + post_type
 * - usa COUNT(DISTINCT p.ID)
 */
function taiji_get_usage_data($lang = null) {
  global $wpdb;

  if ($lang === null || $lang === '') {
    $lang = taiji_get_current_language();
  }

  $cache_key = taiji_get_cache_key($lang);
  $cached    = get_transient($cache_key);

  if ($cached !== false) {
    return $cached;
  }

  list($lang_join, $lang_where) = taiji_get_language_sql_parts($lang);

  $sql = "
        SELECT
            CASE
                WHEN pm.meta_value IS NULL OR pm.meta_value = '' OR pm.meta_value = 'default'
                    THEN 'default'
                ELSE pm.meta_value
            END AS template_key,
            p.post_type,
            COUNT(DISTINCT p.ID) AS usage_count
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm
            ON p.ID = pm.post_id
            AND pm.meta_key = '_wp_page_template'
        {$lang_join}
        WHERE p.post_status IN ('publish', 'draft', 'private')
          AND p.post_type NOT IN ('revision', 'nav_menu_item')
          {$lang_where}
        GROUP BY template_key, p.post_type
    ";

  /**
   * Direct SQL query used intentionally for performance.
   * Aggregates template usage across post types in a single query.
   */
  $results = $wpdb->get_results($sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter

  $counts = array();
  $types  = array();

  if (!empty($results)) {
    foreach ($results as $row) {
      $template_key = !empty($row->template_key) ? $row->template_key : 'default';
      $post_type    = !empty($row->post_type) ? $row->post_type : 'unknown';
      $usage_count  = intval($row->usage_count);

      if (!isset($counts[$template_key])) {
        $counts[$template_key] = 0;
      }

      if (!isset($types[$template_key])) {
        $types[$template_key] = array();
      }

      $counts[$template_key] += $usage_count;
      $types[$template_key][$post_type] = $usage_count;
    }
  }

  $data = array(
    'counts' => $counts,
    'types'  => $types,
  );

  $cache_ttl = (int) apply_filters('taiji_cache_ttl', 5 * MINUTE_IN_SECONDS, $lang, $data);

  set_transient($cache_key, $data, max(1, $cache_ttl));

  return $data;
}

function taiji_get_templates_for_dashboard($lang = null) {
  $templates = taiji_get_templates();
  $usage     = taiji_get_usage_data($lang);
  $counts    = $usage['counts'];

  // aggiunge template "orfani" presenti nel DB ma non più nel tema
  foreach ($counts as $template_key => $count) {
    if ($template_key === 'default') {
      continue;
    }

    if (!isset($templates[$template_key])) {
      $templates[$template_key] = $template_key;
    }
  }

  uksort($templates, function ($a, $b) use ($counts) {
    $count_a = $counts[$a] ?? 0;
    $count_b = $counts[$b] ?? 0;

    if ($count_a === $count_b) {
      return strcmp((string) $a, (string) $b);
    }

    return $count_b <=> $count_a;
  });

  return array($templates, $usage);
}

function taiji_format_post_types_summary($template_key, $types_map) {

  if (empty($types_map[$template_key])) {
    return '';
  }

  $types = $types_map[$template_key];

  $parts = [];
  $i = 0;

  foreach ($types as $post_type => $count) {

    if ($i < 2) {
      $parts[] = sprintf('%s (%d)', $post_type, intval($count));
    }

    $i++;
  }

  $text = implode(', ', $parts);

  if ($i > 2) {
    $text .= ' +' . ($i - 2);
  }

  return $text;
}

function taiji_get_template_file_label($template_key, $theme_path) {
  if ($template_key === 'default') {
    return 'default';
  }

  $path = trailingslashit($theme_path) . $template_key;

  if (!file_exists($path)) {
    return '<span style="color:red">' . esc_html($template_key) . ' (missing)</span>';
  }

  return esc_html($template_key);
}

function taiji_get_last_modified_label($template_key, $theme_path) {
  if ($template_key === 'default') {
    return '';
  }

  $path = trailingslashit($theme_path) . $template_key;

  if (!file_exists($path)) {
    return '';
  }

  return human_time_diff(filemtime($path), current_time('timestamp')) . ' ago';
}

function taiji_build_template_query_args($template, $lang = '') {
  $args = array(
    'post_type'              => 'any',
    'posts_per_page'         => -1,
    'post_status'            => array('publish', 'draft', 'private'),
    'no_found_rows'          => true,
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false,
    'ignore_sticky_posts'    => true,
  );

  if ($template === 'default') {
    $args['meta_query'] = array(
      'relation' => 'OR',
      array(
        'key'     => '_wp_page_template',
        'compare' => 'NOT EXISTS',
      ),
      array(
        'key'   => '_wp_page_template',
        'value' => '',
      ),
      array(
        'key'   => '_wp_page_template',
        'value' => 'default',
      ),
    );
  } else {
    $args['meta_query'] = array(
      array(
        'key'   => '_wp_page_template',
        'value' => $template,
      ),
    );
  }

  if (!empty($lang) && (defined('ICL_SITEPRESS_VERSION') || function_exists('pll_current_language'))) {
    $args['lang'] = $lang;
  }

  return $args;
}

function taiji_get_all_template_urls($lang = '') {

  $args = [
    'post_type'      => 'any',
    'post_status'    => ['publish', 'draft', 'future', 'private'],
    'meta_key'       => '_wp_page_template',
    'posts_per_page' => -1,
    'fields'         => 'ids',
  ];

  /*
   * Language support
   */

  if ($lang) {

    if (function_exists('pll_current_language')) {
      $args['lang'] = $lang;
    }

    if (defined('ICL_SITEPRESS_VERSION')) {
      global $sitepress;
      $sitepress->switch_lang($lang);
    }
  }

  $query = new WP_Query($args);

  if (!$query->have_posts()) {
    return [];
  }

  $map = [];

  foreach ($query->posts as $post_id) {

    $template = get_post_meta($post_id, '_wp_page_template', true);

    if (!$template) {
      $template = 'default';
    }

    if (!isset($map[$template])) {
      $map[$template] = [
        'front' => [],
        'back'  => [],
      ];
    }

    $map[$template]['front'][] = get_permalink($post_id);
    $map[$template]['back'][]  = get_edit_post_link($post_id);
  }

  return $map;
}

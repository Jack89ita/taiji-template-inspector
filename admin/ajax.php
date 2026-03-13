<?php

if (!defined('ABSPATH')) {
  exit;
}

add_action('wp_ajax_tui_load_posts', 'tui_ajax_load_posts');
add_action('wp_ajax_tui_export_csv', 'tui_ajax_export_csv');

function tui_ajax_load_posts() {
  check_ajax_referer('tui_nonce', 'nonce');

  if (! current_user_can('manage_options')) {
    wp_die(esc_html__('You are not allowed to perform this action.', 'template-usage-inspector'));
  }

  $template = isset($_POST['template']) ? sanitize_text_field(wp_unslash($_POST['template'])) : '';
  $lang     = isset($_POST['lang']) ? sanitize_text_field(wp_unslash($_POST['lang'])) : '';

  $args  = tui_build_template_query_args($template, $lang);
  $query = new WP_Query($args);

  if (!$query->have_posts()) {
    echo '<div class="tui-empty-state">';
    echo '<span class="dashicons dashicons-search"></span>';
    echo '<p>' . esc_html__('No results', 'template-usage-inspector') . '</p>';
    echo '</div>';
    wp_die();
  }

  echo '<div class="tui-dropdown-list">';

  while ($query->have_posts()) {
    $query->the_post();

    $post_id   = get_the_ID();
    $edit_link = get_edit_post_link($post_id);
    $view_link = get_permalink($post_id);
    $title     = get_the_title($post_id);
    $post_type = get_post_type($post_id);
    $status    = get_post_status($post_id);

    echo '<div class="tui-dropdown-item">';
    echo '  <div class="tui-dropdown-item-main">';
    echo '      <div class="tui-dropdown-item-title">' . esc_html($title) . '</div>';
    echo '      <div class="tui-dropdown-item-meta">';
    echo '          <span>' . esc_html($post_type) . '</span>';
    echo '          <span class="tui-dropdown-dot">•</span>';
    echo '          <span>' . esc_html($status) . '</span>';
    echo '      </div>';
    echo '  </div>';

    echo '  <div class="tui-dropdown-actions">';
    echo '      <a class="button button-small tui-button tui-button-secondary" href="' . esc_url($edit_link) . '">';
    echo '          <span class="dashicons dashicons-edit"></span>';
    echo '          <span>' . esc_html__('Edit', 'template-usage-inspector') . '</span>';
    echo '      </a>';

    echo '      <a class="button button-small tui-button tui-button-secondary" target="_blank" href="' . esc_url($view_link) . '">';
    echo '          <span class="dashicons dashicons-external"></span>';
    echo '          <span>' . esc_html__('View', 'template-usage-inspector') . '</span>';
    echo '      </a>';
    echo '  </div>';
    echo '</div>';
  }

  echo '</div>';

  wp_reset_postdata();
  wp_die();
}

function tui_escape_csv_value($value) {
  $value = (string) $value;

  if (preg_match('/^[=+\-@]/', $value) === 1) {
    return "'" . $value;
  }

  return $value;
}

function tui_ajax_export_csv() {
  if (!current_user_can('manage_options')) {
    wp_die(esc_html__('You are not allowed to export this data.', 'template-usage-inspector'), 403);
  }

  check_admin_referer('tui_export_csv', 'tui_export_nonce');

  $template = isset($_GET['template']) ? sanitize_text_field(wp_unslash($_GET['template'])) : '';
  $lang     = isset($_GET['tui_lang']) ? sanitize_text_field(wp_unslash($_GET['tui_lang'])) : '';

  $args = tui_build_template_query_args($template, $lang);
  $args['fields'] = 'ids';

  $query = new WP_Query($args);

  $template_slug = $template === 'default'
    ? 'default'
    : sanitize_file_name(str_replace('.php', '', $template));

  $filename = sanitize_file_name('template-usage-' . $template_slug . '.csv');

  nocache_headers();
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=' . $filename);

  $output = fopen('php://output', 'w');

  fputcsv($output, array('ID', 'Title', 'Post Type', 'Status', 'URL'));

  if (!empty($query->posts)) {
    foreach ($query->posts as $post_id) {
      fputcsv($output, array(
        $post_id,
        tui_escape_csv_value(get_the_title($post_id)),
        get_post_type($post_id),
        get_post_status($post_id),
        tui_escape_csv_value(get_permalink($post_id)),
      ));
    }
  }

  // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
  fclose($output);
  exit;
}

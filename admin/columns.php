<?php

if (!defined('ABSPATH')) {
  exit;
}

function tui_add_column($columns) {

  $columns['template'] = 'Template';

  return $columns;
}

add_filter('manage_pages_columns', 'tui_add_column');


function tui_render_column($column, $post_id) {

  if ($column !== 'template') return;

  $template = get_post_meta($post_id, '_wp_page_template', true);

  if (!$template) {

    echo "Default";

    return;
  }

  $templates = wp_get_theme()->get_page_templates();

  echo esc_html($templates[$template] ?? $template);
}

add_action('manage_pages_custom_column', 'tui_render_column', 10, 2);

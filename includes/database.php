<?php

if (!defined('ABSPATH')) {
  exit;
}

function taiji_on_activation() {
  taiji_maybe_add_postmeta_index();
  taiji_flush_cache();
}

function taiji_on_deactivation() {
  taiji_flush_cache();
}

function taiji_maybe_add_postmeta_index() {
  global $wpdb;

  $index_name = 'taiji_template_index';

  $existing = $wpdb->get_var(
    $wpdb->prepare(
      "SHOW INDEX FROM {$wpdb->postmeta} WHERE Key_name = %s",
      $index_name
    )
  );

  if ($existing) {
    return;
  }

  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
  $wpdb->query(
    "ALTER TABLE {$wpdb->postmeta}
         ADD INDEX {$index_name} (meta_key(191), post_id)"
  );
}

function taiji_flush_cache() {
  global $wpdb;

  $like_1 = $wpdb->esc_like('_transient_taiji_template_usage_') . '%';
  $like_2 = $wpdb->esc_like('_transient_timeout_taiji_template_usage_') . '%';

  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
  $wpdb->query(
    $wpdb->prepare(
      "DELETE FROM {$wpdb->options}
             WHERE option_name LIKE %s
             OR option_name LIKE %s",
      $like_1,
      $like_2
    )
  );
}

function taiji_should_track_post($post) {
  if (!$post || empty($post->post_type)) {
    return false;
  }

  if (in_array($post->post_type, array('revision', 'nav_menu_item'), true)) {
    return false;
  }

  return true;
}

function taiji_maybe_flush_cache_on_post_save($post_id, $post) {
  if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
    return;
  }

  if (!taiji_should_track_post($post)) {
    return;
  }

  taiji_flush_cache();
}

function taiji_maybe_flush_cache_on_post_delete($post_id) {
  $post = get_post($post_id);

  if (!taiji_should_track_post($post)) {
    return;
  }

  taiji_flush_cache();
}

function taiji_maybe_flush_cache_on_template_meta_change($meta_id, $post_id, $meta_key) {
  if ($meta_key !== '_wp_page_template') {
    return;
  }

  $post = get_post($post_id);

  if (!taiji_should_track_post($post)) {
    return;
  }

  taiji_flush_cache();
}

add_action('save_post', 'taiji_maybe_flush_cache_on_post_save', 10, 2);
add_action('deleted_post', 'taiji_maybe_flush_cache_on_post_delete');
add_action('trashed_post', 'taiji_maybe_flush_cache_on_post_delete');
add_action('untrashed_post', 'taiji_maybe_flush_cache_on_post_delete');
add_action('added_post_meta', 'taiji_maybe_flush_cache_on_template_meta_change', 10, 3);
add_action('updated_post_meta', 'taiji_maybe_flush_cache_on_template_meta_change', 10, 3);
add_action('deleted_post_meta', 'taiji_maybe_flush_cache_on_template_meta_change', 10, 3);
add_action('switch_theme', 'taiji_flush_cache');

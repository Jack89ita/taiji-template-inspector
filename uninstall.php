<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit;
}

global $wpdb;

/*
|--------------------------------------------------------------------------
| Delete plugin transients
|--------------------------------------------------------------------------
*/

$like_transient = $wpdb->esc_like('_transient_taiji_template_usage_') . '%';
$like_timeout   = $wpdb->esc_like('_transient_timeout_taiji_template_usage_') . '%';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
  $wpdb->prepare(
    "DELETE FROM {$wpdb->options}
        WHERE option_name LIKE %s
        OR option_name LIKE %s",
    $like_transient,
    $like_timeout
  )
);


/*
|--------------------------------------------------------------------------
| Delete potential site transients (multisite)
|--------------------------------------------------------------------------
*/

$like_site_transient = $wpdb->esc_like('_site_transient_taiji_template_usage_') . '%';
$like_site_timeout   = $wpdb->esc_like('_site_transient_timeout_taiji_template_usage_') . '%';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
  $wpdb->prepare(
    "DELETE FROM {$wpdb->options}
        WHERE option_name LIKE %s
        OR option_name LIKE %s",
    $like_site_transient,
    $like_site_timeout
  )
);

<?php

/**
 * Plugin Name: Taiji Template Inspector
 * Plugin URI: https://github.com/Jack89ita/taiji-template-inspector
 * Description: Inspect where WordPress templates are used across pages, posts and CPTs. Quickly open impacted pages, export CSV reports and perform QA checks.
 * Version: 1.0.0
 * Author: Giacomo Mottin
 * Author URI: https://www.giacomomottin.com
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: taiji-template-inspector
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Tested up to: 6.9
 */

if (!defined('ABSPATH')) {
  exit;
}

/*
|--------------------------------------------------------------------------
| CONSTANTS
|--------------------------------------------------------------------------
*/

define('TAIJI_VERSION', '1.0.0');
define('TAIJI_PLUGIN_FILE', __FILE__);
define('TAIJI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TAIJI_PLUGIN_URL', plugin_dir_url(__FILE__));

/*
|--------------------------------------------------------------------------
| INCLUDES
|--------------------------------------------------------------------------
*/

require_once TAIJI_PLUGIN_DIR . 'includes/language.php';
require_once TAIJI_PLUGIN_DIR . 'includes/database.php';
require_once TAIJI_PLUGIN_DIR . 'includes/functions.php';

require_once TAIJI_PLUGIN_DIR . 'admin/dashboard.php';
require_once TAIJI_PLUGIN_DIR . 'admin/ajax.php';
require_once TAIJI_PLUGIN_DIR . 'admin/columns.php';
require_once TAIJI_PLUGIN_DIR . 'admin/filters.php';

/*
|--------------------------------------------------------------------------
| ADMIN ASSETS
|--------------------------------------------------------------------------
*/

add_action('admin_enqueue_scripts', 'taiji_admin_assets', 100);

function taiji_admin_assets($hook) {

  if ($hook !== 'tools_page_taiji-template-inspector') {
    return;
  }

  wp_enqueue_style(
    'taiji-admin',
    TAIJI_PLUGIN_URL . 'assets/admin.css',
    array(),
    TAIJI_VERSION
  );

  wp_enqueue_script(
    'taiji-admin',
    TAIJI_PLUGIN_URL . 'assets/admin.js',
    array('jquery'),
    TAIJI_VERSION,
    true
  );

  wp_localize_script(
    'taiji-admin',
    'taiji_ajax',
    array(
      'url'   => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('taiji_nonce'),
    )
  );
}

/*
|--------------------------------------------------------------------------
| ACTIVATION HOOK
|--------------------------------------------------------------------------
*/

register_activation_hook(__FILE__, 'taiji_plugin_activate');

function taiji_plugin_activate() {

  if (function_exists('taiji_on_activation')) {
    taiji_on_activation();
  }
}

/*
|--------------------------------------------------------------------------
| DEACTIVATION HOOK
|--------------------------------------------------------------------------
*/

register_deactivation_hook(__FILE__, 'taiji_plugin_deactivate');

function taiji_plugin_deactivate() {

  if (function_exists('taiji_on_deactivation')) {
    taiji_on_deactivation();
  }
}

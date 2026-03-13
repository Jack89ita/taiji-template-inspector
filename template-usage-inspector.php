<?php

/**
 * Plugin Name: Template Usage Inspector
 * Plugin URI: https://github.com/Jack89ita/template-usage-inspector
 * Description: Inspect where WordPress templates are used across pages, posts and CPTs. Quickly open impacted pages, export CSV reports and perform QA checks.
 * Version: 1.0.0
 * Author: Giacomo Mottin
 * Author URI: https://www.giacomomottin.com
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: template-usage-inspector
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

define('TUI_VERSION', '1.0.0');
define('TUI_PLUGIN_FILE', __FILE__);
define('TUI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TUI_PLUGIN_URL', plugin_dir_url(__FILE__));

/*
|--------------------------------------------------------------------------
| INCLUDES
|--------------------------------------------------------------------------
*/

require_once TUI_PLUGIN_DIR . 'includes/language.php';
require_once TUI_PLUGIN_DIR . 'includes/database.php';
require_once TUI_PLUGIN_DIR . 'includes/functions.php';

require_once TUI_PLUGIN_DIR . 'admin/dashboard.php';
require_once TUI_PLUGIN_DIR . 'admin/ajax.php';
require_once TUI_PLUGIN_DIR . 'admin/columns.php';
require_once TUI_PLUGIN_DIR . 'admin/filters.php';

/*
|--------------------------------------------------------------------------
| ADMIN ASSETS
|--------------------------------------------------------------------------
*/

add_action('admin_enqueue_scripts', 'tui_admin_assets', 100);

function tui_admin_assets($hook) {

  if ($hook !== 'tools_page_template-usage-inspector') {
    return;
  }

  wp_enqueue_style(
    'tui-admin',
    TUI_PLUGIN_URL . 'assets/admin.css',
    array(),
    TUI_VERSION
  );

  wp_enqueue_script(
    'tui-admin',
    TUI_PLUGIN_URL . 'assets/admin.js',
    array('jquery'),
    TUI_VERSION,
    true
  );

  wp_localize_script(
    'tui-admin',
    'tui_ajax',
    array(
      'url'   => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('tui_nonce'),
    )
  );
}

/*
|--------------------------------------------------------------------------
| ACTIVATION HOOK
|--------------------------------------------------------------------------
*/

register_activation_hook(__FILE__, 'tui_plugin_activate');

function tui_plugin_activate() {

  if (function_exists('tui_on_activation')) {
    tui_on_activation();
  }
}

/*
|--------------------------------------------------------------------------
| DEACTIVATION HOOK
|--------------------------------------------------------------------------
*/

register_deactivation_hook(__FILE__, 'tui_plugin_deactivate');

function tui_plugin_deactivate() {

  if (function_exists('tui_on_deactivation')) {
    tui_on_deactivation();
  }
}

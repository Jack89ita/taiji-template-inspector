<?php

if (!defined('ABSPATH')) {
  exit;
}

add_action('plugins_loaded', 'tui_load_textdomain');

function tui_load_textdomain() {

  load_plugin_textdomain(
    'template-usage-inspector',
    false,
    dirname(plugin_basename(TUI_PLUGIN_FILE)) . '/languages'
  );
}

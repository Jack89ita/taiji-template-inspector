<?php

if (!defined('ABSPATH')) {
  exit;
}

function tui_render_filters() {
  $current_lang = isset($_GET['tui_lang']) ? sanitize_text_field(wp_unslash($_GET['tui_lang'])) : '';
  $languages    = tui_get_available_languages();

  if (empty($languages)) {
    return;
  }
?>
  <form method="get" class="tui-filters-form">
    <input type="hidden" name="page" value="template-usage-inspector">

    <div class="tui-filter-field">
      <label for="tui_lang" class="screen-reader-text">
        <?php esc_html_e('Language', 'template-usage-inspector'); ?>
      </label>

      <select id="tui_lang" name="tui_lang" class="tui-select">
        <option value=""><?php esc_html_e('Current Language', 'template-usage-inspector'); ?></option>

        <?php foreach ($languages as $code => $label) : ?>
          <option value="<?php echo esc_attr($code); ?>" <?php selected($current_lang, $code); ?>>
            <?php echo esc_html($label); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" class="button tui-button tui-button-primary">
      <span class="dashicons dashicons-filter"></span>
      <span><?php esc_html_e('Filter', 'template-usage-inspector'); ?></span>
    </button>
  </form>
<?php
}

<?php

if (!defined('ABSPATH')) {
  exit;
}

function taiji_render_filters() {
  $current_lang = isset($_GET['taiji_lang']) ? sanitize_text_field(wp_unslash($_GET['taiji_lang'])) : '';
  $languages    = taiji_get_available_languages();

  if (empty($languages)) {
    return;
  }
?>
  <form method="get" class="taiji-filters-form">
    <input type="hidden" name="page" value="taiji-template-inspector">

    <div class="taiji-filter-field">
      <label for="taiji_lang" class="screen-reader-text">
        <?php esc_html_e('Language', 'taiji-template-inspector'); ?>
      </label>

      <select id="taiji_lang" name="taiji_lang" class="taiji-select">
        <option value=""><?php esc_html_e('Current Language', 'taiji-template-inspector'); ?></option>

        <?php foreach ($languages as $code => $label) : ?>
          <option value="<?php echo esc_attr($code); ?>" <?php selected($current_lang, $code); ?>>
            <?php echo esc_html($label); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" class="button taiji-button taiji-button-primary">
      <span class="dashicons dashicons-filter"></span>
      <span><?php esc_html_e('Filter', 'taiji-template-inspector'); ?></span>
    </button>
  </form>
<?php
}

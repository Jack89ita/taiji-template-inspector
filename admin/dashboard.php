<?php

if (!defined('ABSPATH')) {
  exit;
}

add_action('admin_menu', 'taiji_register_admin_page');

function taiji_register_admin_page() {
  add_management_page(
    __('Taiji Template Inspector', 'taiji-template-inspector'),
    __('Taiji Template Inspector', 'taiji-template-inspector'),
    'manage_options',
    'taiji-template-inspector',
    'taiji_render_dashboard'
  );
}

function taiji_render_dashboard() {

  $lang_filter = isset($_GET['taiji_lang'])
    ? sanitize_text_field(wp_unslash($_GET['taiji_lang']))
    : taiji_get_current_language();

  list($templates, $usage) = taiji_get_templates_for_dashboard($lang_filter);
  $template_urls = taiji_get_all_template_urls($lang_filter);

  $counts     = $usage['counts'];
  $types      = $usage['types'];
  $theme_path = get_stylesheet_directory();
  $max_usage  = !empty($counts) ? max($counts) : 0;

  $total_templates = count($templates);
  $used_templates = 0;
  $unused_templates = 0;
  $total_pages = 0;

  foreach ($templates as $template_key => $template_name) {
    $count = intval($counts[$template_key] ?? 0);

    if ($count > 0) {
      $used_templates++;
    } else {
      $unused_templates++;
    }

    $total_pages += $count;
  }
?>

  <div class="wrap taiji-wrap">

    <div class="taiji-page-header">
      <div>
        <h1><?php esc_html_e('Taiji Template Inspector', 'taiji-template-inspector'); ?></h1>
        <p class="description">
          <?php esc_html_e('Quickly inspect where templates are used, open affected pages, and export results for QA.', 'taiji-template-inspector'); ?>
        </p>
      </div>
    </div>

    <div class="taiji-summary">
      <div class="taiji-summary-card">
        <span class="dashicons dashicons-media-code"></span>
        <div>
          <strong><?php echo esc_html($total_templates); ?></strong>
          <span><?php esc_html_e('Templates', 'taiji-template-inspector'); ?></span>
        </div>
      </div>

      <div class="taiji-summary-card">
        <span class="dashicons dashicons-yes-alt"></span>
        <div>
          <strong><?php echo esc_html($used_templates); ?></strong>
          <span><?php esc_html_e('Used', 'taiji-template-inspector'); ?></span>
        </div>
      </div>

      <div class="taiji-summary-card">
        <span class="dashicons dashicons-minus"></span>
        <div>
          <strong><?php echo esc_html($unused_templates); ?></strong>
          <span><?php esc_html_e('Unused', 'taiji-template-inspector'); ?></span>
        </div>
      </div>

      <div class="taiji-summary-card">
        <span class="dashicons dashicons-admin-page"></span>
        <div>
          <strong><?php echo esc_html($total_pages); ?></strong>
          <span><?php esc_html_e('Total pages impacted', 'taiji-template-inspector'); ?></span>
        </div>
      </div>
    </div>

    <?php taiji_render_filters(); ?>

    <div class="taiji-toolbar">
      <div class="taiji-search-wrap">
        <span class="dashicons dashicons-search"></span>
        <input
          type="text"
          id="taiji-search"
          placeholder="<?php echo esc_attr__('Search template...', 'taiji-template-inspector'); ?>">
      </div>
    </div>

    <div class="taiji-table-wrap">
      <table class="widefat taiji-table">
        <thead>
          <tr>
            <th><?php esc_html_e('Template', 'taiji-template-inspector'); ?></th>
            <th><?php esc_html_e('File', 'taiji-template-inspector'); ?></th>
            <th><?php esc_html_e('Post Types', 'taiji-template-inspector'); ?></th>
            <th><?php esc_html_e('Last Modified', 'taiji-template-inspector'); ?></th>
            <th><?php esc_html_e('Usage', 'taiji-template-inspector'); ?></th>
            <th><?php esc_html_e('Export', 'taiji-template-inspector'); ?></th>
            <th><?php esc_html_e('QA', 'taiji-template-inspector'); ?></th>
            <th class="taiji-expand-col"></th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($templates as $template_key => $template_name) : ?>
            <?php
            $count         = intval($counts[$template_key] ?? 0);
            $front_urls = [];
            $back_urls  = [];

            if ($count > 0) {

              $front_urls = $template_urls[$template_key]['front'] ?? [];
              $back_urls  = $template_urls[$template_key]['back'] ?? [];
            }

            $front_urls_attr = esc_attr(implode('|', $front_urls));
            $back_urls_attr  = esc_attr(implode('|', $back_urls));
            $post_types    = taiji_format_post_types_summary($template_key, $types);
            $last_modified = taiji_get_last_modified_label($template_key, $theme_path);
            $usage_percent = ($max_usage > 0 && $count > 0)
              ? min(100, round(($count / $max_usage) * 100))
              : 0;
            ?>

            <tr
              class="taiji-template-row"
              data-template="<?php echo esc_attr($template_key); ?>"
              data-lang="<?php echo esc_attr((string) $lang_filter); ?>">
              <td class="taiji-col-template">
                <div class="taiji-template-name-wrap">
                  <span class="dashicons dashicons-media-code taiji-template-icon"></span>

                  <div class="taiji-template-text">
                    <div class="taiji-template-name">
                      <?php echo esc_html($template_name); ?>
                    </div>

                    <?php if ($count > 0) : ?>
                      <span class="taiji-badge taiji-badge-used">
                        <?php esc_html_e('Used', 'taiji-template-inspector'); ?>
                      </span>
                    <?php else : ?>
                      <span class="taiji-badge taiji-badge-unused">
                        <?php esc_html_e('Unused', 'taiji-template-inspector'); ?>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </td>

              <td>
                <?php echo wp_kses_post(taiji_get_template_file_label($template_key, $theme_path)); ?>
              </td>

              <td class="taiji-post-types">
                <?php echo esc_html($post_types); ?>
              </td>

              <td>
                <div class="taiji-last-modified">
                  <?php if (!empty($last_modified)) : ?>
                    <span class="dashicons dashicons-clock"></span>
                  <?php endif; ?>
                  <span><?php echo esc_html($last_modified); ?></span>
                </div>
              </td>

              <td class="taiji-col-usage">
                <div class="taiji-usage-block">
                  <div class="taiji-usage-number-wrap">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <strong
                      class="taiji-usage-number"
                      data-target="<?php echo esc_attr((string) $count); ?>">
                      0
                    </strong>
                  </div>

                  <div class="taiji-progress">
                    <span
                      class="taiji-progress-bar"
                      data-width="<?php echo esc_attr((string) $usage_percent); ?>"
                      style="width:0%;"></span>
                  </div>
                </div>
              </td>

              <td>
                <?php if ($count > 0) : ?>
                  <a
                    class="button taiji-button taiji-button-secondary"
                    href="<?php echo esc_url(
                            wp_nonce_url(
                              admin_url(
                                'admin-ajax.php?action=taiji_export_csv&template=' . rawurlencode($template_key) . '&taiji_lang=' . rawurlencode((string) $lang_filter)
                              ),
                              'taiji_export_csv',
                              'taiji_export_nonce'
                            )
                          ); ?>">
                    <span class="dashicons dashicons-download"></span>
                    <span><?php esc_html_e('CSV', 'taiji-template-inspector'); ?></span>
                  </a>
                <?php endif; ?>
              </td>

              <td>
                <?php if ($count > 0) : ?>
                  <div class="taiji-qa-actions">
                    <button
                      type="button"
                      class="button taiji-button taiji-button-secondary taiji-open-all-front"
                      data-urls="<?php echo esc_attr($front_urls_attr); ?>">
                      <span class=" dashicons dashicons-visibility"></span>
                      <span><?php esc_html_e('Frontend', 'taiji-template-inspector'); ?></span>
                    </button>

                    <button
                      type="button"
                      class="button taiji-button taiji-button-secondary taiji-open-all-back"
                      data-urls="<?php echo esc_attr($back_urls_attr); ?>">
                      <span class="dashicons dashicons-edit"></span>
                      <span><?php esc_html_e('Backend', 'taiji-template-inspector'); ?></span>
                    </button>
                  </div>
                <?php endif; ?>
              </td>

              <td class="taiji-expand-cell">
                <?php if ($count > 0) : ?>
                  <button
                    type="button"
                    class="taiji-expand-arrow"
                    aria-label="<?php esc_attr_e('Expand row', 'taiji-template-inspector'); ?>">
                    <span class="dashicons dashicons-arrow-down-alt2 taiji-arrow"></span>
                  </button>
                <?php endif; ?>
              </td>
            </tr>

            <tr class="taiji-results">
              <td colspan="8"></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>

<?php
}

<?php

if (!defined('ABSPATH')) {
  exit;
}

add_action('admin_menu', 'tui_register_admin_page');

function tui_register_admin_page() {
  add_management_page(
    __('Template Usage Inspector', 'template-usage-inspector'),
    __('Template Usage Inspector', 'template-usage-inspector'),
    'manage_options',
    'template-usage-inspector',
    'tui_render_dashboard'
  );
}

function tui_render_dashboard() {

  $lang_filter = isset($_GET['tui_lang'])
    ? sanitize_text_field(wp_unslash($_GET['tui_lang']))
    : tui_get_current_language();

  list($templates, $usage) = tui_get_templates_for_dashboard($lang_filter);
  $template_urls = tui_get_all_template_urls($lang_filter);

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

  <div class="wrap tui-wrap">

    <div class="tui-page-header">
      <div>
        <h1><?php esc_html_e('Template Usage Inspector', 'template-usage-inspector'); ?></h1>
        <p class="description">
          <?php esc_html_e('Quickly inspect where templates are used, open affected pages, and export results for QA.', 'template-usage-inspector'); ?>
        </p>
      </div>
    </div>

    <div class="tui-summary">
      <div class="tui-summary-card">
        <span class="dashicons dashicons-media-code"></span>
        <div>
          <strong><?php echo esc_html($total_templates); ?></strong>
          <span><?php esc_html_e('Templates', 'template-usage-inspector'); ?></span>
        </div>
      </div>

      <div class="tui-summary-card">
        <span class="dashicons dashicons-yes-alt"></span>
        <div>
          <strong><?php echo esc_html($used_templates); ?></strong>
          <span><?php esc_html_e('Used', 'template-usage-inspector'); ?></span>
        </div>
      </div>

      <div class="tui-summary-card">
        <span class="dashicons dashicons-minus"></span>
        <div>
          <strong><?php echo esc_html($unused_templates); ?></strong>
          <span><?php esc_html_e('Unused', 'template-usage-inspector'); ?></span>
        </div>
      </div>

      <div class="tui-summary-card">
        <span class="dashicons dashicons-admin-page"></span>
        <div>
          <strong><?php echo esc_html($total_pages); ?></strong>
          <span><?php esc_html_e('Total pages impacted', 'template-usage-inspector'); ?></span>
        </div>
      </div>
    </div>

    <?php tui_render_filters(); ?>

    <div class="tui-toolbar">
      <div class="tui-search-wrap">
        <span class="dashicons dashicons-search"></span>
        <input
          type="text"
          id="tui-search"
          placeholder="<?php echo esc_attr__('Search template...', 'template-usage-inspector'); ?>">
      </div>
    </div>

    <div class="tui-table-wrap">
      <table class="widefat tui-table">
        <thead>
          <tr>
            <th><?php esc_html_e('Template', 'template-usage-inspector'); ?></th>
            <th><?php esc_html_e('File', 'template-usage-inspector'); ?></th>
            <th><?php esc_html_e('Post Types', 'template-usage-inspector'); ?></th>
            <th><?php esc_html_e('Last Modified', 'template-usage-inspector'); ?></th>
            <th><?php esc_html_e('Usage', 'template-usage-inspector'); ?></th>
            <th><?php esc_html_e('Export', 'template-usage-inspector'); ?></th>
            <th><?php esc_html_e('QA', 'template-usage-inspector'); ?></th>
            <th class="tui-expand-col"></th>
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
            $post_types    = tui_format_post_types_summary($template_key, $types);
            $last_modified = tui_get_last_modified_label($template_key, $theme_path);
            $usage_percent = ($max_usage > 0 && $count > 0)
              ? min(100, round(($count / $max_usage) * 100))
              : 0;
            ?>

            <tr
              class="tui-template-row"
              data-template="<?php echo esc_attr($template_key); ?>"
              data-lang="<?php echo esc_attr((string) $lang_filter); ?>">
              <td class="tui-col-template">
                <div class="tui-template-name-wrap">
                  <span class="dashicons dashicons-media-code tui-template-icon"></span>

                  <div class="tui-template-text">
                    <div class="tui-template-name">
                      <?php echo esc_html($template_name); ?>
                    </div>

                    <?php if ($count > 0) : ?>
                      <span class="tui-badge tui-badge-used">
                        <?php esc_html_e('Used', 'template-usage-inspector'); ?>
                      </span>
                    <?php else : ?>
                      <span class="tui-badge tui-badge-unused">
                        <?php esc_html_e('Unused', 'template-usage-inspector'); ?>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </td>

              <td>
                <?php echo wp_kses_post(tui_get_template_file_label($template_key, $theme_path)); ?>
              </td>

              <td class="tui-post-types">
                <?php echo esc_html($post_types); ?>
              </td>

              <td>
                <div class="tui-last-modified">
                  <?php if (!empty($last_modified)) : ?>
                    <span class="dashicons dashicons-clock"></span>
                  <?php endif; ?>
                  <span><?php echo esc_html($last_modified); ?></span>
                </div>
              </td>

              <td class="tui-col-usage">
                <div class="tui-usage-block">
                  <div class="tui-usage-number-wrap">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <strong
                      class="tui-usage-number"
                      data-target="<?php echo esc_attr((string) $count); ?>">
                      0
                    </strong>
                  </div>

                  <div class="tui-progress">
                    <span
                      class="tui-progress-bar"
                      data-width="<?php echo esc_attr((string) $usage_percent); ?>"
                      style="width:0%;"></span>
                  </div>
                </div>
              </td>

              <td>
                <?php if ($count > 0) : ?>
                  <a
                    class="button tui-button tui-button-secondary"
                    href="<?php echo esc_url(
                            wp_nonce_url(
                              admin_url(
                                'admin-ajax.php?action=tui_export_csv&template=' . rawurlencode($template_key) . '&tui_lang=' . rawurlencode((string) $lang_filter)
                              ),
                              'tui_export_csv',
                              'tui_export_nonce'
                            )
                          ); ?>">
                    <span class="dashicons dashicons-download"></span>
                    <span><?php esc_html_e('CSV', 'template-usage-inspector'); ?></span>
                  </a>
                <?php endif; ?>
              </td>

              <td>
                <?php if ($count > 0) : ?>
                  <div class="tui-qa-actions">
                    <button
                      type="button"
                      class="button tui-button tui-button-secondary tui-open-all-front"
                      data-urls="<?php echo esc_attr($front_urls_attr); ?>">
                      <span class=" dashicons dashicons-visibility"></span>
                      <span><?php esc_html_e('Frontend', 'template-usage-inspector'); ?></span>
                    </button>

                    <button
                      type="button"
                      class="button tui-button tui-button-secondary tui-open-all-back"
                      data-urls="<?php echo esc_attr($back_urls_attr); ?>">
                      <span class="dashicons dashicons-edit"></span>
                      <span><?php esc_html_e('Backend', 'template-usage-inspector'); ?></span>
                    </button>
                  </div>
                <?php endif; ?>
              </td>

              <td class="tui-expand-cell">
                <?php if ($count > 0) : ?>
                  <button
                    type="button"
                    class="tui-expand-arrow"
                    aria-label="<?php esc_attr_e('Expand row', 'template-usage-inspector'); ?>">
                    <span class="dashicons dashicons-arrow-down-alt2 tui-arrow"></span>
                  </button>
                <?php endif; ?>
              </td>
            </tr>

            <tr class="tui-results">
              <td colspan="8"></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>

<?php
}

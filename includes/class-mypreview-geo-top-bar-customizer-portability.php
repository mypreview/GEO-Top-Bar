<?php
/**
 * A class to create an import and export controls.
 *
 * @author      Mahdi Yazdani
 * @package     GEO Top Bar
 * @since       1.0
 */
// Prevent direct file access
defined('ABSPATH') or exit;
if (class_exists('WP_Customize_Control') && !class_exists('MyPreview_GEO_Top_Bar_Customizer_Portability')):
    final class MyPreview_GEO_Top_Bar_Customizer_Portability extends WP_Customize_Control

    {
        /**
         * Render the content of the portability section.
         * Custom markup to display Export & Import option(s).
         *
         * @since 1.0
         */
        public function render_content()

        {
           ?>
           	<span class="customize-control-title">
				<?php esc_html_e( 'Export Settings', 'mypreview-geo-top-bar' ); ?>
			</span>
			<span class="description customize-control-description">
				<?php esc_html_e( 'Exporting GEO Top Bar plugin customizer settings will create a "JSON" file that can be imported into a different website.', 'mypreview-geo-top-bar' ); ?>
			</span>
			<input type="button" class="button" name="mypreview-geo-top-bar-export-button" value="<?php esc_attr_e( 'Export', 'mypreview-geo-top-bar' ); ?>" />
			<hr class="mypreview-geo-top-bar-hr" />
			<span class="customize-control-title">
				<?php esc_html_e( 'Import Settings', 'mypreview-geo-top-bar' ); ?>
			</span>
			<span class="description customize-control-description">
				<?php esc_html_e( 'Importing a previously-exported GEO Top Bar customizer settings file will overwrite all current data. Please proceed with caution!', 'mypreview-geo-top-bar' ); ?>
			</span>
			<div class="mypreview-geo-top-bar-import-controls">
				<input type="file" name="mypreview-geo-top-bar-import-file" class="mypreview-geo-top-bar-import-file" />
				<?php wp_nonce_field('mypreview_geo_top_bar_import_nonce', 'mypreview_geo_top_bar_import_security'); ?>
			</div>
			<div class="mypreview-geo-top-bar-uploading"><?php esc_html_e( 'Uploading...', 'mypreview-geo-top-bar' ); ?></div>
			<input type="button" class="button" name="mypreview-geo-top-bar-import-button" value="<?php esc_attr_e( 'Import', 'mypreview-geo-top-bar' ); ?>" />
           <?php
        }
    }
endif;
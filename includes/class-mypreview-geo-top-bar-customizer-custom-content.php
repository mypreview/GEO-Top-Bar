<?php
/**
 * A class to create a custom control to output arbitrary HTML.
 *
 * @author      Mahdi Yazdani
 * @package     GEO Top Bar
 * @since       1.0
 */
// Prevent direct file access
defined('ABSPATH') or exit;
if (class_exists('WP_Customize_Control') && !class_exists('MyPreview_GEO_Top_Bar_Customizer_Custom_Content')):
	class MyPreview_GEO_Top_Bar_Customizer_Custom_Content extends WP_Customize_Control

	{
		// Whitelist content parameter
		public $content = '';

		/**
		 * Render the control's content.
		 * Allows the content to be overriden without having to rewrite the wrapper.
		 *
		 * @since 1.0
		 */
		public function render_content()

		{
			if (isset($this->label)):
				echo '<span class="customize-control-title">' . esc_html($this->label) . '</span>' . PHP_EOL;
			endif;
			if (isset($this->content)):
				echo esc_html($this->content) . PHP_EOL;
			endif;
			if (isset($this->description)):
				echo '<span class="description customize-control-description">' . esc_html($this->description) . '</span>' . PHP_EOL;
			endif;
		}
	}
endif;
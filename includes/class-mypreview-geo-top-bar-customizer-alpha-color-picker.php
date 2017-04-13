<?php
/**
 * Creating a custom Alpha Color Picker control to output RGBa value(s).
 * Inspired by Customizer Alpha Color Picker Control.
 *
 * @link 		https://github.com/BraadMartin/components/tree/master/alpha-color-picker
 * @author      Mahdi Yazdani
 * @package     GEO Top Bar
 * @since       1.0
 */
// Prevent direct file access
defined('ABSPATH') or exit;
if (class_exists('WP_Customize_Control') && !class_exists('MyPreview_GEO_Top_Bar_Customizer_Alpha_Color_Picker')):
	final class MyPreview_GEO_Top_Bar_Customizer_Alpha_Color_Picker extends WP_Customize_Control

	{
		/**
		 * Official control name.
		 *
		 * @since 1.0
		 */
		public $type = 'alpha-color';

		/**
		 * Add support for palettes to be passed in.
		 * Supported palette values are true, false, or an array of RGBa and Hex colors.
		 *
		 * @since 1.0
		 */
		public $palette;

		/**
		 * Add support for showing the opacity value on the slider handle.
		 *
		 * @since 1.0
		 */
		public $show_opacity;

		/**
		 * Render the control.
		 *
		 * @since 1.0
		 */
		public function render_content()

		{
			// Process the palette
			if (is_array($this->palette)):
				$palette = implode('|', $this->palette);
			else:
				// Default to true.
				$palette = (false === $this->palette || 'false' === $this->palette) ? 'false' : 'true';
			endif;
			// Support passing show_opacity as string or boolean. Default to true.
			$show_opacity = (false === $this->show_opacity || 'false' === $this->show_opacity) ? 'false' : 'true';
			// Begin the output.
			?>
			<label>
				<?php // Output the label and description if they were passed in.
			if (isset($this->label) && '' !== $this->label):
				echo '<span class="customize-control-title">' . sanitize_text_field($this->label) . '</span>';
			endif;
			if (isset($this->description) && '' !== $this->description):
				echo '<span class="description customize-control-description">' . sanitize_text_field($this->description) . '</span>';
			endif; 
			?>
			<input class="alpha-color-control" type="text" data-show-opacity="<?php
			echo $show_opacity; ?>" data-palette="<?php
			echo esc_attr($palette); ?>" data-default-color="<?php
			echo esc_attr($this->settings['default']->default); ?>" <?php
			$this->link(); ?>  />
			</label>
			<?php
		}
	}
endif;
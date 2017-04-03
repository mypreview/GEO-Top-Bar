<?php
/**
 * A class to create a custom background control.
 * Inspired by Customizer Background Control by devinsays.
 *
 * @link 		https://github.com/devinsays/customizer-background-control
 * @author      Mahdi Yazdani
 * @package     GEO Top Bar
 * @since       1.0
 */
// Prevent direct file access
defined('ABSPATH') or exit;
if (class_exists('WP_Customize_Control') && !class_exists('MyPreview_GEO_Top_Bar_Customizer_Custom_Background')):
	class MyPreview_GEO_Top_Bar_Customizer_Custom_Background extends WP_Customize_Upload_Control

	{
		public $type = 'background-image';
		public $mime_type = 'image';
		public $button_labels = array();
		public $field_labels = array();
		public $background_choices = array();
		/**
		 * Constructor.
		 *
		 * @since 1.0
		 */
		public function __construct($manager, $id, $args = array())
		{
			// Calls the parent __construct
			parent::__construct($manager, $id, $args);
			// Set button labels for image uploader
			$button_labels = $this->get_button_labels();
			$this->button_labels = apply_filters('mypreview_geo_top_bar_customizer_background_button_labels', $button_labels, $id);
			// Set field labels and descriptions
			$field_labels = $this->get_field_labels();
			$field_descriptions = $this->get_field_descriptions();
			$this->field_labels = apply_filters('mypreview_geo_top_bar_customizer_background_field_labels', $field_labels, $id);
			$this->field_descriptions = apply_filters('mypreview_geo_top_bar_customizer_background_field_descriptions', $field_descriptions, $id);
			// Set background choices
			$background_choices = $this->get_background_choices();
			$this->background_choices = apply_filters('mypreview_geo_top_bar_customizer_background_choices', $background_choices, $id);
		}
		/**
		 * Add custom parameters to pass to the JS via JSON.
		 *
		 * @since  1.0
		 */
		public function to_json()

		{
			parent::to_json();
			$background_choices = $this->background_choices;
			$field_labels = $this->field_labels;
			$field_descriptions = $this->field_descriptions;
			// Loop through each of the settings and set up the data for it.
			foreach($this->settings as $setting_key => $setting_id):
				$this->json[$setting_key] = array(
					'link' => $this->get_link($setting_key) ,
					'value' => $this->value($setting_key) ,
					'label' => isset($field_labels[$setting_key]) ? $field_labels[$setting_key] : '' ,
					'description' => isset($field_descriptions[$setting_key]) ? $field_descriptions[$setting_key] : ''
				);
				if ('image_url' === $setting_key):
					if ($this->value($setting_key)):
						// Get the attachment model for the existing file.
						$attachment_id = attachment_url_to_postid($this->value($setting_key));
						if ($attachment_id):
							$this->json['attachment'] = wp_prepare_attachment_for_js($attachment_id);
						endif;
					endif;
				elseif ('repeat' === $setting_key):
					$this->json[$setting_key]['choices'] = $background_choices['repeat'];
				elseif ('size' === $setting_key):
					$this->json[$setting_key]['choices'] = $background_choices['size'];
				elseif ('position' === $setting_key):
					$this->json[$setting_key]['choices'] = $background_choices['position'];
				elseif ('attach' === $setting_key):
					$this->json[$setting_key]['choices'] = $background_choices['attach'];
				endif;
			endforeach;
		}
		/**
		 * Render a JS template for the content of the media control.
		 *
		 * @since 1.0
		 */
		public function content_template()

		{
			parent::content_template();
			?>

			<div class="mypreview-geo-top-bar-background-image-fields">
			<# if ( data.attachment && data.repeat && data.repeat.choices ) { #>
				<li class="mypreview-geo-top-bar-background-image-repeat">
					<# if ( data.repeat.label ) { #>
						<span class="customize-control-title">{{ data.repeat.label }}</span>
					<# } #>
					<# if ( data.repeat.description ) { #>
						<span class="description customize-control-description">{{ data.repeat.description }}</span>
					<# } #>
					<select {{{ data.repeat.link }}}>
						<# _.each( data.repeat.choices, function( label, choice ) { #>
							<option value="{{ choice }}" <# if ( choice === data.repeat.value ) { #> selected="selected" <# } #>>{{ label }}</option>
						<# } ) #>
					</select>
				</li>
			<# } #>

			<# if ( data.attachment && data.size && data.size.choices ) { #>
				<li class="mypreview-geo-top-bar-background-image-size">
					<# if ( data.size.label ) { #>
						<span class="customize-control-title">{{ data.size.label }}</span>
					<# } #>
					<# if ( data.size.description ) { #>
						<span class="description customize-control-description">{{ data.size.description }}</span>
					<# } #>
					<select {{{ data.size.link }}}>
						<# _.each( data.size.choices, function( label, choice ) { #>
							<option value="{{ choice }}" <# if ( choice === data.size.value ) { #> selected="selected" <# } #>>{{ label }}</option>
						<# } ) #>
					</select>
				</li>
			<# } #>

			<# if ( data.attachment && data.position && data.position.choices ) { #>
				<li class="mypreview-geo-top-bar-background-image-position">
					<# if ( data.position.label ) { #>
						<span class="customize-control-title">{{ data.position.label }}</span>
					<# } #>
					<# if ( data.position.description ) { #>
						<span class="description customize-control-description">{{ data.position.description }}</span>
					<# } #>
					<select {{{ data.position.link }}}>
						<# _.each( data.position.choices, function( label, choice ) { #>
							<option value="{{ choice }}" <# if ( choice === data.position.value ) { #> selected="selected" <# } #>>{{ label }}</option>
						<# } ) #>
					</select>
				</li>
			<# } #>

			<# if ( data.attachment && data.attach && data.attach.choices ) { #>
				<li class="mypreview-geo-top-bar-background-image-attach">
					<# if ( data.attach.label ) { #>
						<span class="customize-control-title">{{ data.attach.label }}</span>
					<# } #>
					<# if ( data.attach.description ) { #>
						<span class="description customize-control-description">{{ data.attach.description }}</span>
					<# } #>
					<select {{{ data.attach.link }}}>
						<# _.each( data.attach.choices, function( label, choice ) { #>
							<option value="{{ choice }}" <# if ( choice === data.attach.value ) { #> selected="selected" <# } #>>{{ label }}</option>
						<# } ) #>
					</select>
				</li>
			<# } #>

			</div>

		<?php
		}
		/**
		 * Returns button labels.
		 *
		 * @since 1.0
		 */
		public static function get_button_labels()

		{
			$button_labels = array(
				'select' => __('Select Image', 'mypreview-geo-top-bar') ,
				'change' => __('Change Image', 'mypreview-geo-top-bar') ,
				'remove' => __('Remove', 'mypreview-geo-top-bar') ,
				'default' => __('Default', 'mypreview-geo-top-bar') ,
				'placeholder' => __('No image selected', 'mypreview-geo-top-bar') ,
				'frame_title' => __('Select Image', 'mypreview-geo-top-bar') ,
				'frame_button' => __('Choose Image', 'mypreview-geo-top-bar') ,
			);
			return $button_labels;
		}
		/**
		 * Returns field labels.
		 *
		 * @since 1.0
		 */
		public static function get_field_labels()

		{
			$field_labels = array(
				'repeat' => __('Background Repeat', 'mypreview-geo-top-bar') ,
				'size' => __('Background Size', 'mypreview-geo-top-bar') ,
				'position' => __('Background Position', 'mypreview-geo-top-bar') ,
				'attach' => __('Background Attachment', 'mypreview-geo-top-bar')
			);
			return $field_labels;
		}
		/**
		 * Returns field description.
		 *
		 * @since 1.0
		 */
		public static function get_field_descriptions()

		{
			$field_labels = array(
				'repeat' => __('Set if/how a background image will be repeated.', 'mypreview-geo-top-bar') ,
				'size' => __('Specifie the size of the background image.', 'mypreview-geo-top-bar') ,
				'position' => __('Set the starting position of a background image.', 'mypreview-geo-top-bar') ,
				'attach' => __('Set whether a background image is fixed or scrolls with the rest of the page.', 'mypreview-geo-top-bar')
			);
			return $field_labels;
		}
		/**
		 * Returns the background choices.
		 *
		 * @since 1.0
		 */
		public static function get_background_choices()

		{
			$choices = array(
				'repeat' => array(
					'no-repeat' => __('No Repeat', 'mypreview-geo-top-bar') ,
					'repeat' => __('Tile', 'mypreview-geo-top-bar') ,
					'repeat-x' => __('Tile Horizontally', 'mypreview-geo-top-bar') ,
					'repeat-y' => __('Tile Vertically', 'mypreview-geo-top-bar')
				) ,
				'size' => array(
					'auto' => __('Default', 'mypreview-geo-top-bar') ,
					'cover' => __('Cover', 'mypreview-geo-top-bar') ,
					'contain' => __('Contain', 'mypreview-geo-top-bar')
				) ,
				'position' => array(
					'left top' => __('Left Top', 'mypreview-geo-top-bar') ,
					'left center' => __('Left Center', 'mypreview-geo-top-bar') ,
					'left bottom' => __('Left Bottom', 'mypreview-geo-top-bar') ,
					'right top' => __('Right Top', 'mypreview-geo-top-bar') ,
					'right center' => __('Right Center', 'mypreview-geo-top-bar') ,
					'right bottom' => __('Right Bottom', 'mypreview-geo-top-bar') ,
					'center top' => __('Center Top', 'mypreview-geo-top-bar') ,
					'center center' => __('Center Center', 'mypreview-geo-top-bar') ,
					'center bottom' => __('Center Bottom', 'mypreview-geo-top-bar')
				) ,
				'attach' => array(
					'fixed' => __('Fixed', 'mypreview-geo-top-bar') ,
					'scroll' => __('Scroll', 'mypreview-geo-top-bar')
				)
			);
			return $choices;
		}
	}
endif;
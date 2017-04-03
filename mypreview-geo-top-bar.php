<?php
/*
Plugin Name: 	GEO Top Bar
Plugin URI:  	https://demo.mypreview.one/geo-top-bar
Description: 	Display a highly customizable sleek message bar on your website. An ideal choice for informing visitors from specific geo locations.
Version:     	1.0
Author:      	Mahdi Yazdani
Author URI:  	https://codecanyon.net/user/mypreview
Text Domain: 	mypreview-geo-top-bar
Domain Path: 	/languages
License:     	GPL3
License URI: 	https://www.gnu.org/licenses/gpl-3.0.html

GEO Top Bar is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with GEO Top Bar. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
*/
// Prevent direct file access
defined('ABSPATH') or exit;
define('GEO_TOP_BAR_VERSION', '1.0');
if (!class_exists('MyPreview_GEO_Top_Bar')):
	/**
	 * The MyPreview GEO Top Bar - Class
	 */
	final class MyPreview_GEO_Top_Bar

	{
		private $file;
		private $dir;
		private $public_assets_url;
		private $admin_assets_url;
		private static $_instance = null;
		/**
		 * Main Hypermarket_GEO_Topbar instance
		 *
		 * Ensures only one instance of Hypermarket_GEO_Topbar is loaded or can be loaded.
		 *
		 * @since 1.0
		 */
		public static function instance()

		{
			if (is_null(self::$_instance)) self::$_instance = new self();
			return self::$_instance;
		}
		/**
		 * Setup class.
		 *
		 * @since 1.0
		 */
		public function __construct()

		{
			$this->file = plugin_basename(__FILE__);
			$this->dir = dirname(__FILE__);
			$this->public_assets_url = esc_url(trailingslashit(plugins_url('public/', $this->file)));
			$this->admin_assets_url = esc_url(trailingslashit(plugins_url('admin/', $this->file)));
			require_once( $this->dir . '/includes/geoiploc.php' );
			add_action('init', array(
				$this,
				'textdomain'
			));
			add_action('add_meta_boxes', array(
				$this,
				'add_meta_box'
			) , 10);
			add_action('save_post', array(
				$this,
				'save_meta_box'
			) , 1, 2);
			add_action('customize_controls_enqueue_scripts', array(
				$this,
				'customize_preview_init'
			) , 99);
			add_action('customize_register', array(
				$this,
				'customize_register'
			) , 10, 1);
			add_action('wp_enqueue_scripts', array(
				$this,
				'enqueue'
			) , 10);
			add_action('wp_footer', array(
				$this,
				'country_select_modal'
			) , 10);
			add_action('init', array(
				$this,
				'default_country_cookie'
			) , 1);
		}
		/**
		 * Cloning instances of this class is forbidden.
		 *
		 * @since 1.0
		 */
		public function __clone()

		{
			_doing_it_wrong(__FUNCTION__, __('Cloning instances of this class is forbidden.', 'mypreview-geo-top-bar') , GEO_TOP_BAR_VERSION);
		}
		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0
		 */
		public function __wakeup()

		{
			_doing_it_wrong(__FUNCTION__, __('Unserializing instances of this class is forbidden.', 'mypreview-geo-top-bar') , GEO_TOP_BAR_VERSION);
		}
		/**
		 * Load languages file and text domains.
		 *
		 * @since 1.0
		 */
		public function textdomain()

		{
			$domain = 'mypreview-geo-top-bar';
			$locale = apply_filters('mypreview_geo_top_bar_textdoamin', get_locale() , $domain);
			load_textdomain($domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo');
			load_plugin_textdomain($domain, FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');
		}
		/**
		 * Add "Hide GEO Top Bar" metabox.
		 *
		 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/add_meta_boxes
		 * @since 1.0
		 */
		public function add_meta_box()

		{
			$post_types = array('post', 'page');
			foreach($post_types as $post_type):
				add_meta_box('mypreview-geo-top-bar-metabox', __('GEO Top Bar', 'mypreview-geo-top-bar') , array(
				$this,
				'render_meta_box'
			) , $post_type, 'normal', 'default');
			endforeach;
		}
		/**
		 * Render and display "Hide GEO Top Bar" toggle checkbox.
		 *
		 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/add_meta_boxes
		 * @since 1.0
		 */
		public function render_meta_box($post)

		{
			// Add an nonce field so we can check for it later.
			wp_nonce_field('myprevie_geo_top_bar_metabox_checkbox_toggle', 'myprevie_geo_top_bar_metabox_checkbox_toggle_nonce');
			$hide_geo_top_bar = get_post_meta($post->ID, '_myprevie_geo_top_bar_metabox_checkbox', true);
			if ('post' == $post->post_type || 'page' == $post->post_type):
					?>
					<p>
						<input type="checkbox" id="_myprevie_geo_top_bar_metabox_checkbox" name="_myprevie_geo_top_bar_metabox_checkbox" value="true" <?php checked('true', $hide_geo_top_bar); ?>>
						<label for="_myprevie_geo_top_bar_metabox_checkbox"><strong><?php echo __('Hide GEO Top Bar', 'mypreview-geo-top-bar'); ?></strong></label>
						<br /><br />
						<em style="color:#aaa;"><?php echo __('This checkbox will hide the GEO Top Bar from view.', 'mypreview-geo-top-bar'); ?></em>
					</p>
				<?php
			endif;
		}
		/**
		 * Function to handle saving of the option modified on the metabox
		 *
		 * @since 1.0
		 */
		public function save_meta_box($post_id)

		{
			// Security check
			if (!isset($_POST['myprevie_geo_top_bar_metabox_checkbox_toggle_nonce']) || !wp_verify_nonce($_POST['myprevie_geo_top_bar_metabox_checkbox_toggle_nonce'], 'myprevie_geo_top_bar_metabox_checkbox_toggle')):
				return;
			endif;
			// Bail out if running an autosave, ajax, cron.
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE):
				return;
			endif;
			if (defined('DOING_AJAX') && DOING_AJAX):
				return;
			endif;
			if (defined('DOING_CRON') && DOING_CRON):
				return;
			endif;
			// Bail out if the user doesn't have the correct permissions to update the slider.
			if (!current_user_can('edit_post', $post_id)):
				return;
			endif;
			$var = array();
			$var['_myprevie_geo_top_bar_metabox_checkbox'] = array_key_exists('_myprevie_geo_top_bar_metabox_checkbox', $_POST) ? $_POST['_myprevie_geo_top_bar_metabox_checkbox'] : '';
			foreach($var as $key => $value):
				if ($value === 'true'):
					update_post_meta($post_id, $key, $value);
				else:
					delete_post_meta($post_id, $key, $value);
				endif;
			endforeach;
		}
		/**
		 * Enqueue scripts and styles.
		 *
		 * @since 1.0
		 */
		public function customize_preview_init()

		{
			wp_enqueue_style('geo-top-bar-country-select-style', $this->admin_assets_url . 'css/mypreview-geo-top-bar-country-select.min.css', array() , GEO_TOP_BAR_VERSION, 'all');
			wp_enqueue_style('geo-top-bar-alpha-color-picker-style', $this->admin_assets_url . 'css/mypreview-geo-top-bar-alpha-color-picker.min.css', array() , GEO_TOP_BAR_VERSION, 'all');
			wp_enqueue_style('geo-top-bar-customizer-styles', $this->admin_assets_url . 'css/mypreview-geo-top-bar-customizer.css', array() , GEO_TOP_BAR_VERSION, 'all');
			wp_enqueue_script('geo-top-bar-country-select-js', $this->admin_assets_url . 'js/mypreview-geo-top-bar-country-select.min.js', array() , GEO_TOP_BAR_VERSION, true);
			wp_enqueue_script('geo-top-bar-alpha-color-picker-js', $this->admin_assets_url . 'js/mypreview-geo-top-bar-alpha-color-picker.min.js', array() , GEO_TOP_BAR_VERSION, true);
			wp_enqueue_script('geo-top-bar-custom-background-js', $this->admin_assets_url . 'js/mypreview-geo-top-bar-custom-background.min.js', array() , GEO_TOP_BAR_VERSION, true);
			wp_register_script('geo-top-bar-customizer-js', $this->admin_assets_url . 'js/mypreview-geo-top-bar-customizer.js', array() , GEO_TOP_BAR_VERSION, true);
			wp_localize_script('geo-top-bar-customizer-js', 'mypreview_geo_top_bar_customizer_vars', array(
				'msgBarMaxChar' => __('Plugin prefers message with max %s chars.', 'mypreview-geo-top-bar') ,
				'msgBarContentReq' => __('Plugin prefers to have a message to display in the bar.', 'mypreview-geo-top-bar')
			));
			wp_enqueue_script('geo-top-bar-customizer-js');
		}
		/**
		 * Add copyright section to WordPress customizer.
		 *
		 * @since 1.0
		 */
		public function customize_register($wp_customize)

		{
			/**
			 * Custom customizer control classes.
			 *
			 * @since 1.0
			 */
			require_once ($this->dir . '/includes/class-mypreview-geo-top-bar-customizer-google-fonts.php');
			require_once ($this->dir . '/includes/class-mypreview-geo-top-bar-customizer-alpha-color-picker.php');
			require_once ($this->dir . '/includes/class-mypreview-geo-top-bar-customizer-message-bar-repeater.php');
			require_once ($this->dir . '/includes/class-mypreview-geo-top-bar-customizer-custom-background.php');

			/**
			 * Add the panel
			 */
			$wp_customize->add_panel('mypreview_geo_top_bar_pnl', array(
				'priority' => 30,
				'capability' => 'edit_theme_options',
				'title' => __('GEO Top Bar', 'mypreview-geo-top-bar') ,
				'description' => __('Customise the appearance and content of the sleek GEO message bar that is displayed on your website.', 'mypreview-geo-top-bar')
			));
			/**
			 * Add the sections
			 */
			$wp_customize->add_section('mypreview_geo_top_bar_layout_sec', array(
				'title' => __('Layout', 'mypreview-geo-top-bar') ,
				'priority' => 10,
				'panel' => 'mypreview_geo_top_bar_pnl',
			));
			$wp_customize->add_section('mypreview_geo_top_bar_typography_sec', array(
				'title' => __('Typography', 'mypreview-geo-top-bar') ,
				'priority' => 20,
				'panel' => 'mypreview_geo_top_bar_pnl',
			));
			$wp_customize->add_section('mypreview_geo_top_bar_color_scheme_sec', array(
				'title' => __('Color Scheme', 'mypreview-geo-top-bar') ,
				'priority' => 30,
				'panel' => 'mypreview_geo_top_bar_pnl',
			));
			$wp_customize->add_section('mypreview_geo_top_bar_responsiveness_sec', array(
				'title' => __('Responsiveness', 'mypreview-geo-top-bar') ,
				'priority' => 40,
				'panel' => 'mypreview_geo_top_bar_pnl',
			));
			$wp_customize->add_section('mypreview_geo_top_bar_message_bars_sec', array(
				'title' => __('Message Bars', 'mypreview-geo-top-bar') ,
				'priority' => 50,
				'panel' => 'mypreview_geo_top_bar_pnl',
			));
			$wp_customize->add_section('mypreview_geo_top_bar_test_mode_sec', array(
				'title' => __('Test Mode', 'mypreview-geo-top-bar') ,
				'priority' => 60,
				'panel' => 'mypreview_geo_top_bar_pnl',
			));
			/**
			 * Bar Background Image - Layout
			 */
			$wp_customize->register_control_type( 'MyPreview_GEO_Top_Bar_Customizer_Custom_Background' );

			$wp_customize->add_setting( 'mypreview_geo_top_bar_layout_bar_background_image_url', array(
				'sanitize_callback' => 'esc_url'
			) );
			$wp_customize->add_setting( 'mypreview_geo_top_bar_layout_bar_background_image_id', array(
				'sanitize_callback' => 'absint'
			) );
			$wp_customize->add_setting( 'mypreview_geo_top_bar_layout_bar_background_repeat', array(
					'default' => apply_filters('mypreview_geo_top_bar_layout_bar_background_repeat_default', 'repeat') ,
					'sanitize_callback' => 'sanitize_text_field'
			) );
			$wp_customize->add_setting( 'mypreview_geo_top_bar_layout_bar_background_size', array(
					'default' => apply_filters('mypreview_geo_top_bar_layout_bar_background_size_default', 'auto') ,
					'sanitize_callback' => 'sanitize_text_field'
			) );
			$wp_customize->add_setting( 'mypreview_geo_top_bar_layout_bar_background_position', array(
					'default' => apply_filters('mypreview_geo_top_bar_layout_bar_background_position_default', 'center center') ,
					'sanitize_callback' => 'sanitize_text_field'
			) );
			$wp_customize->add_setting( 'mypreview_geo_top_bar_layout_bar_background_attach', array(
					'default' => apply_filters('mypreview_geo_top_bar_layout_bar_background_attach_default', 'scroll') ,
					'sanitize_callback' => 'sanitize_text_field'
			) );
			$wp_customize->add_control(
				new MyPreview_GEO_Top_Bar_Customizer_Custom_Background(
					$wp_customize,
					'mypreview_geo_top_bar_layout_bar_background_control',
					array(
						'label'		=> __('Bar Background', 'mypreview-geo-top-bar') ,
						'section' => 'mypreview_geo_top_bar_layout_sec',
						'priority' => 10,
						'settings'    => array(
							'image_url' => 'mypreview_geo_top_bar_layout_bar_background_image_url',
							'image_id' => 'mypreview_geo_top_bar_layout_bar_background_image_id',
							'repeat' => 'mypreview_geo_top_bar_layout_bar_background_repeat',
							'size' => 'mypreview_geo_top_bar_layout_bar_background_size',
							'position' => 'mypreview_geo_top_bar_layout_bar_background_position',
							'attach' => 'mypreview_geo_top_bar_layout_bar_background_attach'
						)
					)
				)
			);
			/**
			 * Message Alignment - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_message_alignment', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_message_alignment_default', 'right') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_message_alignment_control', array(
				'label' => __('Message Alignment', 'mypreview-geo-top-bar') ,
				'description' => __('Adjust the message text alignment.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_message_alignment',
				'type' => 'select',
				'priority' => 20,
				'choices' => array(
					'left' => 'Left',
					'right' => 'Right',
					'center' => 'Center'
				)
			));
			/**
			 * Button Float - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_button_float', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_button_float_default', 'right') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_button_float_control', array(
				'label' => __('Button Float', 'mypreview-geo-top-bar') ,
				'description' => __('Adjust the button float.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_button_float',
				'type' => 'select',
				'priority' => 30,
				'choices' => array(
					'left' => 'Left',
					'right' => 'Right'
				)
			));
			/**
			 * Flag Position - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_flag_position', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_flag_position_default', 'after') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_flag_position_control', array(
				'label' => __('Flag Position', 'mypreview-geo-top-bar') ,
				'description' => __('Adjust the flag icon position.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_flag_position',
				'type' => 'select',
				'priority' => 40,
				'choices' => array(
					'after' => 'After the message text',
					'before' => 'Before the message text'
				)
			));
			/**
			 * Bar Spacing - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_bar_spacing', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_bar_spacing_default', 5) ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_bar_spacing_control', array(
				'label' => __('Bar Spacing', 'mypreview-geo-top-bar') ,
				'description' => __('Set the top and bottom padding of message bar.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_bar_spacing',
				'type' => 'range',
				'priority' => 50,
				'input_attrs' => array(
					'min' => 5,
					'max' => 50,
					'step' => 1
				)
			));
			/**
			 * Bar Divider Thickness - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_bar_divider_thickness', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_bar_divider_thickness_default', 2) ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_bar_divider_thickness_control', array(
				'label' => __('Bar Divider Thickness', 'mypreview-geo-top-bar') ,
				'description' => __('Set the width of the message bar divider.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_bar_divider_thickness',
				'type' => 'range',
				'priority' => 60,
				'input_attrs' => array(
					'min' => 0,
					'max' => 8,
					'step' => 1
				)
			));
			/**
			 * Slide Down Speed - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_slide_down_speed', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_slide_down_speed_default', 1000) ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_slide_down_speed_control', array(
				'label' => __('Slide Down Speed', 'mypreview-geo-top-bar') ,
				'description' => __('Determine how long the animation will run.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_slide_down_speed',
				'type' => 'range',
				'priority' => 70,
				'input_attrs' => array(
					'min' => 0,
					'max' => 7000,
					'step' => 100
				)
			));
			/**
			 * Button Border Radius - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_button_border_radius', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_button_border_radius_default', 4) ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_button_border_radius_control', array(
				'label' => __('Button Border Radius', 'mypreview-geo-top-bar') ,
				'description' => __('Adjust rounded corners of button.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_button_border_radius',
				'type' => 'range',
				'priority' => 80,
				'input_attrs' => array(
					'min' => 0,
					'max' => 30,
					'step' => 1
				)
			));
			/**
			 * Button Border Thickness - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_button_border_thickness', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_button_border_thickness_default', 1) ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_button_border_thickness_control', array(
				'label' => __('Button Border Thickness', 'mypreview-geo-top-bar') ,
				'description' => __('Adjust thickness of button border.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_button_border_thickness',
				'type' => 'range',
				'priority' => 90,
				'input_attrs' => array(
					'min' => 0,
					'max' => 8,
					'step' => 1
				)
			));
			/**
			 * Font-Family - Typography
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_typography_font_family', array(
				'default' => apply_filters('mypreview_geo_top_bar_typography_font_family_default', 'inherit') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control(new MyPreview_GEO_Top_Bar_Customizer_Google_Fonts($wp_customize, 'mypreview_geo_top_bar_typography_font_family_control', array(
				'label' => __('Font Family', 'mypreview-geo-top-bar') ,
				'description' => __('This font-family will be applied to message bar content and button element on your website.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_typography_sec',
				'settings' => 'mypreview_geo_top_bar_typography_font_family',
				'priority' => 10
			)));
			/**
			 * Font-Size - Typography
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_typography_message_font_size', array(
				'default' => apply_filters('mypreview_geo_top_bar_typography_message_font_size_default', 12) ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_typography_message_font_size_control', array(
				'label' => __('Message Font Size', 'mypreview-geo-top-bar') ,
				'description' => __('Adjust the message bar text font size.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_typography_sec',
				'settings' => 'mypreview_geo_top_bar_typography_message_font_size',
				'type' => 'range',
				'priority' => 20,
				'input_attrs' => array(
					'min' => 8,
					'max' => 20,
					'step' => 1
				)
			));
			/**
			 * Font-Weight - Typography
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_typography_message_font_weight', array(
				'default' => apply_filters('mypreview_geo_top_bar_typography_message_font_weight_default', 'normal') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_typography_message_font_weight_control', array(
				'label' => __('Message Font Weight', 'mypreview-geo-top-bar') ,
				'description' => __('Set how thick or thin characters in message text should be displayed.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_typography_sec',
				'settings' => 'mypreview_geo_top_bar_typography_message_font_weight',
				'type' => 'select',
				'priority' => 30,
				'choices' => array(
					'normal' => 'Normal',
					'bold' => 'Bold',
					'bolder' => 'Bolder',
					'lighter' => 'Lighter',
					'initial' => 'Initial',
					'inherit' => 'Inherit'
				)
			));
			/**
			 * Font-Style - Typography
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_typography_message_font_style', array(
				'default' => apply_filters('mypreview_geo_top_bar_typography_message_font_style_default', 'normal') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_typography_message_font_style_control', array(
				'label' => __('Message Font Style', 'mypreview-geo-top-bar') ,
				'description' => __('Specifie the font style for the message text.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_typography_sec',
				'settings' => 'mypreview_geo_top_bar_typography_message_font_style',
				'type' => 'select',
				'priority' => 40,
				'choices' => array(
					'normal' => 'Normal',
					'italic' => 'Italic',
					'oblique' => 'Oblique',
					'initial' => 'Initial',
					'inherit' => 'Inherit'
				)
			));
			/**
			 * Text-Transform - Typography
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_typography_message_text_transform', array(
				'default' => apply_filters('mypreview_geo_top_bar_typography_message_text_transform_default', 'none') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_typography_message_text_transform_control', array(
				'label' => __('Message Text Transform', 'mypreview-geo-top-bar') ,
				'description' => __('Specifie the capitalization of message text.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_typography_sec',
				'settings' => 'mypreview_geo_top_bar_typography_message_text_transform',
				'type' => 'select',
				'priority' => 50,
				'choices' => array(
					'none' => 'None',
					'capitalize' => 'Capitalize',
					'uppercase' => 'Uppercase',
					'lowercase' => 'Lowercase',
					'initial' => 'Initial',
					'inherit' => 'Inherit'
				)
			));
			/**
			 * Button Font-Size - Typography
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_typography_button_font_size', array(
				'default' => apply_filters('mypreview_geo_top_bar_typography_button_font_default', 16) ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_typography_button_font_size_control', array(
				'label' => __('Button Font Size', 'mypreview-geo-top-bar') ,
				'description' => __('Adjust the message bar button font size.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_typography_sec',
				'settings' => 'mypreview_geo_top_bar_typography_button_font_size',
				'type' => 'range',
				'priority' => 60,
				'input_attrs' => array(
					'min' => 8,
					'max' => 30,
					'step' => 1
				)
			));
			/**
			 * Button Font-Weight - Typography
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_typography_button_font_weight', array(
				'default' => apply_filters('mypreview_geo_top_bar_typography_button_font_weight_default', 'normal') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_typography_button_font_weight_control', array(
				'label' => __('Message Font Weight', 'mypreview-geo-top-bar') ,
				'description' => __('Set how thick or thin characters in button text should be displayed.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_typography_sec',
				'settings' => 'mypreview_geo_top_bar_typography_button_font_weight',
				'type' => 'select',
				'priority' => 70,
				'choices' => array(
					'normal' => 'Normal',
					'bold' => 'Bold',
					'bolder' => 'Bolder',
					'lighter' => 'Lighter',
					'initial' => 'Initial',
					'inherit' => 'Inherit'
				)
			));
			/**
			 * Button Font-Style - Typography
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_typography_button_font_style', array(
				'default' => apply_filters('mypreview_geo_top_bar_typography_button_font_style_default', 'normal') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_typography_button_font_style_control', array(
				'label' => __('Message Font Style', 'mypreview-geo-top-bar') ,
				'description' => __('Specifie the font style for the button text.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_typography_sec',
				'settings' => 'mypreview_geo_top_bar_typography_button_font_style',
				'type' => 'select',
				'priority' => 80,
				'choices' => array(
					'normal' => 'Normal',
					'italic' => 'Italic',
					'oblique' => 'Oblique',
					'initial' => 'Initial',
					'inherit' => 'Inherit'
				)
			));
			/**
			 * Button Text-Transform - Typography
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_typography_button_text_transform', array(
				'default' => apply_filters('mypreview_geo_top_bar_typography_button_text_transform_default', 'none') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_typography_button_text_transform_control', array(
				'label' => __('Button Text Transform', 'mypreview-geo-top-bar') ,
				'description' => __('Specifie the capitalization of button text.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_typography_sec',
				'settings' => 'mypreview_geo_top_bar_typography_button_text_transform',
				'type' => 'select',
				'priority' => 90,
				'choices' => array(
					'none' => 'None',
					'capitalize' => 'Capitalize',
					'uppercase' => 'Uppercase',
					'lowercase' => 'Lowercase',
					'initial' => 'Initial',
					'inherit' => 'Inherit'
				)
			));
			/**
			 * Bar Background - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_bar_background', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_bar_background_default', '#EFEFEF') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control(new MyPreview_GEO_Top_Bar_Customizer_Alpha_Color_Picker($wp_customize, 'mypreview_geo_top_bar_color_scheme_bar_background_control', array(
				'label' => __('Bar Background', 'mypreview-geo-top-bar') ,
				'description' => __('Will apply to modal box background too!', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_color_scheme_sec',
				'settings' => 'mypreview_geo_top_bar_color_scheme_bar_background',
				'priority' => 10,
				'show_opacity' => true,
				'palette' => array(
					'rgb(150, 50, 220)',
					'rgba(50,50,50,0.8)',
					'rgba(30,50,50,0.8)',
					'rgba(20,50,50,0.8)',
					'rgba(10,50,50,0.8)',
					'rgba(255, 255, 255, 0.2)',
					'#00CC99'
				)
			)));
			/**
			 * Bar Divider - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_bar_divider', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_bar_divider_default', '#FF3366') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control(new MyPreview_GEO_Top_Bar_Customizer_Alpha_Color_Picker($wp_customize, 'mypreview_geo_top_bar_color_scheme_bar_divider_control', array(
				'label' => __('Bar Divider', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_color_scheme_sec',
				'settings' => 'mypreview_geo_top_bar_color_scheme_bar_divider',
				'priority' => 20,
				'show_opacity' => true,
				'palette' => array(
					'rgb(150, 50, 220)',
					'rgba(50,50,50,0.8)',
					'rgba(30,50,50,0.8)',
					'rgba(20,50,50,0.8)',
					'rgba(10,50,50,0.8)',
					'rgba( 255, 255, 255, 0.2 )',
					'#00CC99'
				)
			)));
			/**
			 * Message Text - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_message_text', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_message_text_default', '#1A1A1A') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control(new MyPreview_GEO_Top_Bar_Customizer_Alpha_Color_Picker($wp_customize, 'mypreview_geo_top_bar_color_scheme_message_text_control', array(
				'label' => __('Message Text', 'mypreview-geo-top-bar') ,
				'description' => __('Will apply to modal box text too!', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_color_scheme_sec',
				'settings' => 'mypreview_geo_top_bar_color_scheme_message_text',
				'priority' => 30,
				'show_opacity' => true,
				'palette' => array(
					'rgb(150, 50, 220)',
					'rgba(50,50,50,0.8)',
					'rgba(30,50,50,0.8)',
					'rgba(20,50,50,0.8)',
					'rgba(10,50,50,0.8)',
					'rgba( 255, 255, 255, 0.2 )',
					'#00CC99'
				)
			)));
			/**
			 * Button Text - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_button_text', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_button_text_default', '#1A1A1A') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control(new MyPreview_GEO_Top_Bar_Customizer_Alpha_Color_Picker($wp_customize, 'mypreview_geo_top_bar_color_scheme_button_text_control', array(
				'label' => __('Button Text', 'mypreview-geo-top-bar') ,
				'description' => __('Will apply to modal box button text too!', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_color_scheme_sec',
				'settings' => 'mypreview_geo_top_bar_color_scheme_button_text',
				'priority' => 40,
				'show_opacity' => true,
				'palette' => array(
					'rgb(150, 50, 220)',
					'rgba(50,50,50,0.8)',
					'rgba(30,50,50,0.8)',
					'rgba(20,50,50,0.8)',
					'rgba(10,50,50,0.8)',
					'rgba( 255, 255, 255, 0.2 )',
					'#00CC99'
				)
			)));
			/**
			 * Button Text Hover - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_button_text_hover', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_button_text_hover_default', '#1A1A1A') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control(new MyPreview_GEO_Top_Bar_Customizer_Alpha_Color_Picker($wp_customize, 'mypreview_geo_top_bar_color_scheme_button_text_hover_control', array(
				'label' => __('Button Text Hover', 'mypreview-geo-top-bar') ,
				'description' => __('Works on hover event only!', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_color_scheme_sec',
				'settings' => 'mypreview_geo_top_bar_color_scheme_button_text_hover',
				'priority' => 50,
				'show_opacity' => true,
				'palette' => array(
					'rgb(150, 50, 220)',
					'rgba(50,50,50,0.8)',
					'rgba(30,50,50,0.8)',
					'rgba(20,50,50,0.8)',
					'rgba(10,50,50,0.8)',
					'rgba( 255, 255, 255, 0.2 )',
					'#00CC99'
				)
			)));
			/**
			 * Button Background - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_button_background', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_button_background_default', '#1A1A1A') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control(new MyPreview_GEO_Top_Bar_Customizer_Alpha_Color_Picker($wp_customize, 'mypreview_geo_top_bar_color_scheme_button_background_control', array(
				'label' => __('Button Background', 'mypreview-geo-top-bar') ,
				'description' => __('Will apply to modal box button text too!', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_color_scheme_sec',
				'settings' => 'mypreview_geo_top_bar_color_scheme_button_background',
				'priority' => 60,
				'show_opacity' => true,
				'palette' => array(
					'rgb(150, 50, 220)',
					'rgba(50,50,50,0.8)',
					'rgba(30,50,50,0.8)',
					'rgba(20,50,50,0.8)',
					'rgba(10,50,50,0.8)',
					'rgba( 255, 255, 255, 0.2 )',
					'#00CC99'
				)
			)));
			/**
			 * Button Background Hover - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_button_background_hover', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_button_background_hover_default', '#1A1A1A') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control(new MyPreview_GEO_Top_Bar_Customizer_Alpha_Color_Picker($wp_customize, 'mypreview_geo_top_bar_color_scheme_button_background_hover_control', array(
				'label' => __('Button Background Hover', 'mypreview-geo-top-bar') ,
				'description' => __('Works on hover event only!', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_color_scheme_sec',
				'settings' => 'mypreview_geo_top_bar_color_scheme_button_background_hover',
				'priority' => 70,
				'show_opacity' => true,
				'palette' => array(
					'rgb(150, 50, 220)',
					'rgba(50,50,50,0.8)',
					'rgba(30,50,50,0.8)',
					'rgba(20,50,50,0.8)',
					'rgba(10,50,50,0.8)',
					'rgba( 255, 255, 255, 0.2 )',
					'#00CC99'
				)
			)));
			/**
			 * Button Border - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_button_border', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_button_border_default', '#1A1A1A') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control(new MyPreview_GEO_Top_Bar_Customizer_Alpha_Color_Picker($wp_customize, 'mypreview_geo_top_bar_color_scheme_button_border_control', array(
				'label' => __('Button Border', 'mypreview-geo-top-bar') ,
				'description' => __('Will apply to modal box button border too!', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_color_scheme_sec',
				'settings' => 'mypreview_geo_top_bar_color_scheme_button_border',
				'priority' => 80,
				'show_opacity' => true,
				'palette' => array(
					'rgb(150, 50, 220)',
					'rgba(50,50,50,0.8)',
					'rgba(30,50,50,0.8)',
					'rgba(20,50,50,0.8)',
					'rgba(10,50,50,0.8)',
					'rgba( 255, 255, 255, 0.2 )',
					'#00CC99'
				)
			)));
			/**
			 * Button Border Hover - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_button_border_hover', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_button_border_hover_default', '#1A1A1A') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control(new MyPreview_GEO_Top_Bar_Customizer_Alpha_Color_Picker($wp_customize, 'mypreview_geo_top_bar_color_scheme_button_border_hover_control', array(
				'label' => __('Button Border Hover', 'mypreview-geo-top-bar') ,
				'description' => __('Works on hover event only!', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_color_scheme_sec',
				'settings' => 'mypreview_geo_top_bar_color_scheme_button_border_hover',
				'priority' => 90,
				'show_opacity' => true,
				'palette' => array(
					'rgb(150, 50, 220)',
					'rgba(50,50,50,0.8)',
					'rgba(30,50,50,0.8)',
					'rgba(20,50,50,0.8)',
					'rgba(10,50,50,0.8)',
					'rgba( 255, 255, 255, 0.2 )',
					'#00CC99'
				)
			)));
			/**
			 * Large Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_large_devices', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_large_devices_default', '') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_large_devices_control', array(
				'label' => __('Large Devices', 'mypreview-geo-top-bar') ,
				'description' => __('Desktops (≥1200px)', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_large_devices',
				'type' => 'select',
				'priority' => 10,
				'choices' => array(
					'' => 'Visible',
					'hide-large-devices' => 'Hidden'
				)
			));
			/**
			 * Medium Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_medium_devices', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_medium_devices_default', '') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_medium_devices_control', array(
				'label' => __('Medium Devices', 'mypreview-geo-top-bar') ,
				'description' => __('Desktops (≥992px)', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_medium_devices',
				'type' => 'select',
				'priority' => 20,
				'choices' => array(
					'' => 'Visible',
					'hide-medium-devices' => 'Hidden'
				)
			));
			/**
			 * Small Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_small_devices', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_small_devices_default', '') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_small_devices_control', array(
				'label' => __('Small Devices', 'mypreview-geo-top-bar') ,
				'description' => __('Desktops (≥768px)', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_small_devices',
				'type' => 'select',
				'priority' => 30,
				'choices' => array(
					'' => 'Visible',
					'hide-small-devices' => 'Hidden'
				)
			));
			/**
			 * Extra Small Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_extra_small_devices', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_extra_small_devices_default', '') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_extra_small_devices_control', array(
				'label' => __('Extra Small Devices', 'mypreview-geo-top-bar') ,
				'description' => __('Phones (<768px)', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_extra_small_devices',
				'type' => 'select',
				'priority' => 40,
				'choices' => array(
					'' => 'Visible',
					'hide-extra-small-devices' => 'Hidden'
				)
			));
			/**
			 * Message Bar(s) - Message Bars
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_message_bars_repeater', array(
				'default' => apply_filters('mypreview_geo_top_bar_message_bars_repeater_default', json_encode(array(
					array(
						'country' => '',
						'message' => '',
						'display_flag' => '',
						'button_text' => '',
						'button_url' => '',
						'enable' => 'on'
					)
				))) ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => array($this, 'sanitize_repeater') ,
				'validate_callback' => array($this, 'validate_repeater')
			));
			$wp_customize->add_control(new MyPreview_GEO_Top_Bar_Customizer_Message_Bars_Repeater($wp_customize, 'mypreview_geo_top_bar_message_bars_repeater_control', array(
				'label' => __('Message Bars', 'mypreview-geo-top-bar') ,
				'description' => __('The message bar repeater field allows you to create a set of different message bars based visitor\'s GEO location which can be repeated while editing content!', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_message_bars_sec',
				'settings' => 'mypreview_geo_top_bar_message_bars_repeater',
				'priority' => 10,
				'repeater_label' => __('Message', 'mypreview-geo-top-bar') ,
				'repeater_add_new' => __('Add Message', 'mypreview-geo-top-bar') ,
			) , array(
				'country' => array(
					'type' => 'text',
					'label' => __('Country', 'mypreview-geo-top-bar') ,
					'description' => __('Start by typing 3 or more character into search field or select country from drop down list.', 'mypreview-geo-top-bar')
				) ,
				'message' => array(
					'type' => 'textarea',
					'label' => __('Message', 'mypreview-geo-top-bar') ,
					'description' => __('Specify the text to be displayed in the message bar content area, including HTML.', 'mypreview-geo-top-bar')
				) ,
				'display_flag' => array(
					'type' => 'checkbox',
					'label' => __('Hide Country Flag', 'mypreview-geo-top-bar') ,
					'description' => __('Check this box if you want to hide the country flag.', 'mypreview-geo-top-bar')
				) ,
				'button_text' => array(
					'type' => 'text',
					'label' => __('Button Text', 'mypreview-geo-top-bar') ,
					'description' => __('Specify the text displayed on the button in the top bar component. If this field is empty, no button will be displayed.', 'mypreview-geo-top-bar')
				) ,
				'button_url' => array(
					'type' => 'text',
					'label' => __('Button URL', 'mypreview-geo-top-bar') ,
					'description' => __('Specify the URL the button in the message bar links to.', 'mypreview-geo-top-bar')
				) ,
				'enable' => array(
					'type' => 'switch',
					'label' => esc_attr__('Message Status', 'mypreview-geo-top-bar') ,
					'description' => __('Toggle the message display in the top bar component.', 'mypreview-geo-top-bar') ,
					'switch' => array(
						'on' => __('ON', 'mypreview-geo-top-bar') ,
						'off' => __('OFF', 'mypreview-geo-top-bar')
					) ,
					'default' => 'on'
				)
			)));
			/**
			 * Enable Test Mode - Test Mode
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_test_mode_toggle', array(
				'default' => apply_filters('mypreview_geo_top_bar_test_mode_toggle_default', false) ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => array(
					$this,
					'sanitize_checkbox'
				)
			));
			$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'mypreview_geo_top_bar_test_mode_toggle_control', array(
				'label' => __('Enable Test Mode', 'mypreview-geo-top-bar') ,
				'description' => __('Will allow you to customize the message bar without having to perform on the live website.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_test_mode_sec',
				'settings' => 'mypreview_geo_top_bar_test_mode_toggle',
				'type' => 'checkbox',
				'priority' => 10
			)));
			/**
			 * Message - Test Mode
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_test_mode_message', array(
				'default' => apply_filters('mypreview_geo_top_bar_test_mode_message_default', '') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'wp_kses_post'
			));
			// Abort if selective refresh is not available.
			if (isset($wp_customize->selective_refresh)):
				$wp_customize->get_setting('mypreview_geo_top_bar_test_mode_message')->transport = 'postMessage';
				$wp_customize->selective_refresh->add_partial('mypreview_geo_top_bar_test_mode_message', array(
					'selector' => '#geo-top-bar-wrapper .geo-top-bar-message span',
					'render_callback' => function ()
					{
						return wp_kses_post(get_theme_mod('mypreview_geo_top_bar_test_mode_message'));
					}
				));
			endif;
			$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'mypreview_geo_top_bar_test_mode_message_control', array(
				'label' => __('Message', 'mypreview-geo-top-bar') ,
				'description' => __('Tweak the message text, including HTML.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_test_mode_sec',
				'settings' => 'mypreview_geo_top_bar_test_mode_message',
				'type' => 'textarea',
				'priority' => 20,
				'active_callback' => array(
					$this,
					'is_test_mode_enabled'
				)
			)));
			/**
			 * Button Text - Test Mode
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_test_mode_btn_text', array(
				'default' => apply_filters('mypreview_geo_top_bar_test_mode_btn_text_default', '') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'mypreview_geo_top_bar_test_mode_btn_text_control', array(
				'label' => __('Button Text', 'mypreview-geo-top-bar') ,
				'description' => __('Specify the text displayed on the button in the top bar component. If this field is empty, no button will be displayed.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_test_mode_sec',
				'settings' => 'mypreview_geo_top_bar_test_mode_btn_text',
				'type' => 'text',
				'priority' => 30,
				'active_callback' => array(
					$this,
					'is_test_mode_enabled'
				)
			)));
			/**
			 * Button URL - Test Mode
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_test_mode_btn_url', array(
				'default' => apply_filters('mypreview_geo_top_bar_test_mode_btn_url_default', '') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'esc_url'
			));
			$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'mypreview_geo_top_bar_test_mode_btn_url_control', array(
				'label' => __('Button URL', 'mypreview-geo-top-bar') ,
				'description' => __('Specify the URL the button in the message bar links to.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_test_mode_sec',
				'settings' => 'mypreview_geo_top_bar_test_mode_btn_url',
				'type' => 'url',
				'priority' => 40,
				'active_callback' => array(
					$this,
					'is_test_mode_enabled'
				)
			)));
		}
		/**
		 * Checkbox sanitization callback.
		 *
		 * Sanitization callback for 'checkbox' type controls. This callback sanitizes `$checked`
		 * as a boolean value, either TRUE or FALSE.
		 *
		 * @since 1.0
		 */
		public function sanitize_checkbox($checked)

		{
			return ((isset($checked) && true == $checked) ? true : false);
		}
		/**
		 * Is test mode enabled callback.
		 *
		 * This callback checks the dependency in Customizer
		 * `$checked` as a boolean value, either TRUE or FALSE.
		 *
		 * @since 1.0
		 */
		public function is_test_mode_enabled($control)

		{
			if ($control->manager->get_setting('mypreview_geo_top_bar_test_mode_toggle')->value() == false):
				return false;
			endif;
			return true;
		}
		/**
		 * Repeater sanitization callback.
		 *
		 * Sanitization callback for 'repeater' type controls. 
		 * This callback sanitizes allowed HTML tag(s).
		 *
		 * @since 1.0
		 */
		public function sanitize_repeater($input)

		{
			$input_decoded = json_decode($input, true);
			$allowed_html = array(
				'br' => array() ,
				'em' => array() ,
				'strong' => array() ,
				'a' => array(
					'href' => array() ,
					'class' => array() ,
					'id' => array() ,
					'target' => array()
				) ,
				'button' => array(
					'class' => array() ,
					'id' => array()
				)
			);
			if (!empty($input_decoded)):
				foreach($input_decoded as $boxes => $box):
					foreach($box as $key => $value):
						$input_decoded[$boxes][$key] = wp_kses_post($value);
					endforeach;
				endforeach;
				return json_encode($input_decoded);
			endif;
			return $input;
		}
		/**
		 * Server side validation of a setting.
		 * Returns null or a WP_Error object. 
		 * 
		 * @link https://github.com/xwp/wp-customize-setting-validation
		 * @since 1.0
		 */
		public function validate_repeater($validity, $input)

		{
			$input_decoded = json_decode($input, true);
			$countries = array();
			if (!empty($input_decoded)):
				foreach($input_decoded as $input):
					$countries[] = $input['country'];
				endforeach;
				// Bail out, If the array has duplicates
				if(count(array_unique($countries)) < count($countries)):
					$validity->add('required', __('Deployment fails, Remove the duplicate country and save the changes again!', 'mypreview-geo-top-bar') );
				endif;
			endif;
		    return $validity;
		}
		/**
		 * Get user IP address
		 *
		 * @since 1.0
		 */
		private function get_ipaddress()

		{
			$ipaddress = '';
			if (!empty($_SERVER['HTTP_CLIENT_IP'])):
			    $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
			elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) :
			    $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else :
			    $ipaddress = $_SERVER['REMOTE_ADDR'];
			endif;
			return $ipaddress;
		}
		/**
		 * Find a match for user country and return back ISO2 code and notification message.
		 *
		 * @since 1.0
		 */
		private function get_countryISO2($ipaddress)

		{
		  	$country_code = getCountryFromIP($ipaddress, 'code');
			if (isset($country_code) && !empty($country_code)):
				// Return matched country data
				return $country_code;
			endif;
			return null;
		}
		/**
		 * Enqueue scripts/styles.
		 * 
		 * @since 1.0
		 */
		public function enqueue()

		{
			ob_start();
			global $post;
			$is_geo_top_bar_disabled = esc_attr(get_post_meta(get_the_ID() , '_myprevie_geo_top_bar_metabox_checkbox', true));
			// Bail out, If GEO top bar already disabled by meta box field
			if ($is_geo_top_bar_disabled):
				return;
			endif;
			$test_mode_message = get_theme_mod('mypreview_geo_top_bar_test_mode_message', '');
			// Layout
			$layout_button_float = get_theme_mod('mypreview_geo_top_bar_layout_button_float', 'right');
			$layout_flag_position = get_theme_mod('mypreview_geo_top_bar_layout_flag_position', 'after');
			$layout_slide_down_speed = get_theme_mod('mypreview_geo_top_bar_layout_slide_down_speed', 1000);
			// Responsiveness Classes
			$visibility_classes = $this->get_responsive_classes();
			wp_register_style('geo-top-bar-style', $this->public_assets_url . 'css/mypreview-geo-top-bar.css', array() , GEO_TOP_BAR_VERSION, 'all');
			wp_register_script('geo-top-bar-js', $this->public_assets_url . 'js/mypreview-geo-top-bar.js', array() , GEO_TOP_BAR_VERSION, true);
			// Check if test mode activated
			$is_test_mode_enabled = get_theme_mod('mypreview_geo_top_bar_test_mode_toggle', false);
			if (current_user_can('edit_theme_options') && $is_test_mode_enabled):
				// Retrieve test mode message
				$test_mode_message = get_theme_mod('mypreview_geo_top_bar_test_mode_message');
				$test_mode_btn_text = get_theme_mod('mypreview_geo_top_bar_test_mode_btn_text', '');
				$test_mode_btn_url = get_theme_mod('mypreview_geo_top_bar_test_mode_btn_url', '');
				if (isset($test_mode_message) && !empty($test_mode_message)):
					// Get user's currect country code
					$country_code = esc_html(strtolower(trim(getCountryFromIP($this->get_ipaddress() , 'code'))));
					if (isset($country_code) && !empty($country_code)):
						// Display GEO Top Bar
						$this->display_geo_top_bar($test_mode_message, $country_code, $layout_slide_down_speed, $test_mode_btn_text, $test_mode_btn_url, $layout_button_float, $layout_flag_position, $is_test_mode_enabled, $visibility_classes);
					endif;
				endif;
				return;
			endif;
			// Test mode isn't activated, move forward to retrieve message bar(s)
			$message_bars = json_decode(get_theme_mod('mypreview_geo_top_bar_message_bars_repeater'));
			if (is_array($message_bars) || is_object($message_bars)):
				// Skip test mode if user doesn't have enough permission to access customizer
				if (!current_user_can('edit_theme_options')):
					$is_test_mode_enabled = false;
				endif;
				$default_country_name = '';
				$default_country_code = '';
				if ($this->is_cookied('geo_top_bar_default_country') && $this->is_cookied('geo_top_bar_default_country_code')):
					$default_country_name = esc_html(strtolower(trim($this->get_cookie('geo_top_bar_default_country'))));
					$default_country_code = esc_html(strtolower(trim($this->get_cookie('geo_top_bar_default_country_code'))));
				endif;
				// Display top bar based on visitor's location
				if (empty($default_country_name) && empty($default_country_code)):
					// Get user's currect country code
					$country_code = esc_html(strtolower(trim(getCountryFromIP($this->get_ipaddress() , 'code'))));
					$country_name = esc_html(strtolower(trim(getCountryFromIP($this->get_ipaddress() , 'NamE'))));
					if (isset($country_code) && !empty($country_code)):
						foreach($message_bars as $message_bar):
							$country = esc_html(strtolower(trim($message_bar->country)));
							$current_status = esc_html(strtolower(trim($message_bar->enable)));
							$message = (!empty($message_bar->message) ? wp_kses_post($message_bar->message) : '');
							if (isset($message) && !empty($message) && strpos($country, $country_name) !== false && $current_status !== 'off'):
								$display_flag = (!empty($message_bar->display_flag) ? esc_attr($message_bar->display_flag) : '');
								if (!empty($display_flag) && $display_flag === 'yes'):
									$layout_flag_position = '';
								endif;
								$btn_text = (!empty($message_bar->button_text) ? esc_attr($message_bar->button_text) : '');
								$btn_url = (!empty($message_bar->button_url) ? esc_url($message_bar->button_url) : '');
								// Display GEO Top Bar
								$this->display_geo_top_bar($message, $country_code, $layout_slide_down_speed, $btn_text, $btn_url, $layout_button_float, $layout_flag_position, $is_test_mode_enabled, $visibility_classes);
								return;
							endif;
						endforeach;
					endif;
					// Display top bar with pre-selected country
					elseif (isset($default_country_name, $default_country_code) && !empty($default_country_name) && !empty($default_country_code)):
						$country_code = $default_country_code;
						foreach($message_bars as $message_bar):
							$country = esc_html(strtolower(trim($message_bar->country)));
							$current_status = esc_html(strtolower(trim($message_bar->enable)));
							$message = (!empty($message_bar->message) ? wp_kses_post($message_bar->message) : '');
							if (isset($message) && !empty($message) && $country === $default_country_name && $current_status !== 'off'):
								$display_flag = (!empty($message_bar->display_flag) ? esc_attr($message_bar->display_flag) : '');
								if (!empty($display_flag) && $display_flag === 'yes'):
									$layout_flag_position = '';
								endif;
								$btn_text = (!empty($message_bar->button_text) ? esc_attr($message_bar->button_text) : '');
								$btn_url = (!empty($message_bar->button_url) ? esc_url($message_bar->button_url) : '');
								// Display GEO Top Bar
								$this->display_geo_top_bar($message, $country_code, $layout_slide_down_speed, $btn_text, $btn_url, $layout_button_float, $layout_flag_position, $is_test_mode_enabled, $visibility_classes);
								return;
							endif;
						endforeach;
					endif;
					return;
				endif;
				ob_get_clean();
		}
		/**
		 * 
		 * 
		 * @since 1.0
		 */
		private function display_geo_top_bar($message, $country_code, $slide_down, $button_text, $button_url, $button_float, $flag_position, $is_test_mode_enabled, $visibility_classes)

		{
			// Skip test mode if user doesn't have enough permission to access customizer
			if (!current_user_can('edit_theme_options')):
				$is_test_mode_enabled = false;
			endif;
			// Empty array of registered countries
			$all_defined_countries = array();
			// If user doesn't have enough permission to access customizer
			if(!current_user_can('edit_theme_options')):
				$all_defined_countries = $this->get_all_defined_countries();
			// If user can manage theme options and plugin isn't in test mode
			elseif(current_user_can('edit_theme_options') && !$is_test_mode_enabled):
				$all_defined_countries = $this->get_all_defined_countries();
			endif;
			// Check if we need to send any visibility classes
			array_filter($visibility_classes);
			if(!empty($visibility_classes) && is_array($visibility_classes) && count($visibility_classes) > 0):
				$visibility_classes = json_encode($visibility_classes);
			else:
				$visibility_classes = '';
			endif;
			$this->add_inline_style();
			wp_localize_script('geo-top-bar-js', 'mypreview_geo_top_bar_vars', array(
				'message' => $message,
				'country_code' => $country_code,
				'slide_down' => $slide_down,
				'button_text' => $button_text,
				'button_url' => $button_url,
				'button_float' => $button_float,
				'flag_position' => $flag_position,
				'test_mode' => $is_test_mode_enabled,
				'visibility_classes' => $visibility_classes,
				'all_defined_countries' => $all_defined_countries
			));
			wp_enqueue_script('geo-top-bar-js');
		}
		/**
		 * Appending additional CSS to an existing "geo-top-bar-style" stylesheet
		 * 
		 * @link https://codex.wordpress.org/Function_Reference/wp_add_inline_style
		 * @since 1.0
		 */
		private function add_inline_style()

		{
			// Layout
			$layout_bar_background_image_url = get_theme_mod('mypreview_geo_top_bar_layout_bar_background_image_url', '');
			$layout_bar_background_repeat = get_theme_mod('mypreview_geo_top_bar_layout_bar_background_repeat', 'repeat');
			$layout_bar_background_size = get_theme_mod('mypreview_geo_top_bar_layout_bar_background_size', 'auto');
			$layout_bar_background_position = get_theme_mod('mypreview_geo_top_bar_layout_bar_background_position', 'center-center');
			$layout_bar_background_attach = get_theme_mod('mypreview_geo_top_bar_layout_bar_background_attach', 'scroll');
			$layout_message_alignment = get_theme_mod('mypreview_geo_top_bar_layout_message_alignment', 'right');
			$layout_bar_spacing = get_theme_mod('mypreview_geo_top_bar_layout_bar_spacing', 5);
			$layout_bar_divider_thickness = get_theme_mod('mypreview_geo_top_bar_layout_bar_divider_thickness', 2);
			$layout_button_border_radius = get_theme_mod('mypreview_geo_top_bar_layout_button_border_radius', 4);
			$layout_button_border_thickness = get_theme_mod('mypreview_geo_top_bar_layout_button_border_thickness', 1);
			// Typography
			$typography_font_family = get_theme_mod('mypreview_geo_top_bar_typography_font_family', 'inherit');
			$typography_message_font_size = get_theme_mod('mypreview_geo_top_bar_typography_message_font_size', 12);
			$typography_message_font_weight = get_theme_mod('mypreview_geo_top_bar_typography_message_font_weight', 'normal');
			$typography_message_font_style = get_theme_mod('mypreview_geo_top_bar_typography_message_font_style', 'normal');
			$typography_message_text_transform = get_theme_mod('mypreview_geo_top_bar_typography_message_text_transform', 'none');
			$typography_button_font_size = get_theme_mod('mypreview_geo_top_bar_typography_button_font_size', 16);
			$typography_button_font_weight = get_theme_mod('mypreview_geo_top_bar_typography_button_font_weight', 'normal');
			$typography_button_font_style = get_theme_mod('mypreview_geo_top_bar_typography_button_font_style', 'normal');
			$typography_button_text_transform = get_theme_mod('mypreview_geo_top_bar_typography_button_text_transform', 'none');
			// Color Scheme
			$color_scheme_bar_background = get_theme_mod('mypreview_geo_top_bar_color_scheme_bar_background', '#EFEFEF');
			$color_scheme_bar_divider = get_theme_mod('mypreview_geo_top_bar_color_scheme_bar_divider', '#FF3366');
			$color_scheme_message_text = get_theme_mod('mypreview_geo_top_bar_color_scheme_message_text', '#1A1A1A');
			$color_scheme_button_text = get_theme_mod('mypreview_geo_top_bar_color_scheme_button_text', '#1A1A1A');
			$color_scheme_button_text_hover = get_theme_mod('mypreview_geo_top_bar_color_scheme_button_text_hover', '#1A1A1A');
			$color_scheme_button_background = get_theme_mod('mypreview_geo_top_bar_color_scheme_button_background', '#1A1A1A');
			$color_scheme_button_background_hover = get_theme_mod('mypreview_geo_top_bar_color_scheme_mypreview_geo_top_bar_color_scheme_button_background_hover', '#1A1A1A');
			$color_scheme_button_border = get_theme_mod('mypreview_geo_top_bar_color_scheme_button_border', '#1A1A1A');
			$color_scheme_button_border_hover = get_theme_mod('mypreview_geo_top_bar_color_scheme_button_border_hover', '#1A1A1A');
			$inline_style = "
                #geo-top-bar-wrapper {
					background-color: {$color_scheme_bar_background};
					border-bottom: {$layout_bar_divider_thickness}px solid {$color_scheme_bar_divider};
					padding-top: {$layout_bar_spacing}px;
					padding-bottom: {$layout_bar_spacing}px;
					background-image: url('{$layout_bar_background_image_url}');
					background-repeat: {$layout_bar_background_repeat};
					background-size: {$layout_bar_background_size};
					background-position: {$layout_bar_background_position};
					background-attachment: {$layout_bar_background_attach};
                } 
                #geo-top-bar-wrapper .geo-top-bar-content {
                	text-align: {$layout_message_alignment};
                }
                #geo-top-bar-wrapper .geo-top-bar-message {
					color: {$color_scheme_message_text};
					font-size: {$typography_message_font_size}px;
					font-family: {$typography_font_family};
					font-weight: {$typography_message_font_weight};
					font-style: {$typography_message_font_style};
					text-transform: {$typography_message_text_transform};
                }
                #geo-top-bar-wrapper .geo-top-bar-button,
                #geo-top-bar-modal input[name='geo_top_bar_default_country_submit'] {
					color: {$color_scheme_button_text};
					font-size: {$typography_button_font_size}px;
					font-family: {$typography_font_family};
					font-weight: {$typography_button_font_weight};
					font-style: {$typography_button_font_style};
					text-transform: {$typography_button_text_transform};
					background-color: {$color_scheme_button_background};
					border: {$layout_button_border_thickness}px solid {$color_scheme_button_border};
					-moz-border-radius: {$layout_button_border_radius}px;
					-o-border-radius: {$layout_button_border_radius}px;
					-ms-border-radius: {$layout_button_border_radius}px;
					border-radius: {$layout_button_border_radius}px;
                }
                #geo-top-bar-wrapper .geo-top-bar-button:hover,
                #geo-top-bar-modal input[name='geo_top_bar_default_country_submit'] {
					color: {$color_scheme_button_text_hover};
					background-color: {$color_scheme_button_background_hover};
                }
                #geo-top-bar-modal {
                	background-color: {$color_scheme_bar_background};
                }
                #geo-top-bar-modal input[name='geo_top_bar_default_country']{
                	font-family: {$typography_font_family};
                }
                #geo-top-bar-modal label[for='geo_top_bar_default_country']{
                	font-family: {$typography_font_family};
                }";
            if (isset($typography_font_family) && !empty($typography_font_family) && $typography_font_family !== 'inherit'):
            	wp_enqueue_style('geo-top-bar-google-font', add_query_arg(apply_filters('mypreview_geo_top_bar_font_family_default', array(
				'family' => urlencode($typography_font_family),
				'subset' => urlencode('latin,latin-ext')
				)) , 'https://fonts.googleapis.com/css') , array() , GEO_TOP_BAR_VERSION);
            endif;
            wp_enqueue_style('geo-top-bar-style');
	        wp_add_inline_style('geo-top-bar-style', wp_strip_all_tags($inline_style));
		}
		/**
		 * Get the list of all registered/validated and saved countries
		 * 
		 * @since 1.0
		 */
		private function get_all_defined_countries()

		{
			$all_defined_countries = array();
			$message_bars = json_decode(get_theme_mod('mypreview_geo_top_bar_message_bars_repeater'));
			if (is_array($message_bars) || is_object($message_bars)):
				foreach($message_bars as $message_bar):
					$all_defined_countries[] = esc_html(trim($message_bar->country));
				endforeach;
				$all_defined_countries = json_encode($all_defined_countries);
			endif;
			return $all_defined_countries;
		}
		/**
		 * Get all responsivness classes
		 *
		 * @since 1.0
		 */
		private function get_responsive_classes()

		{
			$visibility_classes = array();
			$responsiveness_large_devices = get_theme_mod('mypreview_geo_top_bar_responsiveness_large_devices', '');
			$responsiveness_medium_devices = get_theme_mod('mypreview_geo_top_bar_responsiveness_medium_devices', '');
			$responsiveness_small_devices = get_theme_mod('mypreview_geo_top_bar_responsiveness_small_devices', '');
			$responsiveness_extra_small_devices = get_theme_mod('mypreview_geo_top_bar_responsiveness_extra_small_devices', '');
			if(isset($responsiveness_large_devices) && !empty($responsiveness_large_devices)):
				$visibility_classes[] = $responsiveness_large_devices;
			endif;
			if(isset($responsiveness_medium_devices) && !empty($responsiveness_medium_devices)):
				$visibility_classes[] = $responsiveness_medium_devices;
			endif;
			if(isset($responsiveness_small_devices) && !empty($responsiveness_small_devices)):
				$visibility_classes[] = $responsiveness_small_devices;
			endif;
			if(isset($responsiveness_extra_small_devices) && !empty($responsiveness_extra_small_devices)):
				$visibility_classes[] = $responsiveness_extra_small_devices;
			endif;
			return $visibility_classes;
		}
		/**
		 * Select default country from drop-down list
		 * jQuery modal box which triggers with clicking on flag icon 
		 * 
		 * @since 1.0
		 */
		public function country_select_modal()

		{
			ob_start();
			global $post;
			$is_geo_top_bar_disabled = esc_attr(get_post_meta(get_the_ID() , '_myprevie_geo_top_bar_metabox_checkbox', true));
			// Bail out, If GEO top bar already disabled by meta box field
			if ($is_geo_top_bar_disabled):
				return;
			endif;
			$is_test_mode_enabled = get_theme_mod('mypreview_geo_top_bar_test_mode_toggle', false);
			// Bail out, If GEO top bar is in test mode
			if (current_user_can('edit_theme_options') && $is_test_mode_enabled):
				return;
			endif;
			?>
			<div id="geo-top-bar-modal" style="display:none;">
				<form name="geo-top-bar-select-default-country-form" id="geo-top-bar-select-default-country-form" method="POST">
					<div class="geo-top-bar-fields">
						<label for="geo_top_bar_default_country"><?php esc_html_e('Where are you from?', 'mypreview-geo-top-bar'); ?></label>
						<input type="text" name="geo_top_bar_default_country" id="geo_top_bar_default_country" required="required" />
						<input type="hidden" name="geo_top_bar_default_country_code" id="geo_top_bar_default_country_code" />
						<input type="submit" name="geo_top_bar_default_country_submit" value="<?php esc_html_e('Go', 'mypreview-geo-top-bar'); ?>" />
					</div><!-- /geo-top-bar-fields -->
				</form><!-- /geo-top-bar-select-default-country-form -->
			</div><!-- /geo-top-bar-modal -->
			<?php
			ob_end_flush();
		}
		/**
		 * Store selected country name and ISO2 code in cookies
		 *
		 * @since 1.0
		 */
		public function default_country_cookie()

		{
			if (isset($_POST['geo_top_bar_default_country'], $_POST['geo_top_bar_default_country_code']) && !empty($_POST['geo_top_bar_default_country']) && !empty($_POST['geo_top_bar_default_country_code'])):
				$default_country = sanitize_text_field($_POST['geo_top_bar_default_country']);
				$default_country_code = sanitize_text_field($_POST['geo_top_bar_default_country_code']);
				setcookie($this->get_cookie_country_name() , $default_country, time() + (86400 * 999) , COOKIEPATH, COOKIE_DOMAIN, false);
				setcookie($this->get_cookie_country_code() , $default_country_code, time() + (86400 * 999) , COOKIEPATH, COOKIE_DOMAIN, false);
				// Now refresh so the header changes get captured
				header('Refresh:0');
				exit;
			endif;
		}
		/**
		 * Country name cookie handle
		 *
		 * @since 1.0
		 */
		private function get_cookie_country_name()

		{
			return 'geo_top_bar_default_country';
		}
		/**
		 * Country code (ISO2) cookie handle
		 *
		 * @since 1.0
		 */
		private function get_cookie_country_code()

		{
			return 'geo_top_bar_default_country_code';
		}
		/**
		 * Get cookie value
		 *
		 * @since 1.0
		 */
		private function get_cookie($cookie_name)

		{
			return $_COOKIE[$cookie_name];
		}
		/**
		 * Check if cookie already exists
		 *
		 * @since 1.0
		 */
		private function is_cookied($cookie_name)

		{
			return isset($_COOKIE[$cookie_name]);
		}
	}
endif;
/**
 * Returns the main instance of The MyPreview GEO Top Bar - Class to prevent the need to use globals.
 *
 * @since 1.0
 */
if (!function_exists('mypreview_geo_top_bar_initialization')):
	function mypreview_geo_top_bar_initialization()
	{
		return MyPreview_GEO_Top_Bar::instance();
	}
	mypreview_geo_top_bar_initialization();
endif;
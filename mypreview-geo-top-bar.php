<?php
/*
Plugin Name: 	GEO Top Bar
Plugin URI:  	https://codecanyon.net/item/geo-top-bar/19819968
Description: 	Display a highly customizable sleek message bar on your website. An ideal choice for informing visitors from specific GEO locations.
Version:     	1.1.0
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
define('GEO_TOP_BAR_VERSION', '1.1.0');
if (!class_exists('MyPreview_GEO_Top_Bar')):
	/**
	 * The MyPreview GEO Top Bar - Class
	 */
	final class MyPreview_GEO_Top_Bar

	{
		private $file;
		private $dir;
		private $post_types;
		private $wp_customize;
		private $public_assets_url;
		private $admin_assets_url;
		private $customizer_hijacked = false;
		private static $_instance = null;
		/**
		 * Main MyPreview_GEO_Top_Bar instance
		 *
		 * Ensures only one instance of MyPreview_GEO_Top_Bar is loaded or can be loaded.
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
		 * @since 1.1.0
		 */
		protected function __construct()

		{
			$this->customizer_hijacked = false;
			$this->file = plugin_basename(__FILE__);
			$this->dir = dirname(__FILE__);
			$this->post_types = apply_filters('mypreview_geo_top_bar_post_types', array('post', 'page', 'product'));
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
			add_action('customize_controls_print_scripts', array(
				$this,
				'customizer_enqueue'
			) , 99);
			add_action('customize_preview_init', array(
				$this,
				'customizer_live_preview'
			) , 99);
			add_action('customize_register', array(
				$this,
				'customize_register'
			) , 10, 1);
			add_action('customize_register', array(
				$this,
				'is_customizer_hijacked'
			) , 100, 1);
			add_action('wp_enqueue_scripts', array(
				$this,
				'enqueue'
			) , 99);
			add_action('wp_footer', array(
				$this,
				'country_select_modal'
			) , 10);
			add_action('init', array(
				$this,
				'default_country_cookie'
			) , 1);
			add_action('customize_controls_print_scripts', array(
				$this,
				'controls_print_scripts'
			) , 10);
			add_action('customize_register', array(
				$this,
				'init_portability'
			) , 999999);
			add_action('wp_ajax_preview_country', array(
				$this,
				'ajax_preview_country'
			) , 10);
			add_action('wp_ajax_reset_plugin_settings', array(
				$this,
				'ajax_reset_plugin_settings'
			) , 10);
			add_filter('plugin_action_links_' . plugin_basename($this->file) , array(
				$this,
				'settings_link'
			) , 10);
			add_action('admin_notices', array(
				$this,
				'activation'
			) , 10);
			register_deactivation_hook(__FILE__, array(
				$this,
				'deactivation'
			));
		}
		/**
		 * Cloning instances of this class is forbidden.
		 *
		 * @since 1.0
		 */
		protected function __clone()

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
			$post_types = $this->post_types;
			if(isset($post_types) && !empty($post_types) && is_array($post_types)):
				foreach($post_types as $post_type):
					add_meta_box('mypreview-geo-top-bar-metabox', __('GEO Top Bar', 'mypreview-geo-top-bar') , array(
					$this,
					'render_meta_box'
				) , $post_type, 'normal', 'default');
				endforeach;
			endif;
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
			wp_nonce_field('mypreview_geo_top_bar_metabox_checkbox_toggle', 'mypreview_geo_top_bar_metabox_checkbox_toggle_nonce');
			$post_types = $this->post_types;
			$hide_geo_top_bar = esc_attr(get_post_meta($post->ID, '_myprevie_geo_top_bar_metabox_checkbox', true));
			if (isset($post_types) && !empty($post_types) && is_array($post_types) && in_array($post->post_type, $post_types)):
				?>
					<p>
						<input type="checkbox" id="_myprevie_geo_top_bar_metabox_checkbox" name="_myprevie_geo_top_bar_metabox_checkbox" value="true" <?php checked('true', $hide_geo_top_bar); ?>>
						<label for="_myprevie_geo_top_bar_metabox_checkbox"><strong><?php esc_html_e('Hide GEO Top Bar', 'mypreview-geo-top-bar'); ?></strong></label>
						<br /><br />
						<em><?php esc_html_e('This checkbox will hide the GEO Top Bar from view.', 'mypreview-geo-top-bar'); ?></em>
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
			// Bail out, If nonce check fails.
			if (!isset($_POST['mypreview_geo_top_bar_metabox_checkbox_toggle_nonce']) || !wp_verify_nonce($_POST['mypreview_geo_top_bar_metabox_checkbox_toggle_nonce'], 'mypreview_geo_top_bar_metabox_checkbox_toggle')):
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
			$var['_myprevie_geo_top_bar_metabox_checkbox'] = array_key_exists('_myprevie_geo_top_bar_metabox_checkbox', $_POST) ? esc_attr($_POST['_myprevie_geo_top_bar_metabox_checkbox']) : '';
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
		public function customizer_enqueue()

		{
			wp_enqueue_style('geo-top-bar-country-select-style', $this->admin_assets_url . 'css/mypreview-geo-top-bar-country-select.min.css', array() , GEO_TOP_BAR_VERSION, 'all');
			wp_enqueue_style('geo-top-bar-alpha-color-picker-style', $this->admin_assets_url . 'css/mypreview-geo-top-bar-alpha-color-picker.min.css', array() , GEO_TOP_BAR_VERSION, 'all');
			wp_enqueue_style('geo-top-bar-customizer-styles', $this->admin_assets_url . 'css/mypreview-geo-top-bar-customizer.css', array() , GEO_TOP_BAR_VERSION, 'all');
			wp_enqueue_script('geo-top-bar-country-select-js', $this->admin_assets_url . 'js/mypreview-geo-top-bar-country-select.min.js', array() , GEO_TOP_BAR_VERSION, true);
			wp_enqueue_script('geo-top-bar-alpha-color-picker-js', $this->admin_assets_url . 'js/mypreview-geo-top-bar-alpha-color-picker.min.js', array() , GEO_TOP_BAR_VERSION, true);
			wp_enqueue_script('geo-top-bar-custom-background-js', $this->admin_assets_url . 'js/mypreview-geo-top-bar-custom-background.min.js', array() , GEO_TOP_BAR_VERSION, true);
			wp_register_script('geo-top-bar-customizer-js', $this->admin_assets_url . 'js/mypreview-geo-top-bar-customizer.js', array() , GEO_TOP_BAR_VERSION, true);
			wp_localize_script('geo-top-bar-customizer-js', 'mypreview_geo_top_bar_customizer_vars', array(
				'customizer_url' => esc_url(wp_customize_url()) ,
				'customizer_autofocus_pnl_url' => $this->customizer_url('panel', 'mypreview_geo_top_bar_pnl') ,
				'export_nonce' => wp_create_nonce('mypreview_geo_top_bar_export_nonce') ,
				'test_msg_bar_max_char' => __('Plugin prefers message with max %s chars.', 'mypreview-geo-top-bar') ,
				'test_msg_bar_content_req' => __('Plugin prefers to have a message to display in the bar.', 'mypreview-geo-top-bar') ,
				'msg_bars_content_req' => __('Plugin prefers to have a message to display in the bar. %country_name%', 'mypreview-geo-top-bar') ,
				'msg_import_file_req' => __('Invalid File format. You should be uploading a JSON file.', 'mypreview-geo-top-bar') ,
				'preview_nonce' => wp_create_nonce('mypreview_geo_top_bar_preview_country_nonce') ,
				'reset_btn' => __('Reset', 'mypreview-geo-top-bar') ,
				'reset_confirmation' => __('WARNING! Are you sure you wish to remove all settings?', 'mypreview-geo-top-bar') ,
				'reset_nonce' => wp_create_nonce('mypreview_geo_top_bar_reset_nonce')
			));
			wp_enqueue_script('geo-top-bar-customizer-js');
		}
		/**
		 * Register live preview script to see the changes instantly.
		 *
		 * @since 1.0
		 */
		public function customizer_live_preview()

		{
			// Check to see if default country modified and passed by cookies
			if ($this->is_cookied($this->get_cookie_preview_country_name()) && $this->is_cookied($this->get_cookie_preview_country_code())):
				$country_name = strtolower(trim($this->get_cookie($this->get_cookie_preview_country_name())));
				$country_code = strtolower(trim($this->get_cookie($this->get_cookie_preview_country_code())));
			// Get user's currect country code
			else:
				$country_name = strtolower(trim(getCountryFromIP($this->get_ipaddress() , 'NamE')));
				$country_code = strtolower(trim(getCountryFromIP($this->get_ipaddress() , 'code')));
			endif;
			wp_register_script('geo-top-bar-customizer-live-preview', $this->admin_assets_url . 'js/mypreview-geo-top-bar-customizer-live-preview.js', array(
				'jquery',
				'customize-preview'
			) , GEO_TOP_BAR_VERSION, true);
			wp_localize_script('geo-top-bar-customizer-live-preview', 'mypreview_geo_top_bar_customizer_live_vars', array(
				'current_country_name' => $country_name ,
				'current_country_code' => $country_code ,
				'slide_down' => absint(get_option('mypreview_geo_top_bar_layout_slide_down_speed', 1000)) ,
				'button_float' => esc_attr(get_option('mypreview_geo_top_bar_layout_button_float', 'right')) ,
				'flag_position' => esc_attr(get_option('mypreview_geo_top_bar_layout_flag_position', 'before')) ,
				'test_mode' => esc_attr(get_option('mypreview_geo_top_bar_test_mode_toggle', false)) ,
				'visibility_classes' => $this->get_responsive_classes()
			));
			wp_enqueue_script('geo-top-bar-customizer-live-preview');
		}
		/**
		 * Add copyright section to WordPress customizer.
		 *
		 * @since 1.0
		 */
		public function customize_register($wp_customize)

		{
			// Store a reference to "WP_Customize_Manager" instance
			$this->wp_customize = $wp_customize;
			/**
			 * Custom customizer control classes.
			 *
			 * @since 1.0
			 */
			require_once ($this->dir . '/includes/class-mypreview-geo-top-bar-customizer-google-fonts.php');
			require_once ($this->dir . '/includes/class-mypreview-geo-top-bar-customizer-alpha-color-picker.php');
			require_once ($this->dir . '/includes/class-mypreview-geo-top-bar-customizer-message-bar-repeater.php');
			require_once ($this->dir . '/includes/class-mypreview-geo-top-bar-customizer-custom-background.php');
			require_once ($this->dir . '/includes/class-mypreview-geo-top-bar-customizer-portability.php');

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
			$wp_customize->add_section('mypreview_geo_top_bar_portability_sec', array(
				'title' => __('Portability', 'mypreview-geo-top-bar') ,
				'priority' => 70,
				'panel' => 'mypreview_geo_top_bar_pnl',
			));
			/**
			 * Bar Background Image - Layout
			 */
			$wp_customize->register_control_type( 'MyPreview_GEO_Top_Bar_Customizer_Custom_Background' );

			$wp_customize->add_setting( 'mypreview_geo_top_bar_layout_bar_background_image_url', array(
				'type' => 'option',
				'sanitize_callback' => 'esc_url'
			) );
			$wp_customize->add_setting( 'mypreview_geo_top_bar_layout_bar_background_image_id', array(
				'type' => 'option',
				'sanitize_callback' => 'absint'
			) );
			$wp_customize->add_setting( 'mypreview_geo_top_bar_layout_bar_background_repeat', array(
					'default' => apply_filters('mypreview_geo_top_bar_layout_bar_background_repeat_default', 'repeat') ,
					'type' => 'option',
					'transport' => 'postMessage',
					'sanitize_callback' => 'sanitize_text_field'
			) );
			$wp_customize->add_setting( 'mypreview_geo_top_bar_layout_bar_background_size', array(
					'default' => apply_filters('mypreview_geo_top_bar_layout_bar_background_size_default', 'auto') ,
					'type' => 'option',
					'transport' => 'postMessage',
					'sanitize_callback' => 'sanitize_text_field'
			) );
			$wp_customize->add_setting( 'mypreview_geo_top_bar_layout_bar_background_position', array(
					'default' => apply_filters('mypreview_geo_top_bar_layout_bar_background_position_default', 'center center') ,
					'type' => 'option',
					'transport' => 'postMessage',
					'sanitize_callback' => 'sanitize_text_field'
			) );
			$wp_customize->add_setting( 'mypreview_geo_top_bar_layout_bar_background_attach', array(
					'default' => apply_filters('mypreview_geo_top_bar_layout_bar_background_attach_default', 'scroll') ,
					'type' => 'option',
					'transport' => 'postMessage',
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
			 * Sticky Header - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_hide_on_scroll', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_hide_on_scroll_default', '') ,
				'type' => 'option',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_hide_on_scroll_control', array(
				'label' => __('Hide bar on Scroll', 'mypreview-geo-top-bar') ,
				'description' => __('This feature can be useful if you are using sticky navigation or header on your website.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_hide_on_scroll',
				'type' => 'select',
				'priority' => 20,
				'choices' => array(
					'' => __('Disabled', 'mypreview-geo-top-bar'),
					'enabled' => __('Enabled', 'mypreview-geo-top-bar')
				)
			));
			/**
			 * Message Alignment - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_message_alignment', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_message_alignment_default', 'center') ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_message_alignment_control', array(
				'label' => __('Message Alignment', 'mypreview-geo-top-bar') ,
				'description' => __('Adjust the message text alignment.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_message_alignment',
				'type' => 'select',
				'priority' => 30,
				'choices' => array(
					'left' => __('Left', 'mypreview-geo-top-bar'),
					'right' => __('Right', 'mypreview-geo-top-bar'),
					'center' => __('Center', 'mypreview-geo-top-bar'),
				)
			));
			/**
			 * Button Float - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_button_float', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_button_float_default', 'right') ,
				'type' => 'option',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_button_float_control', array(
				'label' => __('Button Float', 'mypreview-geo-top-bar') ,
				'description' => __('Adjust the button float.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_button_float',
				'type' => 'select',
				'priority' => 40,
				'choices' => array(
					'left' => __('Left', 'mypreview-geo-top-bar'),
					'right' => __('Right', 'mypreview-geo-top-bar')
				)
			));
			/**
			 * Flag Position - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_flag_position', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_flag_position_default', 'before') ,
				'type' => 'option',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_flag_position_control', array(
				'label' => __('Flag Position', 'mypreview-geo-top-bar') ,
				'description' => __('Adjust the flag icon position.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_flag_position',
				'type' => 'select',
				'priority' => 50,
				'choices' => array(
					'after' => __('After the message text', 'mypreview-geo-top-bar'),
					'before' => __('Before the message text', 'mypreview-geo-top-bar')
				)
			));
			/**
			 * Flag Size - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_flag_size', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_flag_size_default', 24) ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_flag_size_control', array(
				'label' => __('Flag Size', 'mypreview-geo-top-bar') ,
				'description' => __('Set the country flag size.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_flag_size',
				'type' => 'range',
				'priority' => 60,
				'input_attrs' => array(
					'min' => 20,
					'max' => 50,
					'step' => 2
				)
			));
			/**
			 * Bar Top Spacing - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_bar_top_spacing', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_bar_top_spacing_default', 16) ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_bar_top_spacing_control', array(
				'label' => __('Bar Top Spacing', 'mypreview-geo-top-bar') ,
				'description' => __('Set the top padding of message bar.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_bar_top_spacing',
				'type' => 'range',
				'priority' => 70,
				'input_attrs' => array(
					'min' => 1,
					'max' => 70,
					'step' => 1
				)
			));
			/**
			 * Bar Bottom Spacing - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_bar_bottom_spacing', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_bar_bottom_spacing_default', 16) ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_bar_bottom_spacing_control', array(
				'label' => __('Bar Bottom Spacing', 'mypreview-geo-top-bar') ,
				'description' => __('Set the bottom padding of message bar.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_bar_bottom_spacing',
				'type' => 'range',
				'priority' => 80,
				'input_attrs' => array(
					'min' => 1,
					'max' => 70,
					'step' => 1
				)
			));
			/**
			 * Bar Divider Thickness - Layout
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_layout_bar_divider_thickness', array(
				'default' => apply_filters('mypreview_geo_top_bar_layout_bar_divider_thickness_default', 1) ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_bar_divider_thickness_control', array(
				'label' => __('Bar Divider Thickness', 'mypreview-geo-top-bar') ,
				'description' => __('Set the width of the message bar divider.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_bar_divider_thickness',
				'type' => 'range',
				'priority' => 90,
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
				'type' => 'option',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_slide_down_speed_control', array(
				'label' => __('Slide Down Speed', 'mypreview-geo-top-bar') ,
				'description' => __('Determine how long the animation will run.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_slide_down_speed',
				'type' => 'range',
				'priority' => 100,
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
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_button_border_radius_control', array(
				'label' => __('Button Border Radius', 'mypreview-geo-top-bar') ,
				'description' => __('Adjust rounded corners of button.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_button_border_radius',
				'type' => 'range',
				'priority' => 110,
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
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_layout_button_border_thickness_control', array(
				'label' => __('Button Border Thickness', 'mypreview-geo-top-bar') ,
				'description' => __('Adjust thickness of button border.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_layout_sec',
				'settings' => 'mypreview_geo_top_bar_layout_button_border_thickness',
				'type' => 'range',
				'priority' => 120,
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
				'type' => 'option',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control(new MyPreview_GEO_Top_Bar_Customizer_Google_Fonts($wp_customize, 'mypreview_geo_top_bar_typography_font_family_control', array(
				'label' => __('Font Family', 'mypreview-geo-top-bar') ,
				'description' => __('Select from a list of Google Fonts, the best free fonts available.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_typography_sec',
				'settings' => 'mypreview_geo_top_bar_typography_font_family',
				'priority' => 10
			)));
			/**
			 * Font-Size - Typography
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_typography_message_font_size', array(
				'default' => apply_filters('mypreview_geo_top_bar_typography_message_font_size_default', 14) ,
				'type' => 'option',
				'transport' => 'postMessage',
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
				'default' => apply_filters('mypreview_geo_top_bar_typography_message_font_weight_default', 'bold') ,
				'type' => 'option',
				'transport' => 'postMessage',
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
					'normal' => __('Normal', 'mypreview-geo-top-bar'),
					'bold' => __('Bold', 'mypreview-geo-top-bar'),
					'bolder' => __('Bolder', 'mypreview-geo-top-bar'),
					'lighter' => __('Lighter', 'mypreview-geo-top-bar'),
					'initial' => __('Initial', 'mypreview-geo-top-bar'),
					'inherit' => __('Inherit', 'mypreview-geo-top-bar')
				)
			));
			/**
			 * Font-Style - Typography
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_typography_message_font_style', array(
				'default' => apply_filters('mypreview_geo_top_bar_typography_message_font_style_default', 'italic') ,
				'type' => 'option',
				'transport' => 'postMessage',
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
					'normal' => __('Normal', 'mypreview-geo-top-bar'),
					'italic' => __('Italic', 'mypreview-geo-top-bar'),
					'oblique' => __('Oblique', 'mypreview-geo-top-bar'),
					'initial' => __('Initial', 'mypreview-geo-top-bar'),
					'inherit' => __('Inherit', 'mypreview-geo-top-bar')
				)
			));
			/**
			 * Text-Transform - Typography
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_typography_message_text_transform', array(
				'default' => apply_filters('mypreview_geo_top_bar_typography_message_text_transform_default', 'uppercase') ,
				'type' => 'option',
				'transport' => 'postMessage',
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
					'none' => __('None', 'mypreview-geo-top-bar'),
					'capitalize' => __('Capitalize', 'mypreview-geo-top-bar'),
					'uppercase' => __('Uppercase', 'mypreview-geo-top-bar'),
					'lowercase' => __('Lowercase', 'mypreview-geo-top-bar'),
					'initial' => __('Initial', 'mypreview-geo-top-bar'),
					'inherit' => __('Inherit', 'mypreview-geo-top-bar')
				)
			));
			/**
			 * Button Font-Size - Typography
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_typography_button_font_size', array(
				'default' => apply_filters('mypreview_geo_top_bar_typography_button_font_default', 14) ,
				'type' => 'option',
				'transport' => 'postMessage',
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
				'default' => apply_filters('mypreview_geo_top_bar_typography_button_font_weight_default', 'bolder') ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_typography_button_font_weight_control', array(
				'label' => __('Button Font Weight', 'mypreview-geo-top-bar') ,
				'description' => __('Set how thick or thin characters in button text should be displayed.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_typography_sec',
				'settings' => 'mypreview_geo_top_bar_typography_button_font_weight',
				'type' => 'select',
				'priority' => 70,
				'choices' => array(
					'normal' => __('Normal', 'mypreview-geo-top-bar'),
					'bold' => __('Bold', 'mypreview-geo-top-bar'),
					'bolder' => __('Bolder', 'mypreview-geo-top-bar'),
					'lighter' => __('Lighter', 'mypreview-geo-top-bar'),
					'initial' => __('Initial', 'mypreview-geo-top-bar'),
					'inherit' => __('Inherit', 'mypreview-geo-top-bar')
				)
			));
			/**
			 * Button Font-Style - Typography
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_typography_button_font_style', array(
				'default' => apply_filters('mypreview_geo_top_bar_typography_button_font_style_default', 'normal') ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_typography_button_font_style_control', array(
				'label' => __('Button Font Style', 'mypreview-geo-top-bar') ,
				'description' => __('Specifie the font style for the button text.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_typography_sec',
				'settings' => 'mypreview_geo_top_bar_typography_button_font_style',
				'type' => 'select',
				'priority' => 80,
				'choices' => array(
					'normal' => __('Normal', 'mypreview-geo-top-bar'),
					'italic' => __('Italic', 'mypreview-geo-top-bar'),
					'oblique' => __('Oblique', 'mypreview-geo-top-bar'),
					'initial' => __('Initial', 'mypreview-geo-top-bar'),
					'inherit' => __('Inherit', 'mypreview-geo-top-bar')
				)
			));
			/**
			 * Button Text-Transform - Typography
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_typography_button_text_transform', array(
				'default' => apply_filters('mypreview_geo_top_bar_typography_button_text_transform_default', 'uppercase') ,
				'type' => 'option',
				'transport' => 'postMessage',
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
					'capitalize' => __('Capitalize', 'mypreview-geo-top-bar'),
					'uppercase' => __('Uppercase', 'mypreview-geo-top-bar'),
					'lowercase' => __('Lowercase', 'mypreview-geo-top-bar'),
					'initial' => __('Initial', 'mypreview-geo-top-bar'),
					'inherit' => __('Inherit', 'mypreview-geo-top-bar')
				)
			));
			/**
			 * Bar Background - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_bar_background', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_bar_background_default', '#F5F5F5') ,
				'type' => 'option',
				'transport' => 'postMessage',
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
					'#F5F5F5',
					'#377ca8',
					'#181818',
					'#6c84e5',
					'#f9bf3b',
					'#2b2b2b',
					'#112430'
				)
			)));
			/**
			 * Bar Divider - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_bar_divider', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_bar_divider_default', '#EFEFEF') ,
				'type' => 'option',
				'transport' => 'postMessage',
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
					'#EFEFEF',
					'#18496a',
					'rgba(0,0,0,0.25)',
					'#4772d9',
					'rgba(219,154,2,0.5)',
					'rgba(0,0,0,0.3)',
					'rgba(0,0,0,0.3)'
				)
			)));
			/**
			 * Message Text - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_message_text', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_message_text_default', '#606060') ,
				'type' => 'option',
				'transport' => 'postMessage',
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
					'#606060',
					'#ecf8ff',
					'#bfbfbf',
					'#ffffff',
					'#0a0000',
					'#999999',
					'rgba(255,255,255,0.7)'
				)
			)));
			/**
			 * Button Text - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_button_text', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_button_text_default', '#FFFFFF') ,
				'type' => 'option',
				'transport' => 'postMessage',
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
					'#FFFFFF',
					'#35698a',
					'#333333',
					'#ffffff',
					'#f9f9f9',
					'#00ab6b',
					'#fafafa'
				)
			)));
			/**
			 * Button Text Hover - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_button_text_hover', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_button_text_hover_default', '#FFFFFF') ,
				'type' => 'option',
				'transport' => 'postMessage',
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
					'#FFFFFF',
					'#e9f6fe',
					'#333333',
					'rgba(255,255,255,0.95)',
					'#222222',
					'#fafafa',
					'rgba(255,255,255,0.8)'
				)
			)));
			/**
			 * Button Background - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_button_background', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_button_background_default', '#77CDE3') ,
				'type' => 'option',
				'transport' => 'postMessage',
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
					'#77CDE3',
					'#e9f6fe',
					'#f7f7f7',
					'#4772d9',
					'#222222',
					'rgba(10,10,10,0)',
					'#ea8832'
				)
			)));
			/**
			 * Button Background Hover - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_button_background_hover', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_button_background_hover_default', '#51BFDB') ,
				'type' => 'option',
				'transport' => 'postMessage',
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
					'#51BFDB',
					'#35698a',
					'#e6e6e6',
					'#1f47a5',
					'#ffffff',
					'#00ab6b',
					'#d87116'
				)
			)));
			/**
			 * Button Border - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_button_border', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_button_border_default', '#77CDE3') ,
				'type' => 'option',
				'transport' => 'postMessage',
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
					'#77CDE3',
					'#35698a',
					'#cccccc',
					'#1f47a5',
					'rgba(0,0,0,0.5)',
					'#00ab6b',
					'rgba(255,255,255,0.3)'
				)
			)));
			/**
			 * Button Border Hover - Color Scheme
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_color_scheme_button_border_hover', array(
				'default' => apply_filters('mypreview_geo_top_bar_color_scheme_button_border_hover_default', '#51BFDB') ,
				'type' => 'option',
				'transport' => 'postMessage',
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
					'#51BFDB',
					'#e9f6fe',
					'#bbbbbb',
					'#1f47a5',
					'rgba(214,214,214,0.5)',
					'#00ab6b',
					'rgba(0,0,0,0.2)'
				)
			)));
			/**
			 * Large Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_large_devices', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_large_devices_default', '') ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_large_devices_control', array(
				'label' => __('Large Devices', 'mypreview-geo-top-bar') ,
				'description' => __('≥1200px', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_large_devices',
				'type' => 'select',
				'priority' => 10,
				'choices' => array(
					'' => __('Visible', 'mypreview-geo-top-bar'),
					'hide-large-devices' => __('Hidden', 'mypreview-geo-top-bar')
				)
			));
			/**
			 * Max Width Large Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_large_devices_max_width', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_large_devices_max_width_default', 1170) ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_large_devices_max_width_control', array(
				'description' => __('Set the  maximum width of message bar.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_large_devices_max_width',
				'type' => 'range',
				'priority' => 20,
				'input_attrs' => array(
					'min' => 1040,
					'max' => 1920,
					'step' => 1
				)
			));
			/**
			 * Horizontal Spacing Large Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_large_devices_horizontal_spacing', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_large_devices_horizontal_spacing_default', 30) ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_large_devices_horizontal_spacing_control', array(
				'description' => __('Set the left and right padding of message bar.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_large_devices_horizontal_spacing',
				'type' => 'range',
				'priority' => 30,
				'input_attrs' => array(
					'min' => 0,
					'max' => 50,
					'step' => 1
				)
			));
			/**
			 * Medium Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_medium_devices', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_medium_devices_default', '') ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_medium_devices_control', array(
				'label' => __('Medium Devices', 'mypreview-geo-top-bar') ,
				'description' => __('<1200px AND >768px', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_medium_devices',
				'type' => 'select',
				'priority' => 40,
				'choices' => array(
					'' => __('Visible', 'mypreview-geo-top-bar'),
					'hide-medium-devices' => __('Hidden', 'mypreview-geo-top-bar'),
				)
			));
			/**
			 * Max Width Medium Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_medium_devices_max_width', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_medium_devices_max_width_default', 970) ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_medium_devices_max_width_control', array(
				'description' => __('Set the  maximum width of message bar.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_medium_devices_max_width',
				'type' => 'range',
				'priority' => 50,
				'input_attrs' => array(
					'min' => 840,
					'max' => 1200,
					'step' => 1
				)
			));
			/**
			 * Horizontal Spacing Medium Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_medium_devices_horizontal_spacing', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_medium_devices_horizontal_spacing_default', 30) ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_medium_devices_horizontal_spacing_control', array(
				'description' => __('Set the left and right padding of message bar.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_medium_devices_horizontal_spacing',
				'type' => 'range',
				'priority' => 60,
				'input_attrs' => array(
					'min' => 0,
					'max' => 50,
					'step' => 1
				)
			));
			/**
			 * Small Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_small_devices', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_small_devices_default', '') ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_small_devices_control', array(
				'label' => __('Small Devices', 'mypreview-geo-top-bar') ,
				'description' => __('≤768px AND >480px', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_small_devices',
				'type' => 'select',
				'priority' => 70,
				'choices' => array(
					'' => __('Visible', 'mypreview-geo-top-bar'),
					'hide-small-devices' => __('Hidden', 'mypreview-geo-top-bar')
				)
			));
			/**
			 * Max Width Small Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_small_devices_max_width', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_small_devices_max_width_default', 750) ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_small_devices_max_width_control', array(
				'description' => __('Set the  maximum width of message bar.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_small_devices_max_width',
				'type' => 'range',
				'priority' => 80,
				'input_attrs' => array(
					'min' => 520,
					'max' => 760,
					'step' => 1
				)
			));
			/**
			 * Horizontal Spacing Small Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_small_devices_horizontal_spacing', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_small_devices_horizontal_spacing_default', 30) ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_small_devices_horizontal_spacing_control', array(
				'description' => __('Set the left and right padding of message bar.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_small_devices_horizontal_spacing',
				'type' => 'range',
				'priority' => 90,
				'input_attrs' => array(
					'min' => 0,
					'max' => 50,
					'step' => 1
				)
			));
			/**
			 * Extra Small Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_extra_small_devices', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_extra_small_devices_default', '') ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_text_field'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_extra_small_devices_control', array(
				'label' => __('Extra Small Devices', 'mypreview-geo-top-bar') ,
				'description' => __('≤480px', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_extra_small_devices',
				'type' => 'select',
				'priority' => 100,
				'choices' => array(
					'' => __('Visible', 'mypreview-geo-top-bar'),
					'hide-extra-small-devices' => __('Hidden', 'mypreview-geo-top-bar')
				)
			));
			/**
			 * Max Width Small Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_extra_small_devices_max_width', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_extra_small_devices_max_width_default', 450) ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_extra_small_devices_max_width_control', array(
				'description' => __('Set the  maximum width of message bar.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_extra_small_devices_max_width',
				'type' => 'range',
				'priority' => 110,
				'input_attrs' => array(
					'min' => 200,
					'max' => 480,
					'step' => 1
				)
			));
			/**
			 * Horizontal Spacing Small Devices - Responsiveness
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_responsiveness_extra_small_devices_horizontal_spacing', array(
				'default' => apply_filters('mypreview_geo_top_bar_responsiveness_extra_small_devices_horizontal_spacing_default', 15) ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'absint'
			));
			$wp_customize->add_control('mypreview_geo_top_bar_responsiveness_extra_small_devices_horizontal_spacing_control', array(
				'description' => __('Set the left and right padding of message bar.', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_responsiveness_sec',
				'settings' => 'mypreview_geo_top_bar_responsiveness_extra_small_devices_horizontal_spacing',
				'type' => 'range',
				'priority' => 120,
				'input_attrs' => array(
					'min' => 0,
					'max' => 30,
					'step' => 1
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
						'button_target' => '_self',
						'enable' => 'on'
					)
				))) ,
				'type' => 'option',
				'transport' => 'postMessage',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => array($this, 'sanitize_repeater') ,
				'validate_callback' => array($this, 'validate_repeater')
			));
			$wp_customize->add_control(new MyPreview_GEO_Top_Bar_Customizer_Message_Bars_Repeater($wp_customize, 'mypreview_geo_top_bar_message_bars_repeater_control', array(
				'label' => __('Message Bars', 'mypreview-geo-top-bar') ,
				'description' => __('The message bar repeater field allows you to create a set of different message bars based on visitor\'s GEO location which can be repeated while editing content!', 'mypreview-geo-top-bar') ,
				'section' => 'mypreview_geo_top_bar_message_bars_sec',
				'settings' => 'mypreview_geo_top_bar_message_bars_repeater',
				'priority' => 10,
				'repeater_label' => __('Message', 'mypreview-geo-top-bar') ,
				'repeater_add_new' => __('Add Message', 'mypreview-geo-top-bar') ,
			) , array(
				'country' => array(
					'type' => 'text',
					'label' => __('Country', 'mypreview-geo-top-bar') ,
					'description' => __('Start by typing 3 or more character into the search field or select country from drop down list.', 'mypreview-geo-top-bar')
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
				'button_target' => array(
					'type' => 'select',
					'label' => __('URL Target', 'mypreview-geo-top-bar') ,
					'description' => __('Specify where to open the linked document.', 'mypreview-geo-top-bar') ,
					'options' => array(
						'_self' => __('Same frame as it was clicked', 'mypreview-geo-top-bar') ,
						'_blank' => __('Open in a new window or tab', 'mypreview-geo-top-bar')
					) ,
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
			 * Fake Refresh - Message Bars
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_message_bars_fake_refresh', array(
				'capability' => 'edit_theme_options',
				'sanitize_callback' => array(
					$this,
					'sanitize_checkbox'
				)
			));
			$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'mypreview_geo_top_bar_message_bars_fake_refresh_control', array(
				'section' => 'mypreview_geo_top_bar_message_bars_sec',
				'settings' => 'mypreview_geo_top_bar_message_bars_fake_refresh',
				'type' => 'checkbox',
				'priority' => 20
			)));
			/**
			 * Enable Test Mode - Test Mode
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_test_mode_toggle', array(
				'default' => apply_filters('mypreview_geo_top_bar_test_mode_toggle_default', false) ,
				'type' => 'option',
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
				'type' => 'option',
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
						return wp_kses_post(get_option('mypreview_geo_top_bar_test_mode_message'));
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
				'type' => 'option',
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
				'type' => 'option',
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
			/**
			 * Export & Import - Portability
			 */
			$wp_customize->add_setting('mypreview_geo_top_bar_portability_settings', array(
				'default' => apply_filters('mypreview_geo_top_bar_portability_settings_default', '') ,
				'type' => 'none',
				'capability' => 'edit_theme_options'
			));
			$wp_customize->add_control(new MyPreview_GEO_Top_Bar_Customizer_Portability($wp_customize, 'mypreview_geo_top_bar_portability_settings_control', array(
				'section' => 'mypreview_geo_top_bar_portability_sec',
				'settings' => 'mypreview_geo_top_bar_portability_settings',
				'priority' => 1
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
					$validity->add('required', __('Deployment failed, Remove the duplicate country and save the changes again!', 'mypreview-geo-top-bar') );
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
		 * Check if another plugin is already hijacked the WordPress customizer or not!
		 *
		 * @since 1.0
		 */
		public function is_customizer_hijacked($wp_customize)

		{
			$geo_top_bar_pnl = $wp_customize->get_panel('mypreview_geo_top_bar_pnl');
		    if ($geo_top_bar_pnl):
		        $this->customizer_hijacked = true;
		    endif;
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
			global $wp_customize;
			// Bail out, If customizer is already hijacked by another plugin!
			if(! $this->customizer_hijacked && isset($wp_customize)):
				return;
			endif;
			$is_geo_top_bar_disabled = esc_attr(get_post_meta(get_the_ID() , '_myprevie_geo_top_bar_metabox_checkbox', true));
			// Bail out, If GEO top bar already disabled by meta box field!
			if ($is_geo_top_bar_disabled):
				return;
			endif;
			$test_mode_message = wp_kses_post(get_option('mypreview_geo_top_bar_test_mode_message', ''));
			// Layout
			$layout_button_float = esc_attr(get_option('mypreview_geo_top_bar_layout_button_float', 'right'));
			$layout_flag_position = esc_attr(get_option('mypreview_geo_top_bar_layout_flag_position', 'before'));
			$hide_on_scroll = esc_attr(get_option('mypreview_geo_top_bar_layout_hide_on_scroll', ''));
			$layout_slide_down_speed = absint(get_option('mypreview_geo_top_bar_layout_slide_down_speed', 1000));
			// Responsiveness Classes
			$visibility_classes = $this->get_responsive_classes();
			wp_register_style('geo-top-bar-style', $this->public_assets_url . 'css/mypreview-geo-top-bar.css', array() , GEO_TOP_BAR_VERSION, 'all');
			wp_register_script('geo-top-bar-js', $this->public_assets_url . 'js/mypreview-geo-top-bar.js', array() , GEO_TOP_BAR_VERSION, true);
			// Check if test mode activated
			$is_test_mode_enabled = esc_html(get_option('mypreview_geo_top_bar_test_mode_toggle', false));
			if (current_user_can('edit_theme_options') && $is_test_mode_enabled):
				// Retrieve test mode message
				$test_mode_message = wp_kses_post(get_option('mypreview_geo_top_bar_test_mode_message', ''));
				$test_mode_btn_text = esc_html(get_option('mypreview_geo_top_bar_test_mode_btn_text', ''));
				$test_mode_btn_url = esc_url(get_option('mypreview_geo_top_bar_test_mode_btn_url', ''));
				$test_mode_btn_target = esc_attr(apply_filters('mypreview_geo_top_bar_test_mode_btn_target', '_self'));
				if (isset($test_mode_message) && !empty($test_mode_message)):
					// Get user's currect country code
					$country_code = esc_html(strtolower(trim(getCountryFromIP($this->get_ipaddress() , 'code'))));
					if (isset($country_code) && !empty($country_code)):
						// Display GEO Top Bar
						$this->display_geo_top_bar($test_mode_message, $country_code, $layout_slide_down_speed, $test_mode_btn_text, $test_mode_btn_url, $test_mode_btn_target, $layout_button_float, $hide_on_scroll, $layout_flag_position, $is_test_mode_enabled, $visibility_classes);
					endif;
				endif;
				return;
			endif;
			// Test mode isn't activated, move forward to retrieve message bar(s)
			$message_bars = json_decode(get_option('mypreview_geo_top_bar_message_bars_repeater'));
			if (is_array($message_bars) || is_object($message_bars)):
				// Skip test mode if user doesn't have enough permission to manage customizer
				if (!current_user_can('edit_theme_options')):
					$is_test_mode_enabled = false;
				endif;
				$default_country_name = '';
				$default_country_code = '';
				// Check to see if default country modified and passed by cookies
				if ($this->is_cookied($this->get_cookie_country_name()) && $this->is_cookied($this->get_cookie_country_code()) && !isset($wp_customize)):
					$default_country_name = strtolower(trim($this->get_cookie($this->get_cookie_country_name())));
					$default_country_code = strtolower(trim($this->get_cookie($this->get_cookie_country_code())));
				endif;
				// Set preview cookie if previewing throughout the customizer
				if ($this->is_cookied($this->get_cookie_preview_country_name()) && $this->is_cookied($this->get_cookie_preview_country_code()) && isset($wp_customize)):
					$default_country_name = strtolower(trim($this->get_cookie($this->get_cookie_preview_country_name())));
					$default_country_code = strtolower(trim($this->get_cookie($this->get_cookie_preview_country_code())));
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
								$btn_target = (!empty($message_bar->button_target) ? esc_attr($message_bar->button_target) : '');
								// Display GEO Top Bar
								$this->display_geo_top_bar($message, $country_code, $layout_slide_down_speed, $btn_text, $btn_url, $btn_target, $layout_button_float, $hide_on_scroll, $layout_flag_position, $is_test_mode_enabled, $visibility_classes);
								return;
							endif;
						endforeach;
					endif;
					// Display top bar with pre-selected country
					elseif (isset($default_country_name, $default_country_code) && !empty($default_country_name) && !empty($default_country_code)):
						$country_code = $default_country_code;
						foreach($message_bars as $message_bar):
							$country = esc_attr(strtolower(trim($message_bar->country)));
							$current_status = esc_attr(strtolower(trim($message_bar->enable)));
							$message = (!empty($message_bar->message) ? wp_kses_post($message_bar->message) : '');
							if (isset($message) && !empty($message) && $country === $default_country_name && $current_status !== 'off'):
								$display_flag = (!empty($message_bar->display_flag) ? esc_attr($message_bar->display_flag) : '');
								if (!empty($display_flag) && $display_flag === 'yes'):
									$layout_flag_position = '';
								endif;
								$btn_text = (!empty($message_bar->button_text) ? esc_html($message_bar->button_text) : '');
								$btn_url = (!empty($message_bar->button_url) ? esc_url($message_bar->button_url) : '');
								$btn_target = (!empty($message_bar->button_target) ? esc_attr($message_bar->button_target) : '');
								// Display GEO Top Bar
								$this->display_geo_top_bar($message, $country_code, $layout_slide_down_speed, $btn_text, $btn_url, $btn_target, $layout_button_float, $hide_on_scroll, $layout_flag_position, $is_test_mode_enabled, $visibility_classes);
								return;
							endif;
						endforeach;
					endif;
					return;
				endif;
				ob_get_clean();
		}
		/**
		 * Pass PHP Data to Javascript Variable.
		 * "mypreview_geo_top_bar_vars"
		 * 
		 * @since 1.0
		 */
		private function display_geo_top_bar($message, $country_code, $slide_down, $button_text, $button_url, $button_target, $button_float, $hide_on_scroll, $flag_position, $is_test_mode_enabled, $visibility_classes)

		{
			// Skip test mode if user doesn't have enough permission to manage customizer
			if (!current_user_can('edit_theme_options')):
				$is_test_mode_enabled = false;
			endif;
			// Empty array of registered countries
			$all_defined_countries = array();
			// If user doesn't have enough permission to manage customizer
			if(!current_user_can('edit_theme_options')):
				$all_defined_countries = $this->get_all_defined_countries();
			// If user can manage theme options and plugin isn't in test mode
			elseif(current_user_can('edit_theme_options') && !$is_test_mode_enabled):
				$all_defined_countries = $this->get_all_defined_countries();
			endif;
			// Print inline stylesheet before closing </head> tag
			$this->add_inline_style();
			// Ready to display GEO Top Bar, thus send data within the JS variable
			wp_localize_script('geo-top-bar-js', 'mypreview_geo_top_bar_vars', array(
				'message' => $message,
				'country_code' => $country_code,
				'slide_down' => $slide_down,
				'button_text' => $button_text,
				'button_url' => $button_url,
				'button_target' => $button_target,
				'button_float' => $button_float,
				'flag_position' => $flag_position,
				'hide_on_scroll' => $hide_on_scroll,
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
			$layout_bar_background_image_url = esc_url(get_option('mypreview_geo_top_bar_layout_bar_background_image_url', ''));
			$layout_bar_background_repeat = esc_attr(get_option('mypreview_geo_top_bar_layout_bar_background_repeat', 'repeat'));
			$layout_bar_background_size = esc_attr(get_option('mypreview_geo_top_bar_layout_bar_background_size', 'auto'));
			$layout_bar_background_position = esc_attr(get_option('mypreview_geo_top_bar_layout_bar_background_position', 'center center'));
			$layout_bar_background_attach = esc_attr(get_option('mypreview_geo_top_bar_layout_bar_background_attach', 'scroll'));
			$layout_message_alignment = esc_attr(get_option('mypreview_geo_top_bar_layout_message_alignment', 'center'));
			$layout_bar_flag_size = absint(get_option('mypreview_geo_top_bar_layout_flag_size', 24));
			$layout_bar_top_spacing = absint(get_option('mypreview_geo_top_bar_layout_bar_top_spacing', 16));
			$layout_bar_bottom_spacing = absint(get_option('mypreview_geo_top_bar_layout_bar_bottom_spacing', 16));
			$layout_bar_divider_thickness = absint(get_option('mypreview_geo_top_bar_layout_bar_divider_thickness', 1));
			$layout_button_border_radius = absint(get_option('mypreview_geo_top_bar_layout_button_border_radius', 4));
			$layout_button_border_thickness = absint(get_option('mypreview_geo_top_bar_layout_button_border_thickness', 1));
			// Typography
			$typography_font_family = esc_attr(get_option('mypreview_geo_top_bar_typography_font_family', 'inherit'));
			$typography_message_font_size = absint(get_option('mypreview_geo_top_bar_typography_message_font_size', 14));
			$typography_message_font_weight = esc_attr(get_option('mypreview_geo_top_bar_typography_message_font_weight', 'bold'));
			$typography_message_font_style = esc_attr(get_option('mypreview_geo_top_bar_typography_message_font_style', 'italic'));
			$typography_message_text_transform = esc_attr(get_option('mypreview_geo_top_bar_typography_message_text_transform', 'uppercase'));
			$typography_button_font_size = absint(get_option('mypreview_geo_top_bar_typography_button_font_size', 14));
			$typography_button_font_weight = esc_attr(get_option('mypreview_geo_top_bar_typography_button_font_weight', 'bolder'));
			$typography_button_font_style = esc_attr(get_option('mypreview_geo_top_bar_typography_button_font_style', 'normal'));
			$typography_button_text_transform = esc_attr(get_option('mypreview_geo_top_bar_typography_button_text_transform', 'uppercase'));
			// Color Scheme
			$color_scheme_bar_background = esc_attr(get_option('mypreview_geo_top_bar_color_scheme_bar_background', '#F5F5F5'));
			$color_scheme_bar_divider = esc_attr(get_option('mypreview_geo_top_bar_color_scheme_bar_divider', '#EFEFEF'));
			$color_scheme_message_text = esc_attr(get_option('mypreview_geo_top_bar_color_scheme_message_text', '#606060'));
			$color_scheme_button_text = esc_attr(get_option('mypreview_geo_top_bar_color_scheme_button_text', '#FFFFFF'));
			$color_scheme_button_text_hover = esc_attr(get_option('mypreview_geo_top_bar_color_scheme_button_text_hover', '#FFFFFF'));
			$color_scheme_button_background = esc_attr(get_option('mypreview_geo_top_bar_color_scheme_button_background', '#77CDE3'));
			$color_scheme_button_background_hover = esc_attr(get_option('mypreview_geo_top_bar_color_scheme_button_background_hover', '#51BFDB'));
			$color_scheme_button_border = esc_attr(get_option('mypreview_geo_top_bar_color_scheme_button_border', '#77CDE3'));
			$color_scheme_button_border_hover = esc_attr(get_option('mypreview_geo_top_bar_color_scheme_button_border_hover', '#51BFDB'));
			// Responsiveness
			$large_devices_max_width = absint(get_option('mypreview_geo_top_bar_responsiveness_large_devices_max_width', 1170));
			$large_devices_horizontal_spacing = absint(get_option('mypreview_geo_top_bar_responsiveness_large_devices_horizontal_spacing', 30));
			$medium_devices_max_width = absint(get_option('mypreview_geo_top_bar_responsiveness_medium_devices_max_width', 970));
			$medium_devices_horizontal_spacing = absint(get_option('mypreview_geo_top_bar_responsiveness_medium_devices_horizontal_spacing', 30));
			$small_devices_max_width = absint(get_option('mypreview_geo_top_bar_responsiveness_small_devices_max_width', 750));
			$small_devices_horizontal_spacing = absint(get_option('mypreview_geo_top_bar_responsiveness_small_devices_horizontal_spacing', 30));
			$extra_small_devices_max_width = absint(get_option('mypreview_geo_top_bar_responsiveness_extra_small_devices_max_width', 450));
			$extra_small_devices_horizontal_spacing = absint(get_option('mypreview_geo_top_bar_responsiveness_extra_small_devices_horizontal_spacing', 15));
			$inline_style = "
                #geo-top-bar-wrapper {
					background-color: {$color_scheme_bar_background};
					border-bottom: {$layout_bar_divider_thickness}px solid {$color_scheme_bar_divider};
					padding-top: {$layout_bar_top_spacing}px;
					padding-bottom: {$layout_bar_bottom_spacing}px;
					background-image: url('{$layout_bar_background_image_url}');
					background-repeat: {$layout_bar_background_repeat};
					background-size: {$layout_bar_background_size};
					background-position: {$layout_bar_background_position};
					background-attachment: {$layout_bar_background_attach};
                } 
                #geo-top-bar-wrapper .geo-top-bar-content {
                	text-align: {$layout_message_alignment};
                }
                #geo-top-bar-wrapper .geo-top-bar-message,
				#geo-top-bar-modal label[for='geo_top_bar_default_country']	{
					color: {$color_scheme_message_text};
					font-size: {$typography_message_font_size}px;
					font-family: {$typography_font_family};
					font-weight: {$typography_message_font_weight};
					font-style: {$typography_message_font_style};
					text-transform: {$typography_message_text_transform};
                }
                #geo-top-bar-wrapper span.flag-icon {
                	width: {$layout_bar_flag_size}px;
					line-height: {$layout_bar_flag_size}px;
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
                #geo-top-bar-modal input[name='geo_top_bar_default_country_submit']:hover {
					color: {$color_scheme_button_text_hover};
					border-color: {$color_scheme_button_border_hover};
					background-color: {$color_scheme_button_background_hover};
                }
                #geo-top-bar-modal {
                	background-color: {$color_scheme_bar_background};
                }
                /* Media Queries */
				@media (min-width: 1200px) {
				    #geo-top-bar-wrapper .geo-top-bar-content {
				    	max-width: {$large_devices_max_width}px;
				    	padding-left: {$large_devices_horizontal_spacing}px;
				    	padding-right: {$large_devices_horizontal_spacing}px;
				    }
				}
				@media (max-width: 1200px) and (min-width: 768px) {
				    #geo-top-bar-wrapper .geo-top-bar-content {
				    	max-width: {$medium_devices_max_width}px;
				    	padding-left: {$medium_devices_horizontal_spacing}px;
				    	padding-right: {$medium_devices_horizontal_spacing}px;
				    }
				}
				@media (max-width: 768px) and (min-width: 480px) {
				    #geo-top-bar-wrapper .geo-top-bar-content {
				    	max-width: {$small_devices_max_width}px;
				    	padding-left: {$small_devices_horizontal_spacing}px;
				    	padding-right: {$small_devices_horizontal_spacing}px;
				    }
				}
				@media (max-width: 480px) {
				    #geo-top-bar-wrapper .geo-top-bar-content {
				    	max-width: {$extra_small_devices_max_width}px;
				    	padding-left: {$extra_small_devices_horizontal_spacing}px;
				    	padding-right: {$extra_small_devices_horizontal_spacing}px;
				    }
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
			$message_bars = json_decode(get_option('mypreview_geo_top_bar_message_bars_repeater'));
			if (is_array($message_bars) || is_object($message_bars)):
				foreach($message_bars as $message_bar):
					$message = (!empty($message_bar->message) ? wp_kses_post($message_bar->message) : '');
					$current_status = esc_attr(strtolower(trim($message_bar->enable)));
					// Exclude countries with "OFF" status from the array.
					if ($current_status !== 'off' && !empty($message)):
						$all_defined_countries[] = esc_html(trim($message_bar->country));
					endif;
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
			$responsiveness_large_devices = esc_attr(get_option('mypreview_geo_top_bar_responsiveness_large_devices', ''));
			$responsiveness_medium_devices = esc_attr(get_option('mypreview_geo_top_bar_responsiveness_medium_devices', ''));
			$responsiveness_small_devices = esc_attr(get_option('mypreview_geo_top_bar_responsiveness_small_devices', ''));
			$responsiveness_extra_small_devices = esc_attr(get_option('mypreview_geo_top_bar_responsiveness_extra_small_devices', ''));
			// ≥1200px
			if(isset($responsiveness_large_devices) && !empty($responsiveness_large_devices)):
				$visibility_classes[] = $responsiveness_large_devices;
			endif;
			// <1200px AND >768px
			if(isset($responsiveness_medium_devices) && !empty($responsiveness_medium_devices)):
				$visibility_classes[] = $responsiveness_medium_devices;
			endif;
			// ≤768px AND >480px
			if(isset($responsiveness_small_devices) && !empty($responsiveness_small_devices)):
				$visibility_classes[] = $responsiveness_small_devices;
			endif;
			// ≤480px
			if(isset($responsiveness_extra_small_devices) && !empty($responsiveness_extra_small_devices)):
				$visibility_classes[] = $responsiveness_extra_small_devices;
			endif;
			// Check if we need to send any visibility classes
			array_filter($visibility_classes);
			if(!empty($visibility_classes) && is_array($visibility_classes) && count($visibility_classes) > 0):
				$visibility_classes = json_encode($visibility_classes);
			else:
				$visibility_classes = '';
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
			$is_test_mode_enabled = esc_attr(get_option('mypreview_geo_top_bar_test_mode_toggle', false));
			// Bail out, If GEO top bar is in test mode
			if (current_user_can('edit_theme_options') && $is_test_mode_enabled):
				return;
			endif;
			?>
			<div id="geo-top-bar-modal" style="display:none;">
				<form name="geo-top-bar-select-default-country-form" id="geo-top-bar-select-default-country-form" method="POST" onkeypress="return event.keyCode != 13;">
					<div class="geo-top-bar-fields">
						<label for="geo_top_bar_default_country"><?php esc_html_e('Where are you from?', 'mypreview-geo-top-bar'); ?></label>
						<div class="geo-top-bar-input-fields">
							<input type="text" name="geo_top_bar_default_country" id="geo_top_bar_default_country" required="required" />
							<input type="hidden" name="geo_top_bar_default_country_code" id="geo_top_bar_default_country_code" />
							<input type="submit" name="geo_top_bar_default_country_submit" value="<?php esc_html_e('Go', 'mypreview-geo-top-bar'); ?>" />
						</div><!-- /geo-top-bar-input-fields -->
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
				$default_country = esc_html($_POST['geo_top_bar_default_country']);
				$default_country_code = esc_html($_POST['geo_top_bar_default_country_code']);
				$this->set_cookie($this->get_cookie_country_name() , $default_country, time() + 86400);
				$this->set_cookie($this->get_cookie_country_code() , $default_country_code, time() + 86400);
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
			return esc_attr('geo_top_bar_default_country');
		}
		/**
		 * Country code (ISO2) cookie handle
		 *
		 * @since 1.0
		 */
		private function get_cookie_country_code()

		{
			return esc_attr('geo_top_bar_default_country_code');
		}
		/**
		 * Country (preview) name cookie handle
		 *
		 * @since 1.0
		 */
		private function get_cookie_preview_country_name()

		{
			return esc_attr('geo_top_bar_preview_country');
		}
		/**
		 * Country (preview) code (ISO2) cookie handle
		 *
		 * @since 1.0
		 */
		private function get_cookie_preview_country_code()

		{
			return esc_attr('geo_top_bar_preview_country_code');
		}
		/**
		 * Get cookie value
		 *
		 * @since 1.0
		 */
		private function get_cookie($cookie_name)

		{
			return esc_attr($_COOKIE[$cookie_name]);
		}
		/**
		 * Set cookie value
		 *
		 * @since 1.0
		 */
		private function set_cookie($cookie_name, $cookie_value, $cookie_life)

		{
			setcookie($cookie_name, $cookie_value, $cookie_life, COOKIEPATH, COOKIE_DOMAIN, false);
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
		/**
		 * Alert user for any error during the import process
		 *
		 * @since 1.0
		 */
		public function controls_print_scripts()

		{
			global $mypreview_geo_top_bar_error;
			if ($mypreview_geo_top_bar_error):
				echo '<script> alert("' . $mypreview_geo_top_bar_error . '"); </script>';
			endif;
		}
		/**
		 * Initialize Import & Export Process.
		 *
		 * @since 1.0
		 */
		public function init_portability($wp_customize)

		{
			// Skip portability initialization if user doesn't have enough permission to manage customizer
			if (!current_user_can('edit_theme_options')):
				return;
			endif;
			// Call the export method to extract and download the settings
			if (isset($_REQUEST['mypreview_geo_top_bar_export_security'])):
				$this->export($wp_customize);
			endif;
			// Call the import method to import and upload the settings
			if (isset($_REQUEST['mypreview_geo_top_bar_import_security']) && isset($_FILES['mypreview-geo-top-bar-import-file'])):
				$this->import($wp_customize);
			endif;
		}
		/**
		 * Export plugin customizer settings.
		 *
		 * @since 1.0
		 */
		private function export($wp_customize)

		{
			// Security check
			if (!wp_verify_nonce($_REQUEST['mypreview_geo_top_bar_export_security'], 'mypreview_geo_top_bar_export_nonce')):
				return;
			endif;
			$plugin = apply_filters('mypreview_geo_top_bar_export_filename', 'mypreview-geo-top-bar');
			$charset = get_option('blog_charset');
			$data = array(
				'options' => array()
			);
			$option_keys = apply_filters('mypreview_geo_top_bar_option_keys', array(
				'mypreview_geo_top_bar_layout_bar_background_image_url' => '',
				'mypreview_geo_top_bar_layout_bar_background_image_id' => '',
				'mypreview_geo_top_bar_layout_bar_background_repeat' => apply_filters('mypreview_geo_top_bar_layout_bar_background_repeat_default', 'repeat') ,
				'mypreview_geo_top_bar_layout_bar_background_size' => apply_filters('mypreview_geo_top_bar_layout_bar_background_size_default', 'auto') ,
				'mypreview_geo_top_bar_layout_bar_background_position' => apply_filters('mypreview_geo_top_bar_layout_bar_background_position_default', 'center center') ,
				'mypreview_geo_top_bar_layout_bar_background_attach' => apply_filters('mypreview_geo_top_bar_layout_bar_background_attach_default', 'scroll') ,
				'mypreview_geo_top_bar_layout_hide_on_scroll' => apply_filters('mypreview_geo_top_bar_layout_hide_on_scroll_default', '') ,
				'mypreview_geo_top_bar_layout_message_alignment' => apply_filters('mypreview_geo_top_bar_layout_message_alignment_default', 'center') ,
				'mypreview_geo_top_bar_layout_button_float' => apply_filters('mypreview_geo_top_bar_layout_button_float_default', 'right') ,
				'mypreview_geo_top_bar_layout_flag_position' => apply_filters('mypreview_geo_top_bar_layout_flag_position_default', 'before') ,
				'mypreview_geo_top_bar_layout_flag_size' => apply_filters('mypreview_geo_top_bar_layout_flag_size_default', 24) ,
				'mypreview_geo_top_bar_layout_bar_top_spacing' => apply_filters('mypreview_geo_top_bar_layout_bar_top_spacing_default', 16) ,
				'mypreview_geo_top_bar_layout_bar_bottom_spacing' => apply_filters('mypreview_geo_top_bar_layout_bar_bottom_spacing_default', 16) ,
				'mypreview_geo_top_bar_layout_bar_divider_thickness' => apply_filters('mypreview_geo_top_bar_layout_bar_divider_thickness_default', 1) ,
				'mypreview_geo_top_bar_layout_slide_down_speed' => apply_filters('mypreview_geo_top_bar_layout_slide_down_speed_default', 1000) ,
				'mypreview_geo_top_bar_layout_button_border_radius' => apply_filters('mypreview_geo_top_bar_layout_button_border_radius_default', 4) ,
				'mypreview_geo_top_bar_layout_button_border_thickness' => apply_filters('mypreview_geo_top_bar_layout_button_border_thickness_default', 1) ,
				'mypreview_geo_top_bar_typography_font_family' => apply_filters('mypreview_geo_top_bar_typography_font_family_default', 'inherit') ,
				'mypreview_geo_top_bar_typography_message_font_size' => apply_filters('mypreview_geo_top_bar_typography_message_font_size_default', 14) ,
				'mypreview_geo_top_bar_typography_message_font_weight' => apply_filters('mypreview_geo_top_bar_typography_message_font_weight_default', 'bold') ,
				'mypreview_geo_top_bar_typography_message_font_style' => apply_filters('mypreview_geo_top_bar_typography_message_font_style_default', 'italic') ,
				'mypreview_geo_top_bar_typography_message_text_transform' => apply_filters('mypreview_geo_top_bar_typography_message_text_transform_default', 'uppercase') ,
				'mypreview_geo_top_bar_typography_button_font_size' => apply_filters('mypreview_geo_top_bar_typography_button_font_default', 14) ,
				'mypreview_geo_top_bar_typography_button_font_weight' => apply_filters('mypreview_geo_top_bar_typography_button_font_weight_default', 'bolder') ,
				'mypreview_geo_top_bar_typography_button_font_style' => apply_filters('mypreview_geo_top_bar_typography_button_font_style_default', 'normal') ,
				'mypreview_geo_top_bar_typography_button_text_transform' => apply_filters('mypreview_geo_top_bar_typography_button_text_transform_default', 'uppercase') ,
				'mypreview_geo_top_bar_color_scheme_bar_background' => apply_filters('mypreview_geo_top_bar_color_scheme_bar_background_default', '#F5F5F5') ,
				'mypreview_geo_top_bar_color_scheme_bar_divider' => apply_filters('mypreview_geo_top_bar_color_scheme_bar_divider_default', '#EFEFEF') ,
				'mypreview_geo_top_bar_color_scheme_message_text' => apply_filters('mypreview_geo_top_bar_color_scheme_message_text_default', '#606060') ,
				'mypreview_geo_top_bar_color_scheme_button_text' => apply_filters('mypreview_geo_top_bar_color_scheme_button_text_default', '#FFFFFF') ,
				'mypreview_geo_top_bar_color_scheme_button_text_hover' => apply_filters('mypreview_geo_top_bar_color_scheme_button_text_hover_default', '#FFFFFF') ,
				'mypreview_geo_top_bar_color_scheme_button_background' => apply_filters('mypreview_geo_top_bar_color_scheme_button_background_default', '#77CDE3') ,
				'mypreview_geo_top_bar_color_scheme_button_background_hover' => apply_filters('mypreview_geo_top_bar_color_scheme_button_background_hover_default', '#51BFDB') ,
				'mypreview_geo_top_bar_color_scheme_button_border' => apply_filters('mypreview_geo_top_bar_color_scheme_button_border_default', '#77CDE3') ,
				'mypreview_geo_top_bar_color_scheme_button_border_hover' => apply_filters('mypreview_geo_top_bar_color_scheme_button_border_hover_default', '#51BFDB') ,
				'mypreview_geo_top_bar_responsiveness_large_devices' => apply_filters('mypreview_geo_top_bar_responsiveness_large_devices_default', '') ,
				'mypreview_geo_top_bar_responsiveness_large_devices_max_width' => apply_filters('mypreview_geo_top_bar_responsiveness_large_devices_max_width_default', 1170) ,
				'mypreview_geo_top_bar_responsiveness_large_devices_horizontal_spacing' => apply_filters('mypreview_geo_top_bar_responsiveness_large_devices_horizontal_spacing_default', 30) ,
				'mypreview_geo_top_bar_responsiveness_medium_devices' => apply_filters('mypreview_geo_top_bar_responsiveness_medium_devices_default', '') ,
				'mypreview_geo_top_bar_responsiveness_medium_devices_max_width' => apply_filters('mypreview_geo_top_bar_responsiveness_medium_devices_max_width_default', 970) ,
				'mypreview_geo_top_bar_responsiveness_medium_devices_horizontal_spacing' => apply_filters('mypreview_geo_top_bar_responsiveness_medium_devices_horizontal_spacing_default', 30) ,
				'mypreview_geo_top_bar_responsiveness_small_devices' => apply_filters('mypreview_geo_top_bar_responsiveness_small_devices_default', '') ,
				'mypreview_geo_top_bar_responsiveness_small_devices_max_width' => apply_filters('mypreview_geo_top_bar_responsiveness_small_devices_max_width_default', 750) ,
				'mypreview_geo_top_bar_responsiveness_small_devices_horizontal_spacing' => apply_filters('mypreview_geo_top_bar_responsiveness_small_devices_horizontal_spacing_default', 30) ,
				'mypreview_geo_top_bar_responsiveness_extra_small_devices' => apply_filters('mypreview_geo_top_bar_responsiveness_extra_small_devices_default', '') ,
				'mypreview_geo_top_bar_responsiveness_extra_small_devices_max_width' => apply_filters('mypreview_geo_top_bar_responsiveness_extra_small_devices_max_width_default', 450) ,
				'mypreview_geo_top_bar_responsiveness_extra_small_devices_horizontal_spacing' => apply_filters('mypreview_geo_top_bar_responsiveness_extra_small_devices_horizontal_spacing_default', 15) ,
				'mypreview_geo_top_bar_message_bars_repeater' => '',
				'mypreview_geo_top_bar_test_mode_toggle' => apply_filters('mypreview_geo_top_bar_test_mode_toggle_default', false) ,
				'mypreview_geo_top_bar_test_mode_message' => apply_filters('mypreview_geo_top_bar_test_mode_message_default', '') ,
				'mypreview_geo_top_bar_test_mode_btn_text' => apply_filters('mypreview_geo_top_bar_test_mode_btn_text_default', '') ,
				'mypreview_geo_top_bar_test_mode_btn_url' => apply_filters('mypreview_geo_top_bar_test_mode_btn_url_default', '')
			));
			foreach($option_keys as $option_key => $option_default):
				$option_value = get_option($option_key, $option_default);
				if ($option_value):
					$data['options'][$option_key] = $option_value;
				endif;
			endforeach;
			ksort($data['options']);
			ksort($data);
			if (function_exists('json_encode')):
				// Set the download headers
				header('Content-disposition: attachment; filename=' . $plugin . '.json');
				header('Content-Type: application/json; charset=' . $charset);
				// Pretty print only available in newer php versions
				if (version_compare(PHP_VERSION, '5.4.0') >= 0):
					echo json_encode($data, JSON_PRETTY_PRINT);
				else:
					echo json_encode($data);
				endif;
			endif;
			// Start the download.
			die();
		}
		/**
		 * Import plugin customizer settings.
		 *
		 * @since 1.0
		 */
		private function import($wp_customize)

		{
			// Bail out, If nonce check fails.
			if (!wp_verify_nonce($_REQUEST['mypreview_geo_top_bar_import_security'], 'mypreview_geo_top_bar_import_nonce')):
				return;
			endif;
			// Make sure WordPress upload support is loaded.
			if (!function_exists('wp_handle_upload')):
				require_once (ABSPATH . 'wp-admin/includes/file.php');
			endif;
			// Load the import & export option class.
			require_once ($this->dir . '/includes/class-mypreview-geo-top-bar-customizer-load-import-export.php');
			// Setup global variables.
			global $wp_customize;
			global $mypreview_geo_top_bar_error;
			// Setup internal variables.
			$mypreview_geo_top_bar_error = false;
			$overrides = array(
				'test_form' => false,
				'test_type' => false
			);
			if (function_exists('json_decode')):
				$overrides['mimes']['json'] = 'application/json';
			endif;
			$file = wp_handle_upload($_FILES['mypreview-geo-top-bar-import-file'], $overrides);
			// Bail out, If we haven't any uploaded file.
			if (isset($file['error'])):
				$mypreview_geo_top_bar_error = $file['error'];
				return;
			endif;
			if (!file_exists($file['file'])):
				$mypreview_geo_top_bar_error = esc_html__('Error importing settings! Please try again.', 'mypreview-geo-top-bar');
				return;
			endif;
			// Get the upload data
			$raw = file_get_contents($file['file']);
			$data = function_exists('json_decode') ? @json_decode($raw, true) : null;
			// Remove the uploaded file
			unlink($file['file']);
			// Bail out, If data check fails
			if ('array' != gettype($data)):
				$mypreview_geo_top_bar_error = esc_html__('This file cannot be imported! You should be uploading a valid export JSON file.', 'mypreview-geo-top-bar');
				return;
			endif;
			// Import options
			if (isset($data['options'])):
				foreach($data['options'] as $option_key => $option_value):
					$option = new MyPreview_GEO_Top_Bar_Customizer_Load_Import_Export($wp_customize, $option_key, array(
						'default' => apply_filters('mypreview_geo_top_bar_portability_load_import_export_default', '') ,
						'type' => 'option',
						'capability' => 'edit_theme_options'
					));
					$option->import($option_value);
				endforeach;
			endif;
			// Call the customize_save action
			do_action('customize_save', $wp_customize);
			// Loop through the options
			foreach($data as $key => $val):
				// Call the "customize_save_" dynamic action.
				do_action('customize_save_' . $key, $wp_customize);
				// Save the option.
				update_option($key, $val);
			endforeach;
			// Call the "customize_save_after" to finish the importing process.
			do_action('customize_save_after', $wp_customize);
		}
		/**
		 * Set cookies to preview the country message bar
		 *
		 * @since 1.0
		 */
		public function ajax_preview_country()

		{
			// Bail out, If we are not in live preview.
			if (!$this->wp_customize->is_preview()):
				wp_send_json_error('not_preview');
			endif;
			// Bail out, If nonce check fails.
			if (!check_ajax_referer('mypreview_geo_top_bar_preview_country_nonce', 'security', false)):
				wp_send_json_error('invalid_nonce');
			endif;
			// Get method name to process delete_option method.
			if (isset($_REQUEST['country_name'], $_REQUEST['country_code']) && !empty($_REQUEST['country_name'])  && !empty($_REQUEST['country_code'])):
				$preview_country = esc_html($_REQUEST['country_name']);
				$preview_country_code = esc_html($_REQUEST['country_code']);
				$this->set_cookie($this->get_cookie_preview_country_name() , $preview_country, time() + 86400);
				$this->set_cookie($this->get_cookie_preview_country_code() , $preview_country_code, time() + 86400);
			endif;
			wp_send_json_success();
			wp_die();
		}
		/**
		 * Reset plugin options.
		 *
		 * @since 1.0
		 */
		public function ajax_reset_plugin_settings()

		{
			// Bail out, If we are not in live preview.
			if (!$this->wp_customize->is_preview()):
				wp_send_json_error('not_preview');
			endif;
			// Bail out, If nonce check fails.
			if (!check_ajax_referer('mypreview_geo_top_bar_reset_nonce', 'security', false)):
				wp_send_json_error('invalid_nonce');
			endif;
			// Get method name to process delete_option method(s).
			if (isset($_REQUEST['reset_method']) && !empty($_REQUEST['reset_method'])):
				$rest_method = esc_html($_REQUEST['reset_method']);
				$this->delete_plugin_settings($rest_method);
			endif;
			wp_send_json_success();
			wp_die();
		}
		/**
		 * Conditionally delete plugin options, according to requested method name.
		 *
		 * @since 1.0
		 */
		private function delete_plugin_settings($rest_method)
		
		{
			switch ($rest_method):
				// Delete all settings in "Layout" section.
				case 'reset_layout': {
					delete_option('mypreview_geo_top_bar_layout_bar_background_image_url');
					delete_option('mypreview_geo_top_bar_layout_bar_background_image_id');
					delete_option('mypreview_geo_top_bar_layout_bar_background_repeat');
					delete_option('mypreview_geo_top_bar_layout_bar_background_size');
					delete_option('mypreview_geo_top_bar_layout_bar_background_position');
					delete_option('mypreview_geo_top_bar_layout_bar_background_attach');
					delete_option('mypreview_geo_top_bar_layout_hide_on_scroll');
					delete_option('mypreview_geo_top_bar_layout_message_alignment');
					delete_option('mypreview_geo_top_bar_layout_button_float');
					delete_option('mypreview_geo_top_bar_layout_flag_position');
					delete_option('mypreview_geo_top_bar_layout_flag_size');
					delete_option('mypreview_geo_top_bar_layout_bar_top_spacing');
					delete_option('mypreview_geo_top_bar_layout_bar_bottom_spacing');
					delete_option('mypreview_geo_top_bar_layout_bar_divider_thickness');
					delete_option('mypreview_geo_top_bar_layout_slide_down_speed');
					delete_option('mypreview_geo_top_bar_layout_button_border_radius');
					delete_option('mypreview_geo_top_bar_layout_button_border_thickness');
				}
					break;
				// Delete all settings in "Typography" section.
				case 'reset_typography': {
					delete_option('mypreview_geo_top_bar_typography_font_family');
					delete_option('mypreview_geo_top_bar_typography_message_font_size');
					delete_option('mypreview_geo_top_bar_typography_message_font_weight');
					delete_option('mypreview_geo_top_bar_typography_message_font_style');
					delete_option('mypreview_geo_top_bar_typography_message_text_transform');
					delete_option('mypreview_geo_top_bar_typography_button_font_size');
					delete_option('mypreview_geo_top_bar_typography_button_font_weight');
					delete_option('mypreview_geo_top_bar_typography_button_font_style');
					delete_option('mypreview_geo_top_bar_typography_button_text_transform');
				}
					break;
				// Delete all settings in "Color Scheme" section.
				case 'reset_color_scheme': {
					delete_option('mypreview_geo_top_bar_color_scheme_bar_background');
					delete_option('mypreview_geo_top_bar_color_scheme_bar_divider');
					delete_option('mypreview_geo_top_bar_color_scheme_message_text');
					delete_option('mypreview_geo_top_bar_color_scheme_button_text');
					delete_option('mypreview_geo_top_bar_color_scheme_button_text_hover');
					delete_option('mypreview_geo_top_bar_color_scheme_button_background');
					delete_option('mypreview_geo_top_bar_color_scheme_button_background_hover');
					delete_option('mypreview_geo_top_bar_color_scheme_button_border');
					delete_option('mypreview_geo_top_bar_color_scheme_button_border_hover');
				}
					break;
				// Delete all settings in "Responsiveness" section.
				case 'reset_responsiveness': {
					delete_option('mypreview_geo_top_bar_responsiveness_large_devices');
					delete_option('mypreview_geo_top_bar_responsiveness_large_devices_max_width');
					delete_option('mypreview_geo_top_bar_responsiveness_large_devices_horizontal_spacing');
					delete_option('mypreview_geo_top_bar_responsiveness_medium_devices');
					delete_option('mypreview_geo_top_bar_responsiveness_medium_devices_max_width');
					delete_option('mypreview_geo_top_bar_responsiveness_medium_devices_horizontal_spacing');
					delete_option('mypreview_geo_top_bar_responsiveness_small_devices');
					delete_option('mypreview_geo_top_bar_responsiveness_small_devices_max_width');
					delete_option('mypreview_geo_top_bar_responsiveness_small_devices_horizontal_spacing');
					delete_option('mypreview_geo_top_bar_responsiveness_extra_small_devices');
					delete_option('mypreview_geo_top_bar_responsiveness_extra_small_devices_max_width');
					delete_option('mypreview_geo_top_bar_responsiveness_extra_small_devices_horizontal_spacing');
				}
					break;
				// Delete all settings in "Message Bars" section.
				case 'reset_message_bars':
					delete_option('mypreview_geo_top_bar_message_bars_repeater');
					break;
				// Delete all settings in "Test Mode" section.
				case 'reset_test_mode': {
					delete_option('mypreview_geo_top_bar_test_mode_toggle');
					delete_option('mypreview_geo_top_bar_test_mode_message');
					delete_option('mypreview_geo_top_bar_test_mode_btn_text');
					delete_option('mypreview_geo_top_bar_test_mode_btn_url');
				}
					break;

				default:
			endswitch;
		}
		/**
		 * Create the customizer URL.
		 *
		 * @since 1.0
		 */
		private function customizer_url($autofocus, $autofocus_key, $return_url = null)

		{
			$url = '';
			// Getting customizer URL
			$url = esc_url(wp_customize_url());
			$url = add_query_arg('autofocus[' . $autofocus . ']', $autofocus_key, $url);
			if(null !== $return_url):
				$url = add_query_arg('return', urlencode(admin_url() . $return_url) , $url);
			endif;
			return $url;
		}
		/**
		 * Display plugin settings, docs and support links in plugins table page.
		 *
		 * @since 1.0
		 */
		public function settings_link($links)

		{
			$url = $this->customizer_url('panel', 'mypreview_geo_top_bar_pnl', 'plugins.php');
			$plugin_links = array();
			$plugin_links[] = sprintf(__('<a href="%s" target="_blank">Support</a>', 'mypreview-geo-top-bar') , esc_url('https://codecanyon.net/user/mypreview#contact'));
			$plugin_links[] = sprintf(__('<a href="%s" target="_blank">Docs</a>', 'mypreview-geo-top-bar') , esc_url('https://mahdiyazdani.github.io/geo-top-bar'));
			$plugin_links[] = sprintf(__('<a href="%s" target="_self">Settings</a>', 'mypreview-geo-top-bar') , esc_url($url));
			return array_merge($plugin_links, $links);
		}
		/**
		 * Run once plugin activated.
		 *
		 * @since 1.0
		 */
		public function activation()

		{
			// Check if notice value stored in database or not?
			// If nothing found, register notice value into database and display plugin activation notice
			if (true != get_option('mypreview_geo_top_bar_notice_once')):
				$url = $this->customizer_url('panel', 'mypreview_geo_top_bar_pnl', 'plugins.php');
				$message = sprintf(__('Thanks for installing the GEO Top Bar plugin. To get started, visit the %1$sCustomizer%2$s.', 'mypreview-geo-top-bar') , '<a href="' . $url . '" target="_self">', '</a>');
				printf('<div class="notice notice-info is-dismissible"><p>%s</p></div>', $message);
				add_option('mypreview_geo_top_bar_notice_once', true);
			endif;
		}
		/**
		 * Run once plugin deactivated
		 *
		 * @since 1.0
		 */
		public function deactivation()

		{
			// Delete plugin activation notice value from database
			if (false == delete_option('mypreview_geo_top_bar_notice_once')):
				$message = esc_html__('There was a problem deactivating the GEO Top Bar plugin. Please try again.', 'mypreview-geo-top-bar');
				printf('<div class="notice notice-error is-dismissible"><p>%s</p></div>', $message);
			endif;
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
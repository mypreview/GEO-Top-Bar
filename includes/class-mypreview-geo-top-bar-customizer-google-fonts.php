<?php
/**
 * A class to create a dropdown for Google Fonts.
 * Inspired by WordPress Theme Customizer Custom Controls by paulund.
 *
 * @link        https://github.com/paulund/wordpress-theme-customizer-custom-controls
 * @author      Mahdi Yazdani
 * @package     GEO Top Bar
 * @since       1.0
 */
// Prevent direct file access
defined('ABSPATH') or exit;
if (class_exists('WP_Customize_Control') && !class_exists('MyPreview_GEO_Top_Bar_Customizer_Google_Fonts')):
    class MyPreview_GEO_Top_Bar_Customizer_Google_Fonts extends WP_Customize_Control

    {
        private $fonts = false;
        
        public function __construct($manager, $id, $args = array() , $options = array())
        {
            $this->fonts = $this->get_fonts();
            parent::__construct($manager, $id, $args);
        }
        /**
         * Render the content of the category dropdown
         *
         * @since 1.0
         */
        public function render_content()

        {
            if (!empty($this->fonts)):
                ?>
                <label>
                    <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
                    <span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
                    <select <?php $this->link(); ?>> 
                        <option value="inherit"><?php esc_html_e('Default', 'mypreview-geo-top-bar'); ?></option>
                        <?php
                            foreach($this->fonts as $key => $value):
                                printf('<option value="%s" %s>%s</option>', $value->family, selected($this->value() , $key, false) , $value->family);
                            endforeach;
                        ?>
                    </select>
                </label>
            <?php
            endif;
        }
        /**
         * Get the Google fonts from the API or in the cache
         *
         * @since 1.0
         */
        public function get_fonts()

        {
            $amount = apply_filters('mypreview_geo_top_bar_google_fonts_limit', 30);
            $fontFile = plugin_dir_path(__FILE__) . 'cache/google-web-fonts.txt';
            $apiKey = 'AIzaSyCPv3MqlmrV-GSaRPA3s-Q0NREVbmQf7wo';
            // Total time the file will be cached in seconds, set to a week
            $cachetime = 86400 * 7;
            if (file_exists($fontFile) && $cachetime < filemtime($fontFile)):
                $content = json_decode(file_get_contents($fontFile));
            else:
                $googleApi = 'https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key=' . $apiKey;
                $fontContent = wp_remote_get($googleApi, array(
                    'sslverify' => false
                ));
                $fp = fopen($fontFile, 'w');
                fwrite($fp, $fontContent['body']);
                fclose($fp);
                $content = json_decode($fontContent['body']);
            endif;
            if ($amount == 'all'):
                return $content->items;
            else:
                return array_slice($content->items, 0, $amount);
            endif;
        }
    }
endif;
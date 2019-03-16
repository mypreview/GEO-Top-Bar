<?php
/**
 *  A class that extends WP_Customize_Setting so we can access
 *  the protected updated method when importing options.
 *
 * @author      Mahdi Yazdani
 * @package     GEO Top Bar
 * @since       1.0
 */
// Prevent direct file access
defined('ABSPATH') or exit;
if (!class_exists('MyPreview_GEO_Top_Bar_Customizer_Load_Import_Export')):
    final class MyPreview_GEO_Top_Bar_Customizer_Load_Import_Export extends WP_Customize_Setting

    {
        /**
         * Import an option value for this setting.
         *
         * @since 1.0
         */
        public function import($value) 

		{
			$this->update($value);	
		}
    }
endif;
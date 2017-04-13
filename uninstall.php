<?php
/**
 * Unistall GEO Top Bar.
 *
 * @author      Mahdi Yazdani
 * @package     GEO Top Bar
 * @since       1.0
 */
if (!defined( 'WP_UNINSTALL_PLUGIN')):
    exit();
endif;

delete_option('mypreview_geo_top_bar_notice_once');
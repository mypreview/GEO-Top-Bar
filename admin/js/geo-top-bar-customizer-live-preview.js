/**
 * GEO Top Bar - Customizer Live Preview.
 *
 * @author      Mahdi Yazdani
 * @package     GEO Top Bar
 * @since       1.0
 */
(function($) {
    $(function() {
    	/**
    	 * Bar Background Repeat - Layout
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_layout_bar_background_repeat',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('background-repeat', to ? to : '' );
            });
        });
        /**
    	 * Bar Background Size - Layout
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_layout_bar_background_size',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('background-size', to ? to : '' );
            });
        });
        /**
    	 * Bar Background Position - Layout
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_layout_bar_background_position',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('background-position', to ? to : '' );
            });
        });
        /**
    	 * Bar Background Attachment - Layout
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_layout_bar_background_attach',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('background-attachment', to ? to : '' );
            });
        });
    	/**
    	 * Message Alignment - Layout
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_layout_message_alignment',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-content').css('text-align', to ? to : '' );
            });
        });
        /**
    	 * Flag Size - Layout
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_layout_flag_size',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper span.flag-icon').css('width', to ? to + 'px' : '' );
                $('#geo-top-bar-wrapper span.flag-icon').css('line-height', to ? to + 'px' : '' );
            });
        });
        /**
    	 * Bar Top Spacing - Layout
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_layout_bar_top_spacing',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('padding-top', to ? to + 'px' : '' );
            });
        });
        /**
    	 * Bar Bottom Spacing - Layout
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_layout_bar_bottom_spacing',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('padding-bottom', to ? to + 'px' : '' );
            });
        });
        /**
    	 * Bar Divider Thickness - Layout
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_layout_bar_divider_thickness',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('border-bottom-width', to ? to + 'px' : '' );
            });
        });
        /**
    	 * Button Border Radius - Layout
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_layout_button_border_radius',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('border-radius', to ? to + 'px' : '' );
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('border-radius', to ? to + 'px' : '' );
            });
        });
        /**
    	 * Button Border Thickness - Layout
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_layout_button_border_thickness',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('border-width', to ? to + 'px' : '' );
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('border-width', to ? to + 'px' : '' );
            });
        });
    	/**
    	 * Font-Size - Typography
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_typography_message_font_size',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-message').css('font-size', to ? to + 'px' : '' );
                $('#geo-top-bar-modal label[for=geo_top_bar_default_country]').css('font-size', to ? to + 'px' : '' );
            });
        });
        /**
    	 * Font-Weight - Typography
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_typography_message_font_weight',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-message').css('font-weight', to ? to : '' );
                $('#geo-top-bar-modal label[for=geo_top_bar_default_country]').css('font-weight', to ? to : '' );
            });
        });
        /**
    	 * Font-Style - Typography
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_typography_message_font_style',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-message').css('font-style', to ? to : '' );
                $('#geo-top-bar-modal label[for=geo_top_bar_default_country]').css('font-style', to ? to : '' );
            });
        });
        /**
    	 * Text-Transform - Typography
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_typography_message_text_transform',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-message').css('text-transform', to ? to : '' );
                $('#geo-top-bar-modal label[for=geo_top_bar_default_country]').css('text-transform', to ? to : '' );
            });
        });
        /**
    	 * Button Font-Size - Typography
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_typography_button_font_size',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('font-size', to ? to + 'px' : '' );
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('font-size', to ? to + 'px' : '' );
            });
        });
        /**
    	 * Button Font-Weight - Typography
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_typography_button_font_weight',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('font-weight', to ? to : '' );
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('font-weight', to ? to : '' );
            });
        });
        /**
    	 * Button Font-Style - Typography
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_typography_button_font_style',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('font-style', to ? to : '' );
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('font-style', to ? to : '' );
            });
        });
        /**
    	 * Button Text-Transform - Typography
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_typography_button_text_transform',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('text-transform', to ? to : '' );
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('text-transform', to ? to : '' );
            });
        });
    	/**
    	 * Bar Background - Color Scheme
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_color_scheme_bar_background',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('background-color', to ? to : '' );
                $('#geo-top-bar-modal').css('background-color', to ? to : '' );
            });
        });
        /**
    	 * Bar Divider - Color Scheme
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_color_scheme_bar_divider',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('border-bottom-color', to ? to : '' );
            });
        });
        /**
    	 * Message Text - Color Scheme
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_color_scheme_message_text',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-message').css('color', to ? to : '' );
                $('#geo-top-bar-modal label[for=geo_top_bar_default_country]').css('color', to ? to : '' );
            });
        });
        /**
    	 * Button Text - Color Scheme
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_color_scheme_button_text',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('color', to ? to : '' );
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('color', to ? to : '' );
            });
        });
        /**
    	 * Button Text Hover - Color Scheme
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_color_scheme_button_text_hover',function( value ) {
            value.bind(function(to) {
                var style, el;
                style = '<style class="geo-top-bar-button-text-hover-inline-style">#geo-top-bar-wrapper .geo-top-bar-button:hover, #geo-top-bar-modal input[name=geo_top_bar_default_country_submit]:hover { color: ' + to + ' !important; }</style>';
                el =  $( 'style.geo-top-bar-button-text-hover-inline-style' );
                // Add the style element into the DOM or replace the matching style element that is already there
                if ( el.length ) {
                    el.replaceWith( style );
                } else {
                    $('head').append( style );
                }
            });
        });
        /**
    	 * Button Background - Color Scheme
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_color_scheme_button_background',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('background-color', to ? to : '' );
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('background-color', to ? to : '' );
            });
        });
        /**
    	 * Button Background Hover - Color Scheme
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_color_scheme_button_background_hover',function( value ) {
            value.bind(function(to) {
                var style, el;
                style = '<style class="geo-top-bar-button-background-hover-inline-style">#geo-top-bar-wrapper .geo-top-bar-button:hover, #geo-top-bar-modal input[name=geo_top_bar_default_country_submit]:hover { background-color: ' + to + ' !important; }</style>';
                el =  $( 'style.geo-top-bar-button-background-hover-inline-style' );
                // Add the style element into the DOM or replace the matching style element that is already there
                if ( el.length ) {
                    el.replaceWith( style );
                } else {
                    $('head').append( style );
                }
            });
        });
        /**
    	 * Button Border - Color Scheme
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_color_scheme_button_border',function( value ) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('border-color', to ? to : '' );
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('border-color', to ? to : '' );
            });
        });
        /**
    	 * Button Border Hover - Color Scheme
    	 * 
    	 * @since 1.0
    	 */
    	wp.customize('mypreview_geo_top_bar_color_scheme_button_border_hover',function( value ) {
            value.bind(function(to) {
                var style, el;
                style = '<style class="geo-top-bar-button-border-hover-inline-style">#geo-top-bar-wrapper .geo-top-bar-button:hover, #geo-top-bar-modal input[name=geo_top_bar_default_country_submit]:hover { border-color: ' + to + ' !important; }</style>';
                el =  $( 'style.geo-top-bar-button-border-hover-inline-style' );
                // Add the style element into the DOM or replace the matching style element that is already there
                if ( el.length ) {
                    el.replaceWith( style );
                } else {
                    $('head').append( style );
                }
            });
        });
    }); // end of document ready
})(jQuery); // end of jQuery name space
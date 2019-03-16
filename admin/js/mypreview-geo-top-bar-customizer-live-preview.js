/**
 * GEO Top Bar - Customizer Live Preview.
 *
 * @author      Mahdi Yazdani
 * @package     GEO Top Bar
 * @since       1.0
 */
(function($) {
    $(function() {
        'use strict';
        /**
         * Bar Background Repeat - Layout
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_layout_bar_background_repeat', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('background-repeat', to ? to : '');
            });
        });
        /**
         * Bar Background Size - Layout
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_layout_bar_background_size', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('background-size', to ? to : '');
            });
        });
        /**
         * Bar Background Position - Layout
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_layout_bar_background_position', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('background-position', to ? to : '');
            });
        });
        /**
         * Bar Background Attachment - Layout
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_layout_bar_background_attach', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('background-attachment', to ? to : '');
            });
        });
        /**
         * Message Alignment - Layout
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_layout_message_alignment', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-content').css('text-align', to ? to : '');
            });
        });
        /**
         * Flag Size - Layout
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_layout_flag_size', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper span.flag-icon').css('width', to ? to + 'px' : '');
                $('#geo-top-bar-wrapper span.flag-icon').css('line-height', to ? to + 'px' : '');
            });
        });
        /**
         * Bar Top Spacing - Layout
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_layout_bar_top_spacing', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('padding-top', to ? to + 'px' : '');
            });
        });
        /**
         * Bar Bottom Spacing - Layout
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_layout_bar_bottom_spacing', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('padding-bottom', to ? to + 'px' : '');
            });
        });
        /**
         * Bar Divider Thickness - Layout
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_layout_bar_divider_thickness', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('border-bottom-width', to ? to + 'px' : '');
            });
        });
        /**
         * Button Border Radius - Layout
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_layout_button_border_radius', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('border-radius', to ? to + 'px' : '');
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('border-radius', to ? to + 'px' : '');
            });
        });
        /**
         * Button Border Thickness - Layout
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_layout_button_border_thickness', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('border-width', to ? to + 'px' : '');
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('border-width', to ? to + 'px' : '');
            });
        });
        /**
         * Font-Size - Typography
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_typography_message_font_size', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-message').css('font-size', to ? to + 'px' : '');
                $('#geo-top-bar-modal label[for=geo_top_bar_default_country]').css('font-size', to ? to + 'px' : '');
            });
        });
        /**
         * Font-Weight - Typography
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_typography_message_font_weight', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-message').css('font-weight', to ? to : '');
                $('#geo-top-bar-modal label[for=geo_top_bar_default_country]').css('font-weight', to ? to : '');
            });
        });
        /**
         * Font-Style - Typography
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_typography_message_font_style', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-message').css('font-style', to ? to : '');
                $('#geo-top-bar-modal label[for=geo_top_bar_default_country]').css('font-style', to ? to : '');
            });
        });
        /**
         * Text-Transform - Typography
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_typography_message_text_transform', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-message').css('text-transform', to ? to : '');
                $('#geo-top-bar-modal label[for=geo_top_bar_default_country]').css('text-transform', to ? to : '');
            });
        });
        /**
         * Button Font-Size - Typography
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_typography_button_font_size', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('font-size', to ? to + 'px' : '');
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('font-size', to ? to + 'px' : '');
            });
        });
        /**
         * Button Font-Weight - Typography
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_typography_button_font_weight', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('font-weight', to ? to : '');
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('font-weight', to ? to : '');
            });
        });
        /**
         * Button Font-Style - Typography
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_typography_button_font_style', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('font-style', to ? to : '');
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('font-style', to ? to : '');
            });
        });
        /**
         * Button Text-Transform - Typography
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_typography_button_text_transform', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('text-transform', to ? to : '');
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('text-transform', to ? to : '');
            });
        });
        /**
         * Bar Background - Color Scheme
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_color_scheme_bar_background', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('background-color', to ? to : '');
                $('#geo-top-bar-modal').css('background-color', to ? to : '');
            });
        });
        /**
         * Bar Divider - Color Scheme
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_color_scheme_bar_divider', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper').css('border-bottom-color', to ? to : '');
            });
        });
        /**
         * Message Text - Color Scheme
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_color_scheme_message_text', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-message').css('color', to ? to : '');
                $('#geo-top-bar-modal label[for=geo_top_bar_default_country]').css('color', to ? to : '');
            });
        });
        /**
         * Button Text - Color Scheme
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_color_scheme_button_text', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('color', to ? to : '');
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('color', to ? to : '');
            });
        });
        /**
         * Button Text Hover - Color Scheme
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_color_scheme_button_text_hover', function(value) {
            value.bind(function(to) {
                var style, el;
                style = '<style class="geo-top-bar-button-text-hover-inline-style">#geo-top-bar-wrapper .geo-top-bar-button:hover, #geo-top-bar-modal input[name=geo_top_bar_default_country_submit]:hover { color: ' + to + ' !important; }</style>';
                el = $('style.geo-top-bar-button-text-hover-inline-style');
                // Add the style element into the DOM or replace the matching style element that is already there
                if (el.length) {
                    el.replaceWith(style);
                } else {
                    $('head').append(style);
                }
            });
        });
        /**
         * Button Background - Color Scheme
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_color_scheme_button_background', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('background-color', to ? to : '');
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('background-color', to ? to : '');
            });
        });
        /**
         * Button Background Hover - Color Scheme
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_color_scheme_button_background_hover', function(value) {
            value.bind(function(to) {
                var style, el;
                style = '<style class="geo-top-bar-button-background-hover-inline-style">#geo-top-bar-wrapper .geo-top-bar-button:hover, #geo-top-bar-modal input[name=geo_top_bar_default_country_submit]:hover { background-color: ' + to + ' !important; }</style>';
                el = $('style.geo-top-bar-button-background-hover-inline-style');
                // Add the style element into the DOM or replace the matching style element that is already there
                if (el.length) {
                    el.replaceWith(style);
                } else {
                    $('head').append(style);
                }
            });
        });
        /**
         * Button Border - Color Scheme
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_color_scheme_button_border', function(value) {
            value.bind(function(to) {
                $('#geo-top-bar-wrapper .geo-top-bar-button').css('border-color', to ? to : '');
                $('#geo-top-bar-modal input[name=geo_top_bar_default_country_submit]').css('border-color', to ? to : '');
            });
        });
        /**
         * Button Border Hover - Color Scheme
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_color_scheme_button_border_hover', function(value) {
            value.bind(function(to) {
                var style, el;
                style = '<style class="geo-top-bar-button-border-hover-inline-style">#geo-top-bar-wrapper .geo-top-bar-button:hover, #geo-top-bar-modal input[name=geo_top_bar_default_country_submit]:hover { border-color: ' + to + ' !important; }</style>';
                el = $('style.geo-top-bar-button-border-hover-inline-style');
                // Add the style element into the DOM or replace the matching style element that is already there
                if (el.length) {
                    el.replaceWith(style);
                } else {
                    $('head').append(style);
                }
            });
        });
        /**
         * Large Devices - Responsiveness
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_responsiveness_large_devices', function(value) {
            value.bind(function(to) {
                if (to !== '') {
                    $('#geo-top-bar-wrapper').addClass(to);
                } else {
                    $('#geo-top-bar-wrapper').removeClass('hide-large-devices');
                }
            });
        });
        /**
         * Max Width Large Devices - Responsiveness
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_responsiveness_large_devices_max_width', function(value) {
            value.bind(function(to) {
                var style, el;
                style = '<style class="geo-top-bar-responsiveness-large-devices-max-width">@media (min-width: 1200px) { #geo-top-bar-wrapper .geo-top-bar-content { max-width: ' + to + 'px; } }</style>';
                el = $('style.geo-top-bar-responsiveness-large-devices-max-width');
                // Add the style element into the DOM or replace the matching style element that is already there
                if (el.length) {
                    el.replaceWith(style);
                } else {
                    $('head').append(style);
                }
            });
        });
        /**
         * Horizontal Spacing Large Devices - Responsiveness
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_responsiveness_large_devices_horizontal_spacing', function(value) {
            value.bind(function(to) {
                var style, el;
                style = '<style class="geo-top-bar-responsiveness-large-devices-horizontal-spacing">@media (min-width: 1200px) { #geo-top-bar-wrapper .geo-top-bar-content { padding-left: ' + to + 'px; padding-right: ' + to + 'px; } }</style>';
                el = $('style.geo-top-bar-responsiveness-large-devices-horizontal-spacing');
                // Add the style element into the DOM or replace the matching style element that is already there
                if (el.length) {
                    el.replaceWith(style);
                } else {
                    $('head').append(style);
                }
            });
        });
        /**
         * Medium Devices - Responsiveness
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_responsiveness_medium_devices', function(value) {
            value.bind(function(to) {
                if (to !== '') {
                    $('#geo-top-bar-wrapper').addClass(to);
                } else {
                    $('#geo-top-bar-wrapper').removeClass('hide-medium-devices');
                }
            });
        });
        /**
         * Max Width Medium Devices - Responsiveness
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_responsiveness_medium_devices_max_width', function(value) {
            value.bind(function(to) {
                var style, el;
                style = '<style class="geo-top-bar-responsiveness-medium-devices-max-width">@media (max-width: 1200px) and (min-width: 768px) { #geo-top-bar-wrapper .geo-top-bar-content { max-width: ' + to + 'px; } }</style>';
                el = $('style.geo-top-bar-responsiveness-medium-devices-max-width');
                // Add the style element into the DOM or replace the matching style element that is already there
                if (el.length) {
                    el.replaceWith(style);
                } else {
                    $('head').append(style);
                }
            });
        });
        /**
         * Horizontal Spacing Medium Devices - Responsiveness
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_responsiveness_medium_devices_horizontal_spacing', function(value) {
            value.bind(function(to) {
                var style, el;
                style = '<style class="geo-top-bar-responsiveness-medium-devices-horizontal-spacing">@media (max-width: 1200px) and (min-width: 768px) { #geo-top-bar-wrapper .geo-top-bar-content { padding-left: ' + to + 'px; padding-right: ' + to + 'px; } }</style>';
                el = $('style.geo-top-bar-responsiveness-medium-devices-horizontal-spacing');
                // Add the style element into the DOM or replace the matching style element that is already there
                if (el.length) {
                    el.replaceWith(style);
                } else {
                    $('head').append(style);
                }
            });
        });
        /**
         * Small Devices - Responsiveness
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_responsiveness_small_devices', function(value) {
            value.bind(function(to) {
                if (to !== '') {
                    $('#geo-top-bar-wrapper').addClass(to);
                } else {
                    $('#geo-top-bar-wrapper').removeClass('hide-small-devices');
                }
            });
        });
        /**
         * Max Width Small Devices - Responsiveness
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_responsiveness_small_devices_max_width', function(value) {
            value.bind(function(to) {
                var style, el;
                style = '<style class="geo-top-bar-responsiveness-small-devices-max-width">@media (max-width: 768px) and (min-width: 480px) { #geo-top-bar-wrapper .geo-top-bar-content { max-width: ' + to + 'px; } }</style>';
                el = $('style.geo-top-bar-responsiveness-small-devices-max-width');
                // Add the style element into the DOM or replace the matching style element that is already there
                if (el.length) {
                    el.replaceWith(style);
                } else {
                    $('head').append(style);
                }
            });
        });
        /**
         * Horizontal Spacing Small Devices - Responsiveness
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_responsiveness_small_devices_horizontal_spacing', function(value) {
            value.bind(function(to) {
                var style, el;
                style = '<style class="geo-top-bar-responsiveness-small-devices-horizontal-spacing">@media (max-width: 768px) and (min-width: 480px) { #geo-top-bar-wrapper .geo-top-bar-content { padding-left: ' + to + 'px; padding-right: ' + to + 'px; } }</style>';
                el = $('style.geo-top-bar-responsiveness-small-devices-horizontal-spacing');
                // Add the style element into the DOM or replace the matching style element that is already there
                if (el.length) {
                    el.replaceWith(style);
                } else {
                    $('head').append(style);
                }
            });
        });
        /**
         * Extra Small Devices - Responsiveness
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_responsiveness_extra_small_devices', function(value) {
            value.bind(function(to) {
                if (to !== '') {
                    $('#geo-top-bar-wrapper').addClass(to);
                } else {
                    $('#geo-top-bar-wrapper').removeClass('hide-extra-small-devices');
                }
            });
        });
        /**
         * Max Width Extra Small Devices - Responsiveness
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_responsiveness_extra_small_devices_max_width', function(value) {
            value.bind(function(to) {
                var style, el;
                style = '<style class="geo-top-bar-responsiveness-extra-small-devices-max-width">@media (max-width: 480px) { #geo-top-bar-wrapper .geo-top-bar-content { max-width: ' + to + 'px; } }</style>';
                el = $('style.geo-top-bar-responsiveness-extra-small-devices-max-width');
                // Add the style element into the DOM or replace the matching style element that is already there
                if (el.length) {
                    el.replaceWith(style);
                } else {
                    $('head').append(style);
                }
            });
        });
        /**
         * Horizontal Spacing Extra Small Devices - Responsiveness
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_responsiveness_extra_small_devices_horizontal_spacing', function(value) {
            value.bind(function(to) {
                var style, el;
                style = '<style class="geo-top-bar-responsiveness-extra-small-devices-horizontal-spacing">@media (max-width: 480px) { #geo-top-bar-wrapper .geo-top-bar-content { padding-left: ' + to + 'px; padding-right: ' + to + 'px; } }</style>';
                el = $('style.geo-top-bar-responsiveness-extra-small-devices-horizontal-spacing');
                // Add the style element into the DOM or replace the matching style element that is already there
                if (el.length) {
                    el.replaceWith(style);
                } else {
                    $('head').append(style);
                }
            });
        });
        /**
         * Message Bar(s) - Message Bars
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_message_bars_repeater', function(value) {
            value.bind(function(to) {
                // Retrieve all parameters passed by "geo-top-bar-customizer-live-preview"
                var current_country_name = mypreview_geo_top_bar_customizer_live_vars.current_country_name.trim().toLowerCase(),
                    current_country_code = mypreview_geo_top_bar_customizer_live_vars.current_country_code.trim().toLowerCase(),
                    slide_down = mypreview_geo_top_bar_customizer_live_vars.slide_down,
                    button_float = mypreview_geo_top_bar_customizer_live_vars.button_float,
                    flag_position = mypreview_geo_top_bar_customizer_live_vars.flag_position,
                    test_mode = mypreview_geo_top_bar_customizer_live_vars.test_mode,
                    visibility_classes = mypreview_geo_top_bar_customizer_live_vars.visibility_classes,
                    current_country_selected = false,
                    flag_html = '',
                    button_html = '',
                    obj = $.parseJSON(to);
                if (typeof visibility_classes !== 'undefined' && visibility_classes.length > 0) {
                    visibility_classes = $.parseJSON(visibility_classes);
                    visibility_classes = visibility_classes.join(' ');
                }
                if (test_mode !== '') {
                    return;
                }
                // Fetch all submitted values
                obj.forEach(function(item) {
                    var country = item.country.trim().toLowerCase(),
                        message = (item.message) ? item.message : '',
                        display_flag = (item.display_flag) ? item.display_flag : '',
                        button_text = (item.button_text !== '') ? item.button_text : '',
                        button_url = (item.button_url !== '') ? item.button_url : '#',
                        button_target = (item.button_target !== '') ? item.button_target : '_self',
                        enable = (item.enable) ? item.enable : 'on';
                    // Current country selected
                    if (country.indexOf(current_country_name) > -1) {
                        // Need to load GEO Top Bar HTML markup
                        if ($('#geo-top-bar-wrapper').length === 0) {
                            wp.customize.preview.send('refresh');
                        }
                        // Current country already selected
                        current_country_selected = true;
                        // If message bar already exists
                        if ($('#geo-top-bar-wrapper').length > 0) {
                            // Update message bar content
                            if ($('#geo-top-bar-wrapper .geo-top-bar-message').length > 0) {
                                if (message !== '') {
                                    if ($('#geo-top-bar-wrapper').css('display') === 'none' && enable !== 'off' && enable !== '') {
                                        $('#geo-top-bar-wrapper').slideDown(slide_down);
                                    }
                                    $('#geo-top-bar-wrapper .geo-top-bar-message span').html(message);
                                } else {
                                    $('#geo-top-bar-wrapper').slideUp(slide_down);
                                }
                            }
                            // Update OR Create flag icon markup
                            if ($('#geo-top-bar-wrapper .geo-top-bar-content span.flag-icon').length > 0) {
                                if (display_flag === 'yes' && display_flag !== '') {
                                    $('#geo-top-bar-wrapper .geo-top-bar-content span.flag-icon').hide();
                                } else {
                                    $('#geo-top-bar-wrapper .geo-top-bar-content span.flag-icon').show();
                                }
                            } else {
                                if (test_mode === '') {
                                    flag_html = $('<a href="#geo-top-bar-modal" rel="modal:open"><span class="flag-icon flag-icon-' + current_country_code + '"></span></a>');
                                } else {
                                    flag_html = $('<span class="flag-icon flag-icon-' + current_country_code + '"></span>');
                                }
                                if (typeof flag_html !== 'undefined' && flag_html !== '' && flag_position === 'after') {
                                    $('#geo-top-bar-wrapper .geo-top-bar-content').append(flag_html);
                                } else if (typeof flag_html != 'undefined' && flag_html !== '' && flag_position === 'before') {
                                    $('#geo-top-bar-wrapper .geo-top-bar-content').prepend(flag_html);
                                }
                                if (display_flag === 'yes' && display_flag !== '') {
                                    $('#geo-top-bar-wrapper .geo-top-bar-content span.flag-icon').hide();
                                } else {
                                    $('#geo-top-bar-wrapper .geo-top-bar-content span.flag-icon').show();
                                }
                            }
                            // Update OR Create button markup
                            if ($('#geo-top-bar-wrapper a.geo-top-bar-button').length > 0) {
                                if (button_text !== '') {
                                    if ($('#geo-top-bar-wrapper a.geo-top-bar-button').css('display') === 'none') {
                                        $('#geo-top-bar-wrapper a.geo-top-bar-button').show();
                                    }
                                    $('#geo-top-bar-wrapper a.geo-top-bar-button').text(button_text);
                                    $('#geo-top-bar-wrapper a.geo-top-bar-button').attr('href', button_url);
                                    $('#geo-top-bar-wrapper a.geo-top-bar-button').attr('target', button_target);
                                    $('#geo-top-bar-wrapper a.geo-top-bar-button').attr('disabled', 'disabled');
                                } else {
                                    $('#geo-top-bar-wrapper a.geo-top-bar-button').hide();
                                }
                            } else {
                                button_html = $('<a href="' + button_url + '" class="geo-top-bar-button" target="' + button_target + '" disabled="disabled">' + button_text + '</a>');
                                if (typeof button_html !== 'undefined' && button_html !== '' && button_text !== '' && button_float === 'right') {
                                    $('#geo-top-bar-wrapper .geo-top-bar-message').append(button_html);
                                } else if (typeof button_html !== 'undefined' && button_html !== '' && button_float === 'left') {
                                    $('#geo-top-bar-wrapper .geo-top-bar-message').prepend(button_html);
                                }
                            }
                            // Update message bar status
                            if (enable === 'off' && enable !== '') {
                                $('#geo-top-bar-wrapper').slideUp(slide_down);
                            } else if (enable === 'on' && enable !== '' && message !== '') {
                                $('#geo-top-bar-wrapper').slideDown(slide_down);
                            }
                        }
                    }
                });
                // Hide message bar if there is no match for current country.
                if ($('#geo-top-bar-wrapper').length > 0 && current_country_selected === false) {
                    $('#geo-top-bar-wrapper').slideUp(slide_down);
                }
            });
        });
    }); // end of document ready
})(jQuery); // end of jQuery name space
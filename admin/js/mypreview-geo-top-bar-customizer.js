/**
 * GEO Top Bar - Customizer Methods.
 *
 * @author      Mahdi Yazdani
 * @package     GEO Top Bar
 * @since       1.0
 */
(function($) {
    $(function() {
        'use strict';
        /**
         * Message - Test Mode
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_test_mode_message', function(setting) {
            setting.bind(function(value) {
                var code = 'long_test_message';
                var limit = 130;
                if (value.length > limit) {
                    setting.notifications.add(code, new wp.customize.Notification(
                        code, {
                            type: 'warning',
                            message: mypreview_geo_top_bar_customizer_vars.test_msg_bar_max_char.replace('%s', limit)
                        }
                    ));
                } else {
                    setting.notifications.remove(code);
                }
                var code = 'empty_test_message';
                if (value.length == 0) {
                    setting.notifications.add(code, new wp.customize.Notification(
                        code, {
                            type: 'info',
                            message: mypreview_geo_top_bar_customizer_vars.test_msg_bar_content_req
                        }
                    ));
                } else {
                    setting.notifications.remove(code);
                }
            });
        });
        /**
         * Message Bar(s) - Message Bars
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_message_bars_repeater', function (setting) {
            setting.bind(function(value) {
                var obj = JSON.parse(value),
                    code = 'empty_message';
                obj.forEach(function(item) {
                    if (item.message.length == 0) {
                        var country = (item.country) ? ' &mdash; ' + item.country : '';
                        setting.notifications.add(code, new wp.customize.Notification(
                            code, {
                                type: 'info',
                                message: mypreview_geo_top_bar_customizer_vars.msg_bars_content_req.replace('%country_name%', country)
                            }
                        ));
                    } else {
                        setting.notifications.remove(code);
                    }
                });
            });
        });

        /**
         * Refresh repeater value(s) - Message Bar(s)
         * 
         * @since 1.0
         */
        function mypreview_geo_top_bar_refresh_repeater_values() {
            $(".mypreview-geo-top-bar-repeater-field-control-wrap").each(function() {

                var values = [];
                var $this = $(this);

                $this.find(".mypreview-geo-top-bar-repeater-field-control").each(function() {
                    var valueToPush = {};

                    $(this).find('[data-name]').each(function() {
                        var dataName = $(this).attr('data-name');
                        var dataValue = $(this).val();
                        valueToPush[dataName] = dataValue;
                    });

                    values.push(valueToPush);
                });

                $this.next('.mypreview-geo-top-bar-repeater-collector').val(JSON.stringify(values)).trigger('change');
            });
            mypreview_geo_top_bar_init_country_flag();
            mypreview_disable_preview_on_test_mode();
        }
        /**
         * Initialize country flag method - Message Bar(s)
         * 
         * @since 1.0
         */
        function mypreview_geo_top_bar_init_country_flag() {
            $('#customize-control-mypreview_geo_top_bar_message_bars_repeater_control input[data-name=country]').countrySelect({
                'preferredCountries' : [],
                'responsiveDropdown' : true
            });
            // Update message title with country name
            $('#customize-control-mypreview_geo_top_bar_message_bars_repeater_control input[data-name=country]').each(function(){
                var countryName = $(this).val();
                if(countryName !== ''){
                    $(this).closest('.mypreview-geo-top-bar-repeater-fields').prev('h3.mypreview-geo-top-bar-repeater-field-title').text(countryName);
                }
            });
        }
        mypreview_geo_top_bar_init_country_flag();
        /**
         * Bail out, and disable preview if test mode already activated - Message Bar(s)
         * 
         * @since 1.0
         */
        function mypreview_disable_preview_on_test_mode() {
            var test_mode_toggle = $('input[data-customize-setting-link=mypreview_geo_top_bar_test_mode_toggle]');
            if(test_mode_toggle.is(':checked')) {
                $('a.mypreview-geo-top-bar-repeater-field-preview').prop('disabled', true);
                $('a.mypreview-geo-top-bar-repeater-field-preview span.dashicons').removeClass('dashicons-visibility');
                $('a.mypreview-geo-top-bar-repeater-field-preview span.dashicons').addClass('dashicons-hidden');
            }
            test_mode_toggle.on('change', function(){
                if($(this).is(':checked')){
                    wp.customize.state('saved').set(true);
                    $('a.mypreview-geo-top-bar-repeater-field-preview').prop('disabled', true);
                    $('a.mypreview-geo-top-bar-repeater-field-preview span.dashicons').removeClass('dashicons-visibility');
                    $('a.mypreview-geo-top-bar-repeater-field-preview span.dashicons').addClass('dashicons-hidden');
                } else {
                    wp.customize.state('saved').set(true);
                    $('a.mypreview-geo-top-bar-repeater-field-preview').prop('disabled', false);
                    $('a.mypreview-geo-top-bar-repeater-field-preview span.dashicons').addClass('dashicons-visibility');
                    $('a.mypreview-geo-top-bar-repeater-field-preview span.dashicons').removeClass('dashicons-hidden');
                }
            });
        }
        mypreview_disable_preview_on_test_mode();
        /**
         * Handling accardion click event - Message Bar(s)
         * 
         * @since 1.0
         */
        $('#customize-theme-controls').on('click', '.mypreview-geo-top-bar-repeater-field-title', function() {
            $(this).next().slideToggle();
            $(this).closest('.mypreview-geo-top-bar-repeater-field-control').toggleClass('expanded');
        });
        /**
         * Handling add message click event - Message Bar(s)
         * 
         * @since 1.0
         */
        $('body').on('click', '.mypreview-geo-top-bar-add-control-field', function() {

            var $this = $(this).parent();
            if (typeof $this != 'undefined') {

                $('#customize-control-mypreview_geo_top_bar_message_bars_repeater_control input[data-name=country]').countrySelect('destroy');

                var field = $this.find(".mypreview-geo-top-bar-repeater-field-control:first").clone();
                if (typeof field != 'undefined') {

                    field.find("input[type='text'][data-name]").each(function() {
                        var defaultValue = $(this).attr('data-default');
                        $(this).val(defaultValue);
                    });

                    field.find("textarea[data-name]").each(function() {
                        var defaultValue = $(this).attr('data-default');
                        $(this).val(defaultValue);
                    });

                    field.find("select[data-name]").each(function(){
                        var defaultValue = $(this).attr('data-default');
                        $(this).val(defaultValue);
                    });

                    field.find('.onoffswitch').each(function() {
                        var defaultValue = $(this).next('input[data-name]').attr('data-default');
                        $(this).next('input[data-name]').val(defaultValue);
                        if (defaultValue == 'on') {
                            $(this).addClass('switch-on');
                        } else {
                            $(this).removeClass('switch-on');
                        }
                    });

                    field.find('.mypreview-geo-top-bar-fields').show();

                    $this.find('.mypreview-geo-top-bar-repeater-field-control-wrap').append(field);

                    field.addClass('expanded').find('.mypreview-geo-top-bar-repeater-fields').show();
                    $('.accordion-section-content').animate({
                        scrollTop: $this.height()
                    }, 1000);
                    mypreview_geo_top_bar_refresh_repeater_values();
                }

            }
            return false;
        });
        /**
         * Handling remove message click event - Message Bar(s)
         * 
         * @since 1.0
         */
        $('#customize-theme-controls').on('click', '.mypreview-geo-top-bar-repeater-field-remove', function() {
            if (typeof $(this).parent() != 'undefined') {
                $(this).closest('.mypreview-geo-top-bar-repeater-field-control').slideUp('normal', function() {
                    $(this).remove();
                    mypreview_geo_top_bar_refresh_repeater_values();
                });
            }
            return false;
        });
        /**
         * Handling preview message click event - Message Bar(s)
         * 
         * @since 1.0
         */
        $('#customize-theme-controls').on('click', '.mypreview-geo-top-bar-repeater-field-preview', function(event) {
            var country_name = $(this).closest('.mypreview-geo-top-bar-repeater-fields').find('input[data-name=country]').val(),
                country_code = '',
                data = '',
                country_data = $.fn.countrySelect.getCountryData();
            country_data.forEach(function(item) {
                if(country_name === item.name) {
                    country_code = item.iso2;
                }
            });
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                timeout: 10000,
                async: true,
                cache: false,
                data: {
                    wp_customize: 'on',
                    action: 'preview_country',
                    country_name: country_name,
                    country_code: country_code,
                    security: mypreview_geo_top_bar_customizer_vars.preview_nonce
                },
                success: function (result, status){
                    wp.customize.state('saved').set(true);
                },
                error: function (xhr, status, strErr){
                    console.log(status);
                },
                complete: function(xhr, status) {
					$('input[data-customize-setting-link=mypreview_geo_top_bar_message_bars_fake_refresh]').trigger('click');
                }
            });
        });
        /**
         * Handling close message click event - Message Bar(s)
         * 
         * @since 1.0
         */
        $('#customize-theme-controls').on('click', '.mypreview-geo-top-bar-repeater-field-close', function() {
            $(this).closest('.mypreview-geo-top-bar-repeater-fields').slideUp();
            $(this).closest('.mypreview-geo-top-bar-repeater-field-control').toggleClass('expanded');
        });
        /**
         * Handling on key up message field(s) event - Message Bar(s)
         * 
         * @since 1.0
         */
        $('#customize-theme-controls').on('keyup change', '[data-name]', function() {
            mypreview_geo_top_bar_refresh_repeater_values();
            return false;
        });
        /**
         * Handling on checkbox event changed - Message Bar(s)
         *
         * @since 1.0
         */
        $('#customize-theme-controls').on('change', 'input[type="checkbox"][data-name]', function() {
            if ($(this).is(":checked")) {
                $(this).val('yes');
            } else {
                $(this).val('no');
            }
            mypreview_geo_top_bar_refresh_repeater_values();
            return false;
        });
        /**
         * Handling on switch event changed - Message Bar(s)
         *
         * @since 1.0
         */
        $('body').on('click', '.onoffswitch', function() {
            var $this = $(this);
            if ($this.hasClass('switch-on')) {
                $(this).removeClass('switch-on');
                $this.next('input').val('off').trigger('change')
            } else {
                $(this).addClass('switch-on');
                $this.next('input').val('on').trigger('change')
            }
        });
        /**
         * Export all available options - Portability
         *
         * @since 1.0
         */
        $('input[name=mypreview-geo-top-bar-export-button]').click(function() {
            var customizer_url = mypreview_geo_top_bar_customizer_vars.customizer_url,
                export_nonce = mypreview_geo_top_bar_customizer_vars.export_nonce;
            window.location.href = customizer_url + '?mypreview_geo_top_bar_export_security=' + export_nonce;
        });
        /**
         * Import all available options - Portability
         *
         * @since 1.0
         */
        $('input[name=mypreview-geo-top-bar-import-button]').click(function() {
            var win = $(window),
                body = $('body'),
                form = $('<form class="mypreview-geo-top-bar-form" method="POST" enctype="multipart/form-data"></form>'),
                controls = $('.mypreview-geo-top-bar-import-controls'),
                file = $('input[name=mypreview-geo-top-bar-import-file]'),
                message = $('.mypreview-geo-top-bar-uploading'),
                msg_import_file_req = mypreview_geo_top_bar_customizer_vars.msg_import_file_req;
            if ('' == file.val()) {
                alert(msg_import_file_req);
            } else {
                win.off('beforeunload');
                body.append(form);
                form.append(controls);
                message.show();
                form.submit();
            }
        });
        /**
         * Reset plugin settings
         *
         * @since 1.0
         */
        function mypreview_geo_top_bar_reset_button(selector, reset_method) {
            var reset_button = $('<a href="#" class="mypreview-geo-top-bar-reset-btn" data-reset-method="' + reset_method + '"><span class="dashicons dashicons-image-rotate"></span> ' + mypreview_geo_top_bar_customizer_vars.reset_btn + '</a>');
            // Append button if not appended yet!
            if($(selector + ' .customize-section-title > h3 a.geo-top-bar-reset').length === 0) {
                $(selector + ' .customize-section-title > h3').append(reset_button);
            }
        }
        // Append reset button to layout section
        mypreview_geo_top_bar_reset_button('ul#sub-accordion-section-mypreview_geo_top_bar_layout_sec', 'mypreview_geo_top_bar_reset_layout');
        // Append reset button to typography section
        mypreview_geo_top_bar_reset_button('ul#sub-accordion-section-mypreview_geo_top_bar_typography_sec', 'mypreview_geo_top_bar_reset_typography');
        // Append reset button to color scheme section
        mypreview_geo_top_bar_reset_button('ul#sub-accordion-section-mypreview_geo_top_bar_color_scheme_sec', 'mypreview_geo_top_bar_reset_color_scheme');
        // Append reset button to responsiveness section
        mypreview_geo_top_bar_reset_button('ul#sub-accordion-section-mypreview_geo_top_bar_responsiveness_sec', 'mypreview_geo_top_bar_reset_responsiveness');
        // Append reset button to message bar section
        mypreview_geo_top_bar_reset_button('ul#sub-accordion-section-mypreview_geo_top_bar_message_bars_sec', 'mypreview_geo_top_bar_reset_message_bars');
        // Append reset button to test mode section
        mypreview_geo_top_bar_reset_button('ul#sub-accordion-section-mypreview_geo_top_bar_test_mode_sec', 'mypreview_geo_top_bar_reset_test_mode');
        // Handling reset button click event
        $('a.mypreview-geo-top-bar-reset-btn').click(function(event){
            event.preventDefault();
            var response = confirm(mypreview_geo_top_bar_customizer_vars.reset_confirmation),
                reset_method = $(this).data('reset-method').replace('mypreview_geo_top_bar_', '');
            if(! response){
                return;
            }
            $(this).attr('disabled', 'disabled');
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                timeout: 10000,
                async: true,
                cache: false,
                data: {
                    wp_customize: 'on',
                    reset_method: reset_method,
                    action: 'reset_plugin_settings',
                    security: mypreview_geo_top_bar_customizer_vars.reset_nonce
                },
                success: function (result, status){
                    wp.customize.state('saved').set(true);
                },
                error: function (xhr, status, strErr){
                    console.log(status);
                },
                complete: function(xhr, status) {
                    window.location.href = mypreview_geo_top_bar_customizer_vars.customizer_autofocus_pnl_url;
                }
            });
        });
    }); // end of document ready
})(jQuery); // end of jQuery name space
/**
 * GEO Top Bar - Customizer Methods.
 *
 * @author      Mahdi Yazdani
 * @package     GEO Top Bar
 * @since       1.0
 */
(function($) {
    $(function() {
        /**
         * Message - Test Mode
         *
         * @since 1.0
         */
        wp.customize('mypreview_geo_top_bar_test_mode_message', function(setting) {
            setting.bind(function(value) {
                var code = 'long_title';
                var limit = 100;
                if (value.length > limit) {
                    setting.notifications.add(code, new wp.customize.Notification(
                        code, {
                            type: 'warning',
                            message: mypreview_geo_top_bar_customizer_vars.msgBarMaxChar.replace('%s', limit)
                        }
                    ));
                } else {
                    setting.notifications.remove(code);
                }
                var code = 'required';
                if (value.length == 0) {
                    setting.notifications.add(code, new wp.customize.Notification(
                        code, {
                            type: 'info',
                            message: mypreview_geo_top_bar_customizer_vars.msgBarContentReq
                        }
                    ));
                } else {
                    setting.notifications.remove(code);
                }
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

        $('#customize-theme-controls').on('click', '.mypreview-geo-top-bar-repeater-field-title', function() {
            $(this).next().slideToggle();
            $(this).closest('.mypreview-geo-top-bar-repeater-field-control').toggleClass('expanded');
        });

        $('#customize-theme-controls').on('click', '.mypreview-geo-top-bar-repeater-field-close', function() {
            $(this).closest('.mypreview-geo-top-bar-repeater-fields').slideUp();;
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

    }); // end of document ready
})(jQuery); // end of jQuery name space
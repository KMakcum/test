(function ($) {

    $(document).ready(function () {

        // Check is checkout page
        let hash
        let sf_chekout_path
        let easypost
        if ($('body').hasClass('woocommerce-checkout')) {
            // Checkout new logic
            hash = (window.location.hash == '' || !$(window.location.hash).length) ? '#Delivery-Address' : window.location.hash, // If hask is empty and subscription don't exist - show first step
                stepFromUrl = $(hash);

            // Push initial URL state
            window.history.pushState(hash.replace('#', ''), null, hash);

            // Show step from URL
            stepFromUrl
                .removeClass('checkout-item--filled')
                .addClass('checkout-item--current')
            stepFromUrl.find('.checkout-item__body.filled').slideUp(500)
            stepFromUrl.find('.checkout-item__body.changing').slideDown(500)
            stepFromUrl.find('.checkout-head__change').hide(500)

            // Mobile steper init
            $(hash + '-mobile').parent().addClass('step-nav__item--current')

            sf_chekout_path = {
                current: hash.replace('#', ''),
                prev: '',
                latest: '',
            }

            easypost = {
                status: true
            }
        }

        $(document).on('click', '.sf_checkout_processing_step:not(.create-user-payment-token)', function (e) {
            e.preventDefault()

            let is_change = ($(this).data('mode') === 'change') ? true : false

            // Validate current step
            let is_valid = sf_checkout_validate_step(sf_chekout_path.current)
            if (!is_change) {
                if (!is_valid) {
                    return false
                }
            }

            if ($(this).data('step') === sf_chekout_path.current) {
                return false
            }

            // Validate address via EasyPost
            if ($(this).hasClass('validate-address')) {
                sf_validate_address($(this).closest('.checkout-item__body').attr('data-type'), $(this).closest('.checkout-list__item').attr('id'), true)
                // Return if easypost respond with error
                if (!easypost.status) {
                    return false
                }
            }

            // Save current step data to filled block
            sf_checkout_save_step_data(is_change)

            sf_chekout_path.prev = sf_chekout_path.current
            sf_chekout_path.current = $(this).attr('data-step')

            // Update URL and save history
            sf_save_browser_history()

            // Move to next step
            sf_checkout_process_step(is_change, false);

            // Update "save" button step, according to lates filled step
            if (is_change) {
                // Check if latest step was 'Confirmation'
                let latest_filled_step
                if (sf_chekout_path.latest == 'Confirmation') {
                    latest_filled_step = sf_chekout_path.latest
                } else {
                    // Case latest filled step selected via "change" button
                    let latest_filled_id = $('.checkout-item--filled').last().attr('data-step-number')
                    let current_id = $(this).closest('.checkout-list__item').attr('data-step-number')

                    // console.log(latest_filled_id)
                    // console.log(current_id)
                    if (current_id < latest_filled_id) {
                        latest_filled_step = $('.checkout-item--filled').last().attr('data-step')
                    }
                }
                $('#' + sf_chekout_path.current).find('.checkout-item__foot .sf_checkout_processing_step').attr('data-step', latest_filled_step)
            } else {
                // Save latest step value
                sf_chekout_path.latest = sf_chekout_path.current
            }
        })


        // Check all steps data, if last step button was clicked
        $('#place_order').on('click', function (e) {
            e.preventDefault()
            let is_valid = true

            $('.checkout-list__item').each(function () {
                if ($(this).hasClass('checkout-item--has-errors')) {
                    $(this).find('.checkout-head__change').trigger('click')
                    is_valid = false
                }
            })

            // Finalize submit, if there are no errors
            if (is_valid) {
                // Show loader
                activateLoader('main', 'body');

                $('.checkout__form').submit()
            }
        })


        //Create user payment token
        $('.create-user-payment-token').on('click', function (e) {
            // Validate current step
            let is_valid = sf_checkout_validate_step(sf_chekout_path.current)
            if (!is_valid) {
                return false
            }

            if ($(this).data('step') === sf_chekout_path.current) {
                return false
            }

            // Show loader
            activateLoader('main', 'body');

            // Validate address via EasyPost
            if ($(this).hasClass('validate-address') && !$('.hide-billing').length) {
                sf_validate_address($(this).closest('.checkout-item__body').attr('data-type'), $(this).closest('.checkout-list__item').attr('id'), true)
                // Return if easypost respond with error
                if (!easypost.status) {
                    return false
                }
            }

            $(this).attr('disabled', 'disabled');

            let step_obj = $(this)

            let data_obj = {
                action: 'create_user_pay_token',
                checkout_saved_card: $('input[name="checkout_saved_card"]').val(),
                checkout: {
                    card_number: $('#checkout-card-number').val(),
                    name_on_card: $('#checkout-name-on-card').val(),
                    exp_date: $('#checkout-exp-date').val(),
                    cvv: $('#checkout-cvc').val(),
                }
            }

            $.ajax({
                url: woocommerce_params.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: data_obj,
                success: function (response) {
                    step_obj.removeAttr('disabled');

                    // Hide loader
                    disactivateLoader('main', 'body');

                    if (response.success) {
                        e.preventDefault()

                        let is_change = (step_obj.data('mode') === 'change') ? true : false

                        // Save current step data to filled block
                        sf_checkout_save_step_data(is_change)

                        sf_chekout_path.prev = sf_chekout_path.current
                        sf_chekout_path.current = step_obj.attr('data-step')

                        // Update URL and save history
                        sf_save_browser_history()

                        // Move to next step
                        sf_checkout_process_step(is_change, false);

                        // Update "save" button step, according to lates filled step
                        if (is_change) {
                            // Check if latest step was 'Confirmation'
                            let latest_filled_step
                            if (sf_chekout_path.latest == 'Confirmation') {
                                latest_filled_step = sf_chekout_path.latest
                            } else {
                                // Case latest filled step selected via "change" button
                                let latest_filled_id = $('.checkout-item--filled').last().attr('data-step-number')
                                let current_id = step_obj.closest('.checkout-list__item').attr('data-step-number')

                                // console.log(latest_filled_id)
                                // console.log(current_id)
                                if (current_id < latest_filled_id) {
                                    latest_filled_step = $('.checkout-item--filled').last().attr('data-step')
                                }
                            }
                            $('#' + sf_chekout_path.current).find('.checkout-item__foot .sf_checkout_processing_step').attr('data-step', latest_filled_step)
                        } else {
                            // Save latest step value
                            sf_chekout_path.latest = sf_chekout_path.current
                        }
                    } else {
                        // Card data error
                        console.log(response.data.debug);
                        $('#checkout-card-number, #checkout-name-on-card, #checkout-exp-date, #checkout-cvc').addClass('error');
                    }
                },
            })
        })


        // Trigger click on mobile steper
        $(document).on('click touchend', '.step-nav__item--filled', function () {
            let clicked_step = $(this).children().attr('data-step');

            //Validate current step
            let is_valid = sf_checkout_validate_step(sf_chekout_path.current)
            if (!is_valid) {
                return false
            }

            sf_chekout_path.prev = sf_chekout_path.current
            sf_chekout_path.current = $(this).children().attr('data-step')

            // Update URL and save history
            sf_save_browser_history()

            // Move to next step
            sf_checkout_process_step(false, false)

            // Check if latest step was 'Confirmation'
            let latest_filled_step
            if (sf_chekout_path.latest == 'Confirmation') {
                latest_filled_step = sf_chekout_path.latest
            } else {
                latest_filled_step = $('.checkout-item--filled').last().attr('data-step')
            }
            $('#' + sf_chekout_path.current).find('.checkout-item__foot .sf_checkout_processing_step').attr('data-step', latest_filled_step)
        })


        // Validate fields
        function sf_checkout_validate_step(step_name) {
            let step = $('#' + step_name)
            let step_valid = true

            // Validate Delivery date separately
            if (step_name == 'Schedule-Your-First-Delivery') {
                if (!$('.checkout-item__datepicker .ui-state-active').length) {
                    $('.checkout-item__datepicker').addClass('picker-error')
                    step_valid = false
                }
            } else {
                step.find('.validate-required .field-box__field').each(function () {
                    // Check is not empty
                    if ($(this).val() == '') {
                        $(this).addClass('error')
                        step_valid = false
                    } else {
                        // Validate separate fields
                        if ($(this).attr('id') == 'checkout-card-number' && $(this).hasClass('jp-card-invalid')) {
                            $(this).addClass('error')
                            step_valid = false
                        }
                        if ($(this).attr('id') == 'checkout-exp-date') {
                            let exp_string = $(this).val()

                            if (exp_string.search('M') > 0 || exp_string.search('Y') > 0) {
                                $(this).addClass('error')
                                step_valid = false
                            }

                            //TODO: make validation by past date
                        }
                    }

                    // Validate if current field is email
                    if ($(this).attr('type') == 'email') {
                        if (!validateEmail($(this).val())) {
                            step_valid = false
                        }
                    }
                })

                step.find('.filled-validate-required .field-box__field').each(function () {
                    if ($(this).attr('id') == 'checkout-card-number' && $(this).hasClass('jp-card-invalid')) {
                        $(this).addClass('error')
                        step_valid = false
                    }
                })
            }

            // Add error class to step, if it's not valid
            if (!step_valid) {
                step.addClass('checkout-item--has-errors')
            } else {
                step.removeClass('checkout-item--has-errors')
            }


            return step_valid
        }

        // Validate all steps
        function sf_checkout_validate_form() {

        }

        function sf_checkout_validate_step_status(step_name) {
            let step = $('#' + step_name)
            let step_valid = true

            // Validate Delivery date separately
            if (step_name == 'Schedule-Your-First-Delivery') {
                if (!$('.checkout-item__datepicker .ui-state-active').length) {
                    $('.checkout-item__datepicker').addClass('picker-error')
                    step_valid = false
                }
            } else {
                step.find('.validate-required .field-box__field').each(function () {
                    // Check is not empty
                    if ($(this).val() == '') {
                        step_valid = false
                    } else {
                        // Validate separate fields
                        if ($(this).attr('id') == 'checkout-card-number' && $(this).hasClass('jp-card-invalid')) {
                            step_valid = false
                        }
                        if ($(this).attr('id') == 'checkout-exp-date') {
                            let exp_string = $(this).val()

                            if (exp_string.search('M') > 0 || exp_string.search('Y') > 0) {
                                step_valid = false
                            }

                            //TODO: make validation by past date
                        }
                    }

                    // Validate if current field is email
                    if ($(this).attr('type') == 'email') {
                        if (!validateEmail($(this).val())) {
                            step_valid = false
                        }
                    }
                })

                step.find('.filled-validate-required .field-box__field').each(function () {
                    if ($(this).attr('id') == 'checkout-card-number' && $(this).hasClass('jp-card-invalid')) {
                        step_valid = false
                    }
                })
            }

            return step_valid
        }

        // Validate required checkboxes
        function sf_checkout_validate_checkboxes(step_name) {
            let step = $('#' + step_name)
            let step_valid = true

            step.find('.validate-checkbox').each(function () {
                if (!$(this).is(':checked')) {
                    step_valid = false
                }
            })

            return step_valid
        }

        // Disable next step button on value change
        $('.validate-required .field-box__field, .filled-validate-required .field-box__field').focusout(function () {
            let is_still_valid = sf_checkout_validate_step_status(sf_chekout_path.current)
            let is_checkboxes_valid = sf_checkout_validate_checkboxes(sf_chekout_path.current)

            if (!is_still_valid || !is_checkboxes_valid) {
                $(this).addClass('error');
                $('#' + sf_chekout_path.current + ' .checkout-item__button').attr('disabled', 'disabled')
            } else {
                $('#' + sf_chekout_path.current + ' .checkout-item__button').removeAttr('disabled')
            }
        })

        // Validate addresses via EasyPost // DO NOT DELETE
        // let inputs = ['billing_address_1', 'billing_address_2', 'shipping_address_1', 'shipping_address_1']

        // inputs.forEach(function(input){
        //   $('#'+input).on('keyup', _.debounce(() => {
        //     if ($('#'+input).val().length >= 6) {
        //       sf_validate_address($('#'+input).closest('.checkout-item__body').attr('data-type'), input)
        //     }
        //   }, 400))
        // })

        function sf_validate_address(address_type, field = '', step_submit = false) {
            let form_data = new FormData($('.checkout__form')[0]);

            form_data.append('action', 'sf_ep_check_address')
            form_data.append('address_type', address_type)
            form_data.append('field_type', field)

            $.ajax({
                url: woocommerce_params.ajax_url,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                async: false,
                data: form_data,
                type: 'post',
                success: function (response) {
                    if (response.success) {
                        // Validate and update address according to Response from Easypost
                        if (step_submit) {
                            for (const property in response.data) {
                                // if (response.data[property] !== '') {
                                $('#' + address_type + '_' + property).val(response.data[property])
                                // }
                            }
                        }

                        // Save status for step actions
                        easypost.status = true
                    } else {
                        if (response.data.field == 'address') {
                            $('#' + field).find('#' + address_type + '_address_1').addClass('error')
                            $('#' + field).find('.checkout-item__button').attr('disabled', 'disabled')

                            // Save status for step actions
                            easypost.status = false
                        }
                    }
                },
            })
        }


        // Save step to browser history
        function sf_save_browser_history() {
            window.history.pushState(sf_chekout_path.current, null, '#' + sf_chekout_path.current);

            window.addEventListener('popstate', function (event) {
                sf_chekout_path.current = event.state

                // Move to next step
                sf_checkout_process_step(false, true)

                // Check if latest step was 'Confirmation'
                // let latest_filled_step
                // if (sf_chekout_path.latest == 'Confirmation') {
                //   latest_filled_step = sf_chekout_path.latest
                // }else {
                //   latest_filled_step = $('.checkout-item--filled').last().attr('data-step')
                // }
                // $('#' + sf_chekout_path.current).find('.checkout-item__foot .sf_checkout_processing_step').attr('data-step', latest_filled_step)
            });
        }


        // Input type 'emaile' field validation
        function validateEmail(email) {
            const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        }


        function getWeekDay(number) {
            let weekday = new Array(7);
            weekday[0] = "Sunday";
            weekday[1] = "Monday";
            weekday[2] = "Tuesday";
            weekday[3] = "Wednesday";
            weekday[4] = "Thursday";
            weekday[5] = "Friday";
            weekday[6] = "Saturday";

            return weekday[number]
        }


        function getMonthName(number) {
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

            return monthNames[number]
        }


        //Save step data to 'filled' block
        function sf_checkout_save_step_data(is_change) {
            switch (sf_chekout_path.current) {
                case 'Delivery-Address':
                    // let address_line = $('#billing_address_1').val() + ', ' + $('#billing_city').val() + ', ' + $('#billing_postcode').val() + '<br>'
                    //                           + $('#billing_first_name').val() + ', ' + $('#billing_last_name').val() + ', ' + $('#checkout-email').val() + '<br>'
                    //                           + $('#checkout-phone').val()
                    let address_line = $('#billing_first_name').val() + ' ' + $('#billing_last_name').val() + ',<br>'
                        + $('#billing_address_1').val() + ',<br>'
                        + $('#billing_city').val() + ', ' + $('#billing_state').val() + '.<br>'
                        + $('#billing_postcode').val()

                    $('#Delivery-Address .data-block__txt').html(address_line)
                    break

                case 'Schedule-Your-First-Delivery':
                    let delivery_date = new Date(Date.parse($('#sf_first_delivery').text()))

                    let every_line = getWeekDay(delivery_date.getDay())
                    // let delivery_line = getWeekDay(delivery_date.getDay()) + ', ' + getMonthName(delivery_date.getMonth()) + ' ' + delivery_date.getDate() + ', ' + delivery_date.getFullYear()
                    let delivery_line = getMonthName(delivery_date.getMonth()) + ' ' + delivery_date.getDate() + ', ' + delivery_date.getFullYear();

                    $('.dynamic-delivery-day').text(every_line)
                    $('.dynamic-delivery-date').text(delivery_line)
                    break

                case 'Payment-Method':
                    let card_line = $('.field-box--card-number input').val()

                    // Remove filled block cc type
                    let card_type
                    if (typeof card_type !== 'undefined') {
                        $('.card-data__number').removeClass(card_type)
                    }

                    // if ($('.field-box--card-number input').attr('data-type')) { // If subscription exists
                    //   card_type = $('.field-box--card-number input').attr('data-type')
                    // }else { // If not
                    let card_types = ['mastercard', 'visa', 'visaelectron', 'amex', 'dankort', 'dinersclub', 'discover', 'elo', 'hipercard', 'jcb', 'maestro', 'troy', 'unionpay']
                    card_types.forEach(function (current_type) {
                        if ($('.field-box--card-number input').hasClass(current_type)) {
                            card_type = current_type
                        }
                    });
                    // }
                    $('.card-data__number').addClass(card_type)

                    let current_last4 = (card_line.substring(card_line.length - 4) !== '') ? card_line.substring(card_line.length - 4) : $('.field-box--card-number input').attr('data-last4')
                    $('.card-data__number-inner').text(current_last4)

                    let expire_line = $('#checkout-exp-date').val()
                    $('.card-data__exp-inner').text(expire_line);

                    let payment_line = ''
                    if ($('#checkout-use-as-billing-address').is(':checked')) {
                        // payment_line = $('#billing_address_1').val() + ', ' + $('#billing_city').val() + ', ' + $('#billing_postcode').val() + '<br>'
                        //                         + $('#billing_first_name').val() + ', ' + $('#billing_last_name').val() + '<br>'
                        //                         + $('#checkout-phone').val()
                        payment_line = $('#billing_first_name').val() + ' ' + $('#billing_last_name').val() + ',<br>'
                            + $('#billing_address_1').val() + ',<br>'
                            + $('#billing_city').val() + ', ' + $('#billing_state').val() + '.<br>'
                            + $('#billing_postcode').val()
                    } else {
                        // payment_line = $('#shipping_address_1').val() + ', ' + $('#shipping_city').val() + ', ' + $('#shipping_postcode').val() + '<br>'
                        //                         + $('#shipping_first_name').val() + ', ' + $('#shipping_last_name').val() + '<br>'
                        //                         + $('#checkout-shipping-phone').val()
                        payment_line = $('#shipping_first_name').val() + ' ' + $('#shipping_last_name').val() + ',<br>'
                            + $('#shipping_address_1').val() + ',<br>'
                            + $('#shipping_city').val() + ', ' + $('#shipping_state').val() + '.<br>'
                            + $('#billing_postcode').val()
                    }
                    $('#Payment-Method .data-block__txt').html(payment_line)
                    break
            }
        }


        // Reload page on coupon add
        // $('.checkout_coupon').on('submit', function() {
        //   sf_coupon_reload()
        // })

        // Reload page on coupon remove
        $(document).on('click', '.woocommerce-cart .woocommerce-remove-coupon', function () {
            sf_coupon_reload()
        })

        // Reload function
        function sf_coupon_reload() {
            $(document).ajaxComplete(function (event, xhr, settings) {
                document.location.reload()
            })
        }


        // Checkout steps function
        function sf_checkout_process_step(is_change, is_history) {
            let checkoutList = $('.checkout__list')

            // reset prev error displaying
            $('.checkout-item__datepicker').removeClass('picker-error')
            $('[data-name]').attr('style', '')

            if (!is_history) {
                checkoutList.find('.checkout-item__body.changing').slideUp(500)

                if (sf_chekout_path.prev) {
                    let prevStep = $('#' + sf_chekout_path.prev)

                    // Mobile steper
                    $('#' + sf_chekout_path.prev + '-mobile').parent()
                        .removeClass('step-nav__item--current')
                        .addClass('step-nav__item--filled')

                    // Show filled block data
                    prevStep.find('.checkout-item__body.filled').slideDown(500)

                    if ('Confirmation' != sf_chekout_path.prev) {
                        prevStep
                            .removeClass('checkout-item--current')
                            .addClass('checkout-item--filled')
                        prevStep.find('.checkout-head__change').show(500)
                    }
                }
            } else {
                // Remove all current classes
                $('.checkout-list__item').removeClass('checkout-item--current');
                checkoutList.find('.checkout-list__item:not(' + '#' + sf_chekout_path.current + ') .checkout-item__body.changing').slideUp(500)
            }

            let thisStep = $('#' + sf_chekout_path.current)

            thisStep
                .removeClass('checkout-item--filled')
                .addClass('checkout-item--current')
            thisStep.find('.checkout-item__body.filled').slideUp(500)
            thisStep.find('.checkout-item__body.changing').slideDown(500)
            thisStep.find('.checkout-head__change').hide(500)

            // Mobile steper
            $('#' + sf_chekout_path.current + '-mobile').parent()
                .removeClass('step-nav__item--filled')
                .addClass('step-nav__item--current')

            setTimeout(function () {
                $('html,body').animate(
                    {scrollTop: thisStep.offset().top},
                    300
                )
            }, 500)
        }


        // Update checkout step data
        $(document).on('click', '.sf_checkout_update_step', function () {
            // Validate address via EasyPost
            if ($(this).hasClass('validate-address')) {
                sf_validate_address($(this).closest('.checkout-item__body').attr('data-type'), $(this).closest('.checkout-list__item').attr('id'), true)
                // Return if easypost respond with error
                if (!easypost.status) {
                    return false
                }
            }

            let form_data = new FormData($('.checkout__form')[0]);

            form_data.append('action', 'sf_checkout_update_step')
            form_data.append('step', $(this).closest('.checkout-list__item').attr('data-step'))
            if ($(this).closest('.checkout-list__item').find('.iti__selected-dial-code').length) {
                form_data.append('phone_code', $(this).closest('.checkout-list__item').find('.iti__selected-dial-code').text())
            }

            $.ajax({
                url: woocommerce_params.ajax_url,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function (response) {
                    if (response.success) {
                        // Redirect to cart
                        window.location.href = '/cart';
                    } else {
                        if (response.data.step_errors) {
                            for (const item of response.data.step_errors) {
                                switch (item) {
                                    case 'delivery_date':
                                        $('.checkout-item__datepicker').attr(
                                            'style',
                                            'outline: 1px solid #f00'
                                        )
                                        break

                                    default:
                                        $(`[data-name="${item}"] .field-box__field`).addClass('error')
                                        break
                                }
                            }
                        }
                    }
                },
            })
        });

        /**
         * Check checkboxes policy & terms for 'save & continue' button
         */

        $('#checkout-terms-and-conditions-agreements').on('click touchend', function () {
            let is_still_valid = sf_checkout_validate_step(sf_chekout_path.current)

            if (is_still_valid) {
                sf_check_disabled_step_btn('#checkout-terms-and-conditions-agreements', 'button[data-step="Confirmation"]')
            }
        })
        if ($('.checkout-terms-and-conditions-agreements').length) {
            if ($('.checkout-terms-and-conditions-agreements').is(':checked')) {
                $(this).closest('.checkout-list__item').find('.sf_checkout_processing_step').removeAttr('disabled');
            }
        }

        $('#checkout-use-as-billing-address-2').click(function () {
            sf_check_disabled_step_btn('#checkout-use-as-billing-address-2', '#place_order')
        })

        function sf_check_disabled_step_btn($checkbox_selector, $btn_selector) {

            if ($($checkbox_selector).prop('checked')) {
                $($btn_selector).attr('disabled', false)
            } else {
                $($btn_selector).attr('disabled', true)
            }
        }

        /**
         * Disabled billing state if country not Unates State
         */

        if ($('#billing_country').length) {

            $('#billing_country').on('change', function () {

                if ('US' != this.value) {
                    $('#billing_state').val('selectedIndex', 0).attr('disabled', true)
                } else {
                    $('#billing_state').attr('disabled', false)
                }
            })
        }

        $(document).on('submit', '.op_ajax_save_subscription', function (e) {
            e.preventDefault()
            let $form = $(this),
                $result = $form.find('.test-result')
            form_data = new FormData()
            form_data.append('action', 'op_update_subscription')
            form_data.append('form', $form.serialize())

            $result.attr('style', '').html()
            jQuery.ajax({
                url: woocommerce_params.ajax_url,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function (response) {
                    if (response.success) {

                        $result.html(response.data)

                    } else {

                        console.log(response)

                    }

                }
            })

        })

        /**
         * User registration
         */

        $('#register_form').submit(function (e) {

            const $form = $(this);
            const $formParent = $form.parents('.modal-common__data');

            // Check password strength
            // Get current pwd state
            var pwdBoxItems = $('#pr-box:eq(0) ul li'),
                checkedCount = 0,
                totalCount = 0;

            // Check each point of pwd box
            pwdBoxItems.each(function () {
                if ($(this).find('.pr-ok').length) {
                    checkedCount++;
                }

                totalCount++;
            });
            // \Check password strength

            e.preventDefault()
            var data = {
                action: 'user_registration',
                ajax_nonce: main.ajax_nonce,
                form: $('#register_form').serialize(),
                pwd: {
                    totalC: totalCount,
                    checkedC: checkedCount
                }
            }
            $.ajax({
                type: 'POST',
                url: main.ajaxurl,
                data: data,
                beforeSend: function () {
                    $('input[name="register"]').attr('disabled', true)
                },
                complete: function () {
                    $('input[name="register"]').removeAttr('disabled')
                },
                success: function (response) {
                    if (response.data.status && response.data.status === 'sended') {
                        window.location.replace(response.data.redirectUrl)
                    }

                    if (response.data.status && response.data.status === 'login') {
                        if (window.location === response.data.redirectUrl) {
                            window.reload()
                        }
                        window.location.replace(response.data.redirectUrl)
                    }

                    if (response.data.status && response.data.status === 'sent') {
                        window.location.replace(response.data.redirectUrl)
                    }
                    if (response.data.status && response.data.status === 'verified') {
                        let userEmail = $('#modal-sign-up-email').val();
                        $('.link-2.btn-modal[href="#js-modal-sign-in"]')[0].click()
                        $('#modal-sign-in-email').val(userEmail)
                        $('#modal-sign-in-email').addClass('field-box__field--entered')
                    }


                    if (response.data.message == 'authentication_failed') {
                        var error_msg = 'Invalid username or incorrect password.'
                    } else {
                        var error_msg = response.data.message
                    }

                    if (response.success == false) {
                        $formParent.find('.message--full.message--success').css({display: 'none'})
                        $formParent.find('.message--full.message--error').css({display: 'flex'})
                        $formParent.find('#error_register').text(error_msg)
                    } else {
                        $formParent.find('.message--full.message--error').css({display: 'none'})
                        $formParent.find('.message--full.message--success').css({display: 'flex'})
                        $formParent.find('#success_register').text(error_msg)

                        $formParent.find('#register_form #modal-sign-up-email').val('')
                        $formParent.find('#register_form .field-box__field--password').val('')
                    }
                },
                error: function (response) {
                    document.location.reload()
                }
            })
        })

        /**
         * User authorization
         */

        $('#login_form').submit(function (e) {

            const $form = $(this);
            const $formParent = $form.parents('.modal-common__data');

            e.preventDefault()
            var data = {
                action: 'user_login',
                ajax_nonce: main.ajax_nonce,
                form: $('#login_form').serialize(),
            }

            $.ajax({
                type: 'POST',
                url: main.ajaxurl,
                data: data,
                beforeSend: function () {
                    $('input[name="login"]').attr('disabled', true)
                },
                complete: function () {
                    $('input[name="login"]').removeAttr('disabled')
                },
                success: function (response) {

                    if (response.success == false) {
                        $formParent.find('.message--full.message--error').css({display: 'flex'})
                        $formParent.find('#error_login').text(response.data.message)
                    } else {
                        // After auth redirect to account page
                        document.location.href = response.data.redirect_url
                    }

                }
            })
        })

        /**
         * Remove product from cart
         */

        $('.remove_from_cart').on('click', function (e) {

            var $this = $(this)
            var data = {
                action: 'op_remove_from_cart',
                ajax_nonce: main.ajax_nonce,
                product_id: $this.data('product_id'),
                cart_product_key: $this.data('cart-product-key'),
            }

            $.ajax({
                type: 'POST',
                url: main.ajaxurl,
                data: data,
                beforeSend: function () {
                    $this.attr('disabled', true)
                },
                complete: function () {
                    $this.removeAttr('disabled')
                },
                success: function (response) {

                    console.log(response)
                    // if ( response.success ) {

                    // }
                }
            })
        })

        /**
         * add to cart
         */
        $('.js-product-list').on('click', 'a.ajax_add_to_cart', function (evt) {
            evt.preventDefault()

            var $thisbutton = $(this)

            if ($thisbutton.is('.ajax_add_to_cart')) {

                if (!$thisbutton.attr('data-product_id')) {
                    return true
                }

                $thisbutton.removeClass('added')
                $thisbutton.addClass('loading')

                var data = {}

                // Fetch changes that are directly added by calling $thisbutton.data( key, value )
                $.each($thisbutton.data(), function (key, value) {
                    data[key] = value
                })

                // Fetch data attributes in $thisbutton. Give preference to data-attributes because they can be directly modified by javascript
                // while `.data` are jquery specific memory stores.
                $.each($thisbutton[0].dataset, function (key, value) {
                    data[key] = value
                })

                $(document.body).trigger('adding_to_cart', [$thisbutton, data])

                $.ajax({
                    type: 'POST',
                    url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
                    data: data,
                    dataType: 'json',
                    beforeSend: function () {
                        // $this.attr("disabled", true);
                    },
                    complete: function () {
                        // $this.removeAttr("disabled");
                    },
                    success: function (response) {
                        if (!response) {
                            return
                        }

                        if (response.error && response.product_url) {
                            window.location = response.product_url
                            return
                        }

                        $.each(response.fragments, function (key, value) {
                            $(key).replaceWith(value)
                        })

                        $(document.body).trigger('wc_fragments_loaded')
                    }
                })
            }

        })

        $('.js-product-list').on('click', '.add-button', function (evt) {
            animationAddProduct(evt);
        });

        $('.product-group__slider').on('click', '.add-button', function (evt) {
            animationAddProduct(evt);
        });

        $('.form-add-to-cart').on('click', '.add-button', function (evt) {
            animationAddProduct(evt);
        });

        // START ANIMATIONS WHEN ADDING A PRODUCT TO THE CART
        ////////////////////////////////////////////////////////////////////////////
        $(document).on('click', '.add-button', function (e) {

            if ($('[name="category_redirect"]').length > 0) {

                let totalQnt = getTotalQntCart();

                if (totalQnt < 14) {

                    let $this = $(this);
                    let $headerCart = $('.header-cart');

                    $headerCart.addClass('header-cart--adding');

                    if ($this.hasClass('add-button--added')) {
                        $this.addClass('add-button--adding');
                    }

                    if (!$this.hasClass('add-button--added')) {
                        $this.addClass('add-button--first-adding');
                    }

                    $this.addClass('add-button--added');

                    setTimeout(() => {
                        $headerCart.removeClass('header-cart--adding');
                        $this.removeClass('add-button--adding add-button--first-adding');
                    }, 850);

                }

            } else {
                let $this = $(this);

                let $headerCart = $('.header-cart');

                $headerCart.addClass('header-cart--adding');

                if ($this.hasClass('add-button--added')) {
                    $this.addClass('add-button--adding');
                }

                if (!$this.hasClass('add-button--added')) {
                    $this.addClass('add-button--first-adding');
                }

                $this.addClass('add-button--added');

                setTimeout(() => {
                    $headerCart.removeClass('header-cart--adding');
                    $this.removeClass('add-button--adding add-button--first-adding');
                }, 850);
            }

        });

        $(document).on('click', '.product-item__button', function (e) {
            e.preventDefault();

            let $this = $(this);
            let $headerCart = $('.header-cart');

            $headerCart.addClass('header-cart--adding');

            if ($this.hasClass('product-item__button--added')) {
                $this.addClass('product-item__button--adding');
            }

            if (!$this.hasClass('product-item__button--added')) {
                $this.addClass('product-item__button--first-adding');
            }

            $this.addClass('product-item__button--added');

            setTimeout(() => {
                $headerCart.removeClass('header-cart--adding');
                $this.removeClass('product-item__button--adding product-item__button--first-adding');
            }, 850);
        });
        // END ANIMATIONS WHEN ADDING A PRODUCT TO THE CART
        ////////////////////////////////////////////////////////////////////////////

        const animationAddProduct = function (evt) {
            let btn = evt.target;

            if (!evt.target.classList.contains('add-button')) {
                btn = evt.target.parentNode;
            }

            // let spanText = btn.querySelector('.add-button__txt-2');

            let quantity = parseInt(btn.dataset.added);

            if (isNaN(quantity)) {
                quantity = 1;
                btn.dataset.added = quantity;
            } else {
                quantity = quantity + 1;
                btn.dataset.added = quantity;
            }

            // change text
            // if (quantity >= 1) {
            //  spanText.innerText = `Added`;
            // }
        };

        /**
         * Add to cart single product
         */
        function objectifyForm(formArray) {
            let returnArray = {}
            for (let i = 0; i < formArray.length; i++) {
                returnArray[formArray[i]['name']] = formArray[i]['value']
            }
            return returnArray
        }

        const form = $('.footer-product-details__form')

        /**
         * Add product single
         */
        form.on('submit', function (e) {
            e.preventDefault();
            addFrequencySingleProduct();
            addSingleProduct();
        });

        const addFrequencySingleProduct = function () {
            const formSelector = $('.form-add-to-cart')
            const frequency = $('.product-details__body .options-section .select__field option:selected').val();
            let productId = formSelector.find('[name="add-to-cart"]').val()
            $.ajax({
                type: 'POST',
                url: ajaxSettings.ajax_url,
                data: {
                    'action': 'add_frequency_to_item',
                    'frequency': frequency,
                    'product_id': productId,
                },
                dataType: 'json',
                success: function (response) {
                }
            })
        }

        const addSingleProduct = function () {
            const formSelector = $('.form-add-to-cart')
            let productId = formSelector.find('[name="add-to-cart"]').val()
            let quantity = formSelector.find('[name="quantity"]').val()
            let categoryPage = formSelector.find('[name="category_redirect"]').val()
            const data = {
                'product_id': productId,
                'quantity': quantity
            }
            $.ajax({
                type: 'POST',
                url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (!response) {
                        return
                    }
                    if (response.error && response.product_url) {
                        window.location = response.product_url
                        return
                    }
                    if (response.error && !response.product_url) {
                        $('.btn-modal[href="#js-modal-remove-item"]').click();
                    } else {
                        saveCartData();
                        $.each(response.fragments, function (key, value) {
                            $(key).replaceWith(value)
                        })
                        $(document.body).trigger('wc_fragments_loaded')
                        document.location.href = categoryPage
                    }
                }
            })
        }

        /**
         * Save in Local Storage cart state
         */
        const saveCartData = function () {
            let formData = new FormData()
            formData.append('action', 'get_cart_data')
            $.ajax({
                url: wc_add_to_cart_params.ajax_url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    localStorage.setItem('cart', JSON.stringify(response.data.cart));
                }
            })
        };

        saveCartData();

        const getTotalQntCart = function () {
            const cart = JSON.parse(localStorage.getItem('cart'));
            let totalQnt = 0;
            if (cart !== null) {
                cart.forEach((item) => {
                    totalQnt += item.quantity;
                });
            }

            return totalQnt;
        }

        /**
         * Change products count at catalog load
         */
        $('.products').on('ajax_product_load_end', function (evt) {
            const event = new Event('init_ajax_rating');
            document.dispatchEvent(event);
        });


        $('.btn-modal').on('click', function (evt) {
            if ($('.page-template-template-our-story').length) {
                $('#js-modal-zip-code [name="redirect"]').val($(this).data('redirect'));
            } else if ($('body.error404').length) {
                $('#js-modal-zip-code [name="redirect"]').val($(this).data('redirect'));
                $('#js-modal-zip-code [name="current_page"]').val('error404');
            } else {
                let isSignUp = evt.target.classList.contains('show-signup');
                let isFooterLink = evt.target.classList.contains('footer-links');

                if (isSignUp) {
                    $('#js-modal-zip-code [name="sign_up_flow"]').val(isSignUp);
                } else {
                    $('#js-modal-zip-code [name="sign_up_flow"]').val(false);
                }

                if (isFooterLink) {
                    $('#js-modal-zip-code [name="redirect"]').val(evt.target.dataset.redirect);
                }
            }
        });

        // personal event listener for home slider left button
        $('.offer-slider__item .show-signup').on('click', function (e) {
            let isSignUp = e.target.classList.contains('show-signup');
            if (isSignUp) {
                $('#js-modal-zip-code [name="sign_up_flow"]').val(isSignUp);
            } else {
                $('#js-modal-zip-code [name="sign_up_flow"]').val(false);
            }
        });

        // personal event listener for home meal slider
        $('.on-the-menu .product-item__img-link, .on-the-menu .product-item__name-link ').on('click', function (e) {
            $('#js-modal-zip-code [name="redirect"] ').val($(this).data('redirect'));
            $('#js-modal-zip-code .fields-list__item ').append(
                $('<input/>', {
                    'id': 'is-front-meal-slider',
                    'name': 'is-front-meal-slider',
                    'type': 'hidden',
                    'value': true
                })
            );
        });

        /**
         * Save zip-code for user
         */

        $(document).on('submit', '#zipcode_form', function (e) {

            e.preventDefault()
            var data = {
                action: 'zipcode_user',
                ajax_nonce: main.ajax_nonce,
                form: $('#zipcode_form').serialize(),
            }

            $.ajax({
                type: 'POST',
                url: main.ajaxurl,
                data: data,
                beforeSend: function () {
                    $('#zipcode_form .form__button').attr('disabled', true)
                },
                complete: function () {
                    $('#zipcode_form .form__button').removeAttr('disabled')
                },
                success: function (response) {
                    if (response.success) {
                        // Check is this zip setup is initial
                        if (response.data.is_initial) {
                            // Make redirect empty, if is_ajax true
                            if (response.data.is_ajax !== '') {
                                response.data.redirect = '';
                            }

                            // if ( window.location.pathname === '/' && response.data.need_signup ) {
                            if (response.data.need_signup) {
                                if (response.data.zip !== '') {
                                    let registerTitle = document.querySelector('.modal-sign-up .data__header h3');
                                    $('.header-zip-update-link').attr('href', '#js-modal-sign-up');
                                    $('#js-modal-zip-code .modal-common__close').trigger('click');

                                    if (!registerTitle) {
                                        let registerHeader = `<h3>${response.data.register_title}</h3>`;
                                        document.querySelector('.modal-sign-up .data__header').insertAdjacentHTML('afterbegin', registerHeader);
                                    } else {
                                        registerTitle.innerHTML = response.data.register_title;
                                    }

                                    document.querySelector('.header-zip-update-link').click();

                                    $('#js-modal-sign-up .modal-common__close').on('click', () => {
                                        document.location.href = response.data.redirect;
                                    });

                                }
                            }
                            // Reload zip, if page in reload list
                            if (response.data.reload_anyway || response.data.redirect !== '' && !response.data.need_signup) {

                                if (response.data.show == 'national'
                                    && (response.data.is_front_meal_slider
                                        || response.data.current_page === 'error404'
                                        || (response.data.current_page === 'our-story'
                                            && (response.data.redirect === '/product-category/meals/'
                                                || response.data.redirect === '/groceries/'
                                            )
                                        )
                                    )
                                ) {
                                    document.location.href = '/offerings/';
                                } else if (response.data.show == 'overnight'
                                    && response.data.current_page === 'our-story'
                                    && response.data.redirect === '/product-category/meals/'
                                ) {
                                    document.location.href = '/offerings/';
                                } else {
                                    document.location.href = response.data.redirect;
                                }
                            } else {
                                // Update zip
                                $('.header-zip-value').text(response.data.zip); // Update all page values

                                // Update signup modal href
                                $('.header-zip-update-link').attr('href', '#js-modal-sign-up');
                                // Update promo bar href
                                if ($('.promo-bar__button').length) {
                                    $('.promo-bar__button').attr('href', '#js-modal-promo-code-to-use');
                                }
                                // Update header menu items
                                bulk_urls_update('#menu-header');
                                // Update all content links
                                bulk_urls_update('.site-main');
                                // Update footer links
                                bulk_urls_update('.main-footer__nav');

                                // Update template variable
                                user_zip_code = response.data.zip; // TODO: Is this update necessary?

                                // Close current
                                $('#js-modal-zip-code .modal-common__close').trigger('click');
                            }
                        } else {
                            // Close current
                            $('#js-modal-zip-code .modal-common__close').trigger('click');

                            $('.new-zip').text(response.data.zip);

                            // Open updated modal
                            // Fix for updated modal
                            if (response.data.show == 'updated') {
                                $('#js-modal-change-zip-code-' + response.data.show + ' .message--warning').hide();
                            }
                            $.fancybox.open({
                                src: '#js-modal-change-zip-code-' + response.data.show,
                            });

                            // Redirect user on modal close
                            $('#js-modal-change-zip-code-' + response.data.show + ' .fancybox-close-small, .fancybox-slide--current').on('click', function () {
                                document.location.href = response.data.redirect
                            });
                        }
                    } else {
                        if (response.data.confirm) {
                            // Close current
                            $('#js-modal-zip-code .modal-common__close').trigger('click');

                            $('.new-zip').text(response.data.zip);

                            // Open updated modal
                            $.fancybox.open({
                                src: '#js-modal-change-zip-code-' + response.data.show,
                            });

                            // Close modal on "keep zip" button click
                            $(document).on('click', '.keep-zip-code', function () {
                                $('#js-modal-change-zip-code-' + response.data.show + ' .fancybox-close-small').trigger('click');
                            });
                        } else {
                            // Close current
                            $('#js-modal-zip-code .fancybox-close-small').trigger('click');

                            // Save zip to form input
                            $('.zip-code-modal-field').val(response.data.zip).addClass('field-box__field--entered');
                            // Save address data
                            $('#modal-change-delivery-details-city-state').val(response.data.city + ', ' + response.data.state).addClass('field-box__field--entered')
                            $('#modal-change-delivery-details-country').val(response.data.country).addClass('field-box__field--entered')

                            // Show modal with address update
                            $.fancybox.open({
                                src: '#js-modal-change-zip-code-' + response.data.show,
                            });
                        }
                    }
                },
            })
        })

        function bulk_urls_update(element) {
            $(element + ' a[href="#js-modal-zip-code"]').each(function () {
                var new_href = $(this).attr('data-redirect');
                $(this).attr('href', new_href).addClass('ajax-updated-item');
            });
        }

        // Fix for links,that were update after ajax zip
        $(document).on('click', '.ajax-updated-item', function (e) {
            e.preventDefault();
            $('.fancybox-button--close').trigger('click'); // TODO: Update this fix to normal solution
            document.location.href = $(this).attr('href');
        });

        // Confirm update to national ZIP code
        $(document).on('click', '.change-zip-code', function (e) {
            e.preventDefault()
            var data = {
                action: 'zipcode_user',
                ajax_nonce: main.ajax_nonce,
                form: $('#zipcode_form').serialize(),
                confirm: true,
            }

            $.ajax({
                type: 'POST',
                url: main.ajaxurl,
                data: data,
                beforeSend: function () {
                    $('.change-zip-code').attr('disabled', true)
                },
                complete: function () {
                    $('.change-zip-code').removeAttr('disabled')
                },
                success: function (response) {
                    if (response.success) {
                        // Close current
                        $('#js-modal-change-zip-code-national .fancybox-close-small').trigger('click');

                        $('.new-zip').text(response.data.zip);
                        if (response.data.update_result > 0) {
                            $('.removed-items').text(response.data.update_result + ((response.data.update_result > 1) ? ' items' : ' item'));
                            $('#js-modal-change-zip-code-' + response.data.show + ' .message--warning').show();
                        } else {
                            $('#js-modal-change-zip-code-' + response.data.show + ' .message--warning').hide();
                        }

                        // Open updated modal
                        $.fancybox.open({
                            src: '#js-modal-change-zip-code-' + response.data.show,
                        });

                        // Redirect user on modal close
                        $('#js-modal-change-zip-code-' + response.data.show + ' .fancybox-close-small, .fancybox-slide--current').on('click', function () {
                            document.location.href = response.data.redirect;
                        });
                    } else { // National zip with existing subscription
                        $('#js-modal-change-zip-code-national .fancybox-close-small').trigger('click');

                        // Save zip to form input
                        $('.zip-code-modal-field').val(response.data.zip).addClass('field-box__field--entered');
                        // Save address data
                        $('#modal-change-delivery-details-city-state').val(response.data.city + ', ' + response.data.state).addClass('field-box__field--entered')
                        $('#modal-change-delivery-details-country').val(response.data.country).addClass('field-box__field--entered')

                        // Show modal with address update
                        $.fancybox.open({
                            src: '#js-modal-change-zip-code-address',
                        });
                    }
                },
            })
        });

        // Edit zip code on button click
        $(document).on('click', '.zip-code-modal-edit', function () {
            // Close current
            $('#js-modal-change-zip-code-address .fancybox-close-small').trigger('click');

            // Open zip modal
            $.fancybox.open({
                src: '#js-modal-zip-code',
            });
        });

        // Save delivery data to subscription (from modal window)
        $(document).on('submit', '.update-delivery-address-modal', function (e) {
            e.preventDefault()
            var data = {
                action: 'zipcode_update_delivery',
                ajax_nonce: main.ajax_nonce,
                form: $('.update-delivery-address-modal').serialize(),
            }

            $.ajax({
                type: 'POST',
                url: main.ajaxurl,
                data: data,
                beforeSend: function () {
                    $('.modal-save-address').attr('disabled', true)
                },
                complete: function () {
                    $('.modal-save-address').removeAttr('disabled')
                },
                success: function (response) {
                    if (response.success) {
                        // Update modal data
                        $('.new-address').text(response.data.shipping_address);
                        $('.new-billing-address').text(response.data.billing_address);

                        if (response.data.update_result > 0) {
                            $('.removed-items').text(response.data.update_result + ((response.data.update_result > 1) ? ' items' : ' item'));
                            $('#js-modal-change-zip-code-' + response.data.show + ' .message--warning').show();
                        } else {
                            $('#js-modal-change-zip-code-' + response.data.show + ' .message--warning').hide();
                        }

                        // Close current
                        $('#js-modal-change-zip-code-address .fancybox-close-small').trigger('click');

                        // Open updated modal
                        $.fancybox.open({
                            src: '#js-modal-change-zip-code-' + response.data.show,
                        });

                        // Redirect user on modal close
                        $('#js-modal-change-zip-code-' + response.data.show + ' .fancybox-close-small, .fancybox-slide--current').on('click', function () {
                            document.location.reload()
                        });
                    } else {
                        // Save zip to billing form
                        $('#delivery_zip').val(response.data.zip);
                        $('#delivery_address').val(response.data.shipping_address);
                        $('#removed_count').val(response.data.update_result)

                        // Close current
                        $('#js-modal-change-zip-code-address .fancybox-close-small').trigger('click');

                        // Open updated modal
                        $.fancybox.open({
                            src: '#js-modal-change-zip-code-' + response.data.show,
                        });
                    }
                },
            })
        });

        // Save billing data to subscription (from modal window)
        $(document).on('submit', '.update-billing-address-modal', function (e) {
            e.preventDefault()
            var data = {
                action: 'zipcode_update_billing',
                ajax_nonce: main.ajax_nonce,
                form: $('.update-billing-address-modal').serialize(),
            }

            $.ajax({
                type: 'POST',
                url: main.ajaxurl,
                data: data,
                beforeSend: function () {
                    $('.modal-save-address').attr('disabled', true)
                },
                complete: function () {
                    $('.modal-save-address').removeAttr('disabled')
                },
                success: function (response) {
                    if (response.success) {
                        // Update modal data
                        // $('.modal-billing-name').text(response.data.first_name + ' ' + response.data.last_name);
                        // $('.modal-billing-addres').text(response.data.address_1 + ' ' + response.data.city + ', ' + response.data.state + ' ' + response.data.zip_code);
                        // $('.modal-billing-country').text(response.data.country);
                        // Update modal data
                        $('.new-address').text(response.data.shipping_address);
                        $('.new-billing-address').text(response.data.billing_address);

                        if (response.data.update_result > 0) {
                            $('.removed-items').text(response.data.update_result + ((response.data.update_result > 1) ? ' items' : ' item'));
                            $('#js-modal-change-zip-code-' + response.data.show + ' .message--warning').show();
                        } else {
                            $('#js-modal-change-zip-code-' + response.data.show + ' .message--warning').hide();
                        }

                        // Close current
                        $('#js-modal-change-zip-code-billing-address .fancybox-close-small').trigger('click');

                        // Open updated modal
                        $.fancybox.open({
                            src: '#js-modal-change-zip-code-' + response.data.show,
                        });

                        //Redirect user on modal close
                        $('#js-modal-change-zip-code-' + response.data.show + ' .fancybox-close-small, .fancybox-slide--current').on('click', function () {
                            document.location.reload()
                        });
                    }
                },
            })
        });

        // Show/hide billing details in change address modal (DO NOT DELETE)
        // $('#use-this-as-my-billing-address').on('click touchend', function(){
        //   if ($('#use-this-as-my-billing-address').is(':checked')) {
        //     $('.modal-billing-address-container').hide();
        //   }else {
        //     $('.modal-billing-address-container').show();
        //   }
        // });

        // Open billing modal window
        // $('.open-modal-billing').on('click', function(){
        //   // Close current modal
        //   $('#js-modal-change-zip-code-address .fancybox-close-small').trigger('click');
        // });

        $('.select-option-onload option').each(function () {
            if ($(this).val() == $(this).parent().attr('data-value')) {
                $(this).attr('selected', 'selected');
                $(this).parent().addClass('field-box__field--entered');
            }
        });

        // Disable click on disabled select elements
        $('.select-disabled').on('mousedown', function (e) {
            e.preventDefault();
            this.blur();
            window.focus();
        });

        $(document.body).on('updated_checkout', function () {
            // trigger init modal event vanila
            var event = new Event('build');
            document.dispatchEvent(event);
        });


        $('.checkout__body').on('click', '.add-promo-code__trigger', function () {
            $('form.checkout_coupon').show();
        });

        $(document.body).on('applied_coupon_in_checkout', function () {
            if ($('.modal-promo-code .woocommerce-error').length > 0) {
                let err_message = $('.modal-promo-code .woocommerce-error');
                $('#error_coupon').html(err_message);
                $('.message--error').show();
                $('.woocommerce-error').css({"list-style": "none", "padding": "0"});
            } else {
                $('.message--error').hide();
                $('#js-promo-code .modal-common__close').trigger('click');
            }

        });


        if ($('.js-head-shop-list__counter--exist').length > 0) {
            $('.cart-totals__button').removeAttr('disabled');
        }


        // function update_customize_div(html_str, modal) {
        //  var $html = $.parseHTML(html_str);
        //  var $new_form = $('.option-list-' + modal, $html);
        //
        //  $('#meals-variation-group-' + modal + ' .option-list').replaceWith($new_form);
        //
        //  // $( document.body ).trigger('after_update_html_customize');
        // }

        /**
         * Customize block
         */
        $('.js-toggle-switch').on('change', function (evt) {

            let survey = false;

            if (evt.target.checked) {
                survey = true;
            }

            let formSelector = $(this).parents('.modal-builder__form').data('form');
            let productId = $(this).parents('.modal-builder__form').data('product');
            let variationId = $(this).parents('.modal-builder__form').data('variation');

            var dataInfo = {
                action: 'filter_customize_block',
                ajax_nonce: main.ajax_nonce,
                survey_status: survey,
                modal: formSelector,
                product: productId,
                variation: variationId
            }

            $.ajax({
                type: 'POST',
                url: main.ajaxurl,
                data: dataInfo,
                beforeSend: function () {
                },
                complete: function () {
                },
                success: function (response) {

                    if (response.success) {
                        if (evt.target.classList.contains('js-catalog-toggle-switch')) {
                            const getParams = '?use_survey=on';
                            let currentUrl = window.location.origin + window.location.pathname;
                            if (survey) {
                                currentUrl += getParams;
                            }

                            window.location.href = currentUrl;
                        }

                        $(document.body).trigger('finish_update_customize');
                    }

                },
            });

            // var data = {
            //  action: 'filter_customize_block',
            //  ajax_nonce: main.ajax_nonce,
            //  modal: formSelector
            // }
            //
            //  localStorage.setItem('survey_default', survey);
            //
            //  let product_link = window.location.href.split('?');
            //
            //  $.ajax({
            //    type: 'GET',
            //    url: product_link[0] + '?use_survey=' + survey,
            //    data: data,
            //    dataType: 'html',
            //    beforeSend: function () {
            //    },
            //    complete: function () {
            //    },
            //    success: function (response) {
            //      //update_customize_div(response, data.modal);
            //      $(document.body).trigger('finish_update_customize');
            //    },
            //  })

        });

        // $('.js-catalog-toggle-switch').on()

        $(document.body).on('finish_update_customize', function () {
            const event = new Event('init_ajax_rating');
            document.dispatchEvent(event);
        });


        $(document.body).on('finish_update_customize', function () {
            // change links in menu
            let allLinks = $('a');
            let allProductLinks = $('[data-link]');
            let surveyState = localStorage.getItem('survey_default');

            if (surveyState === 'true') {
                addGetParams(allLinks);
                addGetParamsProducts(allProductLinks);
                addGetParamToCategory();
            } else {
                removeGetParams(allLinks);
                removeGetParamsProducts(allProductLinks);
                removeGetParamToCategory();
            }

        });

        const addGetParamToCategory = function () {
            let hiddenInput = $('[name="category_redirect"]');
            let hiddenInputValue = hiddenInput.val();

            if (hiddenInputValue.indexOf('product-category/meals') !== -1) {
                hiddenInput.val(hiddenInputValue + '?use_survey=on');
            }
        }

        const removeGetParamToCategory = function () {
            let hiddenInput = $('[name="category_redirect"]');
            let hiddenInputValue = hiddenInput.val();

            if (hiddenInputValue.indexOf('product-category/meals') !== -1) {
                let link = hiddenInputValue.split('?');

                if (hiddenInputValue.length > 1) {
                    hiddenInput.val(link[0]);
                }
            }
        }

        const removeGetParams = function (allLinks) {
            allLinks.each(function (index, item) {
                if (item.href.indexOf('product-category/meals') !== -1) {
                    let link = item.href.split('?');

                    if (link.length > 1) {
                        item.href = link[0]
                    }
                }
            });
        };

        const removeGetParamsProducts = function (allProductLinks) {
            allProductLinks.each(function (index, item) {
                let itemValue = $(item).data('link');
                let link = itemValue.split('?');
                $(item).attr("data-link", link[0]);
            });
        };

        const addGetParams = function (allLinks) {
            allLinks.each(function (index, item) {
                if (item.href.indexOf('product-category/meals') !== -1) {
                    item.href = item.href + '?use_survey=on'
                }
            });
        };

        const addGetParamsProducts = function (allProductLinks) {
            allProductLinks.each(function (index, item) {
                let itemValue = $(item).data('link');
                let link = itemValue + '?use_survey=true';
                $(item).attr("data-link", link);
            });
        };

        const getCookie = function (cname) {
            const name = cname + '=';
            const decodedCookie = decodeURIComponent(document.cookie);
            const ca = decodedCookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return '';
        }

        const setCookie = function (cname, cvalue, exdays) {
            const d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            const expires = "expires=" + d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        }

        if (!$('.logged-in').length) {

            if ($('.page-template-page-offerings').length || $('.home').length) {
                const cookieBanner = $('.cookies');
                const cookieBannerCookieName = 'hide_cookie_banner';
                $('body').on('click', '#cookie-submit', function () {
                    setCookie(cookieBannerCookieName, 'true', 30);
                });
                $('body').on('click', '.cookies__close', function () {
                    setCookie(cookieBannerCookieName, 'true', 30);
                });
                if (getCookie(cookieBannerCookieName) === '') {
                    setTimeout(() => {
                        cookieBanner.show();
                    }, 10000);
                }
            }
        }

        const groceryMenuItem = $('#menu-hamburger-sub-menu .grocery-anchor a');
        if (groceryMenuItem.length) {
            const hrefAttr = groceryMenuItem.attr('href');
            if (hrefAttr === '#js-modal-zip-code') {
                groceryMenuItem.attr('data-redirect', groceryMenuItem.attr('data-redirect') + '#subcategories-anchor');
            } else {
                groceryMenuItem.attr('href', hrefAttr + '#subcategories-anchor');
            }
        }

        $('#chat-now').on('click', function (e) {
            e.preventDefault();
            $('body>.intercom-lightweight-app>.intercom-lightweight-app-launcher').click();
        });

        const hideUserOfferBannerCookieName = 'user_offer_banner';
        if ($('.page-template-page-groceries').length) {
            const sectionSurveyExists = $('#survey-exists');
            const sectionSurveyNotExists = $('#survey-not-exists');
            const whileButton = $('.user-offer__lead button');
            whileButton.on('click', function (e) {
                e.preventDefault();
                setCookie(hideUserOfferBannerCookieName, 'true', 7);
                sectionSurveyNotExists.hide();
                sectionSurveyExists.show();
                $('main').removeClass('catalog-main--padding-top--no');
            });
        }

        // show/hide instruction block on vitamin page
        if ($('main.product_cat-vitamins').length) {
            $(document).on('click', '.instruction__head', function () {
                let instruction = $(this).closest('.instruction');
                instruction.toggleClass('instruction--open');
                instruction.find('.instruction__body').slideToggle(300);
            });
        }

        // disable zip modal for thank you page
        if ($('body.woocommerce-order-received').length) {
            const zipField = $('.sf_show_zip_picker');
            zipField.on('click', function (e) {
                e.preventDefault();
            });

        }

        /**
         * Coupon Implement
         */

        $('.js-discount-button').on('click', function () {
            setCookie('discount_promo_bar', 'true', 30);
        });


        $(document).on('submit', '.ajax_filter_products', function (evt) {
            evt.preventDefault(); // uncomment when will be ajax

            // Show loader
            activateLoader('catalog', '.catalog-main');

            let $form = $(this),
                regName = /.*\[\]$/,
                termData = {}
            $form.serializeArray().forEach(function (item) {
                if (regName.test(item.name)) {
                    let tempName = item.name.replace(/([\[\]]*)$/g, '')
                    if (!termData[tempName]) termData[tempName] = []
                    termData[tempName].push(item.value)
                }
            })

            let searchParams = new URLSearchParams(window.location.search);

            let closeBtn = $form.find('.control-button--close');

            const dataInfo = {
                action: 'set_chose_filters',
                ajax_nonce: main.ajax_nonce,
                filters: termData,
                questionId: $form.find("[name='question_id']").val(),
                order: searchParams.get('orderby'),
                isSearchPage: typeof backendParams !== 'undefined'
            }

            $.ajax({
                type: 'POST',
                url: main.ajaxurl,
                data: dataInfo,
                dataType: 'json',
                beforeSend: function () {
                },
                complete: function () {
                    closeBtn.trigger('click');
                },
                success: function (response) {
                    if (response.success) {
                        // Hide loader
                        disactivateLoader('catalog', '.catalog-main');

                        $('.footer-products-filter__count > span').text(response.data.filteredTotal);
                        $('.products__page-info > span').text(response.data.filteredTotal);
                        $('.product-list').html(response.data.html);
                    }
                },
            });
        })

        const url = document.createElement('a');
        url.href = document.location.href;
        if (url.search.indexOf('from-email-template') !== -1 && backendCommonParams.is_user_logged === '0') {
            setTimeout(function () {
                $.fancybox.open({
                    src: '#js-modal-sign-up',
                    opts: {
                        afterClose: function () {
                            window.location.href = backendCommonParams.site_url + '/offerings'
                        }
                    }
                })
            }, 200)
        }

        // Count checked filters
        let answersVariantsContainer = $('.body-products-filter__checkbox-list');

        const changeCounterFilters = function () {
            let counterFilters = $('.filter-list__button .filter-list__button-counter');
            let $checked = answersVariantsContainer.find('input:checked').length;
            if (counterFilters.length < 1) {
                let btn = document.querySelector('.filter-list__button');
                btn.insertAdjacentHTML('beforeend', `<span class = "filter-list__button-counter filter-list__button-counter--recommended" >${$checked}</span>`);
            } else {
                counterFilters.text($checked);
            }
        }

        answersVariantsContainer.on('click', '.checkbox-item__field', changeCounterFilters);

        $(document).on('click', '.products-filter__clear', function (e) {
            e.preventDefault();
            let $form = $(this);

            let $filterForm = $form.parents(".products-filter");
            $filterForm.find('input:checked:not(:disabled)').prop('checked', false);
            $filterForm.find("select").val("");

            changeCounterFilters();

            const dataInfo = {
                action: 'clear_selected_filters',
                ajax_nonce: main.ajax_nonce,
                questionId: $form.find("[name='question_id']").val(),
                isSearchPage: typeof backendParams !== 'undefined'
            }

            $.ajax({
                type: 'POST',
                url: main.ajaxurl,
                data: dataInfo,
                dataType: 'json',
                beforeSend: function () {
                },
                complete: function () {
                },
                success: function (response) {
                    if (response.success) {
                        $('.products__page-info > span').text(response.data.filteredTotal);
                        $('.footer-products-filter__count > span').text(response.data.filteredTotal);
                        $('.product-list').html(response.data.html);
                    }
                },
            });


        });

        const hideOfferBannerCookieName = 'hide_offer_banner';
        $(document).on('click', '.promo-bar__close', function () {
            setCookie(hideOfferBannerCookieName, 'true', 30);
        });


        /**
         *  Loaders zone
         */

        let key;
        if ($('.term-meals').length) {
            $(window).on('keydown', function (e) {
                key = e.keyCode;
            })
        }

        // Hide loader in catalog, after page loaded
        if ($('.catalog-main').length) {
            $(window).load(function () {
                disactivateLoader('catalog', '.catalog-main');
            });
        }
        // Show loader during sorting
        $('#products-filter-sort-by').on('change', function () {
            activateLoader('catalog', '.catalog-main');
        });
        // Hide loader on single product, after page loaded
        if ($('.product-main').length) {
            $(window).load(function () {
                disactivateLoader('catalog', '.product-main');
            });
        }
        // Show loader on single product add to cart
        $('.form-add-to-cart__button').on('click', function () {
            activateLoader('catalog', '.product-main');
        });

        $('#js-modal-remove-item button').on('click', function (e) {
            if ( $( 'body.single-product' ).length ) {
                disactivateLoader( 'catalog', '.product-main' );
                $( '.js-nice-number' ).val(1)
            }
        });

        // Show loader on single product click
        $(document).on('click', '.product-item__img-link, .product-item__name-link', function () {
            if (key !== 17 && key !== 91) {
                activateLoader('catalog', '.catalog-main');
            }
        });
        // Show loader on "recomended" switch
        $('#products-filter-disable-survey').on('change', function () {
            activateLoader('catalog', '.catalog-main');
        });
        // Show loader on coupon remove (cart page)
        $('.woocommerce-cart .woocommerce-remove-coupon').on('click', function () {
            activateLoader('main', 'body');
        });
        // Show loader on quantity update (cart page)
        $('.woocommerce-cart .nice-number__field').on('change', function () {
            activateLoader('main', 'body');
        });
        $('.woocommerce-cart .nice-number button').on('click', function () {
            activateLoader('main', 'body');
        });

        // Tutorials actions
        // Skip handler
        $( document ).on( 'click', '.introjs-skipbutton, .introjs-donebutton', function() {

          let tutoria_type = ''
          switch( true ) {
            case $( 'body' ).hasClass( 'tutorial-catalog-survey' ): // Survey case
              tutoria_type = 'survey'
            break

            // case $( 'body' ).hasClass( tutoria_class ):
            // break

            // case $( 'body' ).hasClass( tutoria_class ):
            // break

            default:
              tutoria_type = 'main'
            break
          }

          let action_state = ( $( this ).hasClass( 'introjs-skipbutton' ) ) ? 'skip' : 'finish'

          $.ajax( {
            type: 'POST',
            url: main.ajaxurl,
            data: {
              action: 'save_tutorial_status',
              state: action_state,
              type: tutoria_type,
              page_name: $( 'body' ).attr( 'data-tutorial-page' )
            },
            dataType: 'json',
            success: function (response) {}
          } )
        } )

        // Remove tutorial status for current page
        $( document ).on( 'click', '.remove-tutorial-status', function() {
          let tutoria_type = ''
          switch( true ) {
            case $( 'body' ).attr( 'data-tutorial-page' ) == 'catalog': // Survey case
              tutoria_type = 'survey'
            break

            // case $( 'body' ).hasClass( tutoria_class ):
            // break

            // case $( 'body' ).hasClass( tutoria_class ):
            // break

            default:
              tutoria_type = 'main'
            break
          }

          $.ajax( {
            type: 'POST',
            url: main.ajaxurl,
            data: {
              action: 'delete_tutorial_status',
              type: tutoria_type,
              page_name: $( 'body' ).attr( 'data-tutorial-page' )
            },
            dataType: 'json',
            success: function (response) {}
          } )
        } )

        // Got it (pass) handler
        // $( document ).on( 'click', '.introjs-nextbutton', function() {
        //   if ( $( this ).hasClass( 'introjs-donebutton' ) ) {
        //     $.ajax( {
        //       type: 'POST',
        //       url: main.ajaxurl,
        //       data: {
        //         action: 'save_tutorial_status',
        //         type: 'finish'
        //       },
        //       dataType: 'json',
        //       success: function (response) {}
        //     } )
        //   }
        // } )

    }) // Document ready end

})(jQuery)

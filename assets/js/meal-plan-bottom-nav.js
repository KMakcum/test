jQuery(document).ready(function ($) {
    $.blockUI.defaults.message = null

    let intervalID = 0
    let startYourPlanButton = $('.head-meal-plan__btn-start-plan.button.button--small')
    let exploreGroceriesLink = $('.head-meal-plan__mobile-explore-staples.link-2')

    function ajax_add_to_cart(post_id, quantity) {
        let formData = new FormData()
        formData.append('action', 'add_to_cart')
        formData.append('post_id', post_id)
        formData.append('quantity', quantity)
        $.ajax({
            url: ajaxSettingsMealPlan.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.fragments) {
                    jQuery.each(response.fragments, function (key, value) {
                        jQuery(key).replaceWith(value)
                    })
                }
                jQuery('body').trigger('wc_fragments_refreshed')
                // $.unblockUI()
            },
            error: function () {
                // $.unblockUI()
            },
            beforeSend: function () {
                // $.blockUI()
            }
        })
    }

    function ajax_remove_from_cart(post_id, quantity) {
        let formData = new FormData()
        formData.append('action', 'remove_from_cart')
        formData.append('post_id', post_id)
        formData.append('quantity', quantity)
        $.ajax({
            url: ajaxSettingsMealPlan.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.fragments) {
                    jQuery.each(response.fragments, function (key, value) {
                        jQuery(key).replaceWith(value)
                    })
                }
                jQuery('body').trigger('wc_fragments_refreshed')
                // $.unblockUI()
            },
            error: function () {
                // $.unblockUI()
            },
            beforeSend: function () {
                // $.blockUI()
            }
        })
    }

    function ajax_remove_from_cart_totally(post_id, li) {
        let formData = new FormData()
        formData.append('action', 'remove_from_cart_totally')
        formData.append('post_id', post_id)
        $.ajax({
            url: ajaxSettingsMealPlan.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                li.remove()
                countFilledMeals--
                mealsLeft++
                $('.meals-count-selected').click()
                if (response.fragments) {
                    jQuery.each(response.fragments, function (key, value) {
                        jQuery(key).replaceWith(value)
                    })
                }
                jQuery('body').trigger('wc_fragments_refreshed')
                // $.unblockUI()
            },
            error: function () {
                // $.unblockUI()
            },
            beforeSend: function () {
                // $.blockUI()
            }
        })
    }


    function decrementTreatment() {
        $('.js-nice-number').siblings().each(function (index) {
            if ($(this).text() === '–') {
                $(this).off('click')
                $(this).on('click', function () {
                    if ($(this).siblings('.js-nice-number').val() !== '0') {
                        if ($(this).attr('data-before-remove') === 'true') {
                            $(this).siblings('.js-nice-number').val(0)
                            const postId = $(this).parents('.meal-plan-list__item').attr('data-product_id')
                            const li = $(this).parents('.meal-plan-list__item')
                            clearInterval(intervalID)
                            ajax_remove_from_cart_totally(postId, li)
                            placeHoldersDrawer(countMealsPlaceholders)
                            mealPlanCounter.text('' + countFilledMeals + '/' + mealsTotalCount)
                            ratingCounter.attr('data-rate-value', countFilledMeals)
                            ratingCounter.attr('data-rateit-max', mealsTotalCount)
                            $('.meals-count-selected').click()
                            return false
                        }
                        $(this).attr('data-before-remove', parseInt($(this).siblings('.js-nice-number').val()) === parseInt('1'))
                        const price = $(this).closest('.meal-plan-list__item').find('.meal-plan-item__price').attr('data-price')
                        const priceCurrent = $(this).closest('.meal-plan-list__item').find('.meal-plan-item__price').text().replace(/[^0-9.]/g, '')
                        countFilledMeals--
                        mealsLeft++
                        placeHoldersDrawer(countMealsPlaceholders)
                        mealPlanCounter.text('' + countFilledMeals + '/' + mealsTotalCount)
                        ratingCounter.attr('data-rate-value', countFilledMeals)
                        ratingCounter.attr('data-rateit-max', mealsTotalCount)
                        $(this).closest('.meal-plan-list__item.meal-plan-item').find('.meal-plan-item__price').text('$' + (parseFloat(priceCurrent) - (parseFloat(price))).toFixed(2))
                        $('.meals-count-selected').click()
                        const post_id = $(this).closest('.meal-plan-list__item').attr('data-product_id')
                        const quantity = $(this).siblings('.js-nice-number').val()
                        if (intervalID) {
                            clearInterval(intervalID)
                            intervalID = setTimeout(function () {
                                ajax_remove_from_cart(post_id, quantity)
                            }, 450)

                        } else {
                            intervalID = setTimeout(function () {
                                ajax_remove_from_cart(post_id, quantity)
                            }, 450)
                        }
                    }
                })
            }
        })
    }

    function incrementTreatment() {
        $('.js-nice-number').siblings().each(function (index) {
            if ($(this).text() === '+') {
                $(this).off('click')
                $(this).on('click', function () {
                    if (parseInt(countFilledMeals) === 14) {
                        $('.btn-modal[href="#js-modal-remove-item"]').click()
                        $(this).siblings('.js-nice-number').val($(this).siblings('.js-nice-number').val() - 1)
                        return false
                    }
                    if (mealsLeft === 0 && parseInt(countFilledMeals) === 14) {
                        $('.btn-modal[href="#js-modal-remove-item"]').click()
                        $(this).siblings('.js-nice-number').val($(this).siblings('.js-nice-number').val() - 1)
                        return false
                    }
                    $(this).siblings('button').attr('data-before-remove', parseInt($(this).siblings('.js-nice-number').val()) === parseInt('1'))
                    if (mealsLeft === 0 && parseInt(countFilledMeals) === 6
                        && (!$('.plan-cards__item[data-meal-count="10"]').hasClass('meals-count-selected')
                            || $('.plan-cards__item[data-meal-count="14"]').hasClass('meals-count-selected'))) {
                        $('.plan-cards__item[data-meal-count="10"]').click()
                        $('.plan-cards__item[data-meal-count="10"]').find('.plan-card__box').click()

                        if (countFilledMeals >= 10 && $('.plan-cards__item[data-meal-count="14"]').hasClass('meals-count-selected')) {
                            $('.meals-left').text(mealsLeft + ' more')
                        } else {
                            $('.meals-left').text('at least ' + mealsLeft + ' more')
                        }
                    }
                    if (mealsLeft === 0 && parseInt(countFilledMeals) === 10
                        && !$('.plan-cards__item[data-meal-count="14"]').hasClass('meals-count-selected')) {
                        $('.plan-cards__item[data-meal-count="14"]').click()
                        $('.plan-cards__item[data-meal-count="14"]').find('.plan-card__box').click()

                        if (countFilledMeals >= 10 && $('.plan-cards__item[data-meal-count="14"]').hasClass('meals-count-selected')) {
                            $('.meals-left').text(mealsLeft + ' more')
                        } else {
                            $('.meals-left').text('at least ' + mealsLeft + ' more')
                        }

                    }
                    const price = $(this).closest('.meal-plan-list__item').find('.meal-plan-item__price').attr('data-price')
                    const priceCurrent = $(this).closest('.meal-plan-list__item').find('.meal-plan-item__price').text().replace(/[^0-9.]/g, '')
                    countFilledMeals++
                    mealsLeft--
                    placeHoldersDrawer(countMealsPlaceholders)
                    mealPlanCounter.text('' + countFilledMeals + '/' + mealsTotalCount)
                    $(this).closest('.meal-plan-list__item.meal-plan-item').find('.meal-plan-item__price').text('$' + (parseFloat(priceCurrent) + (parseFloat(price))).toFixed(2))
                    const post_id = $(this).closest('.meal-plan-list__item').attr('data-product_id')
                    const quantity = $(this).siblings('.js-nice-number').val()
                    $('.meals-count-selected').click()
                    if (intervalID) {
                        clearInterval(intervalID)
                        intervalID = setTimeout(function () {
                            ajax_add_to_cart(post_id, quantity)
                        }, 450)
                    } else {
                        intervalID = setTimeout(function () {
                            ajax_add_to_cart(post_id, quantity)
                        }, 450)
                    }
                })
            }
        })
    }

    // setTimeout($.unblockUI, 4500)
    const mealPlanList = $('.body-meal-plan__list')
    let mealsCount = mealPlanList.find($('.item-filled'))
    const mealPlanItem = $('.plan-cards__item')
    const mealsLeftText = $('.meals-left')
    let countFilledMeals = 0
    const mealPlanCounter = $('.meal-plan-counter--medium-filled')
    const placeholder = '   <li class="meal-plan-list__item meal-plan-item meal-plan-item--empty">\n' +
        '                            <figure class="meal-plan-item__img-box">\n' +
        '                                <img class="meal-plan-item__img" src="' + ajaxSettingsMealPlan.stylesheet_dir + '/assets/img/base/placeholder.svg" alt="">\n' +
        '                            </figure>\n' +
        '                            <div class="meal-plan-item__center">\n' +
        '                                <p class="meal-plan-item__title"><a style="text-decoration:none;color: #e1e3e6;" href="' + ajaxSettingsMealPlan.site_url + '/product-category/meals/">Add Your Meal</a></p>\n' +
        '                            </div>\n' +
        '                        </li>'
    const placeHoldersDrawer = (count) => {
        for (let i = 1; i <= count; i++) {
            mealPlanList.append(placeholder)
        }
    }
    const ratingCounter = $('.head-meal-plan__progress.rating-progress')
    $('.plan-card__field:checked').parents('.plan-cards__item').addClass('meals-count-selected')

    $('.head-meal-plan__mobile-switch').on('click', function (e) {
        const nextMealPlan = $(this).attr('data-next-meal')
        $('.plan-cards__item[data-meal-count=' + nextMealPlan + ']').parent('click')
        $('.plan-cards__item[data-meal-count=' + nextMealPlan + ']').children('.plan-card__label').trigger('click')
        $('.meals-count-selected').trigger('click')
    });

    mealPlanItem.on('click', function (e) {
        let prevSelected = $('.meals-count-selected')
        mealPlanItem.each(function () {
            $(this).removeClass('meals-count-selected')
        })
        $(this).addClass('meals-count-selected')
        mealsTotalCount = $('.meals-count-selected').attr('data-meal-count')
        if (parseInt(countFilledMeals) > parseInt(mealsTotalCount)) {
            $('.message.message--full.message--warning').find('.meals-count-span').text(parseInt(mealsTotalCount))
            $('.message.message--full.message--warning').fadeIn()
            $(this).removeClass('meals-count-selected')
            prevSelected.addClass('meals-count-selected')
            mealsTotalCount = $('.meals-count-selected').attr('data-meal-count')
            return false
        }
        switch ($('.meals-count-selected').data('meal-count')) {
            case  6:
                $('.head-meal-plan__mobile-switch').removeAttr('style')
                $('.meals-plan-next').text(10)
                $('.head-meal-plan__mobile-switch').attr('data-next-meal', '10')
                break;
            case 10:
                $('.head-meal-plan__mobile-switch').removeAttr('style')
                $('.meals-plan-next').text(14)
                $('.head-meal-plan__mobile-switch').attr('data-next-meal', '14')
                break;
            case 14:
                $('.head-meal-plan__mobile-switch').css({'display': 'none'})
                break;
        }

        if (parseInt(countFilledMeals) === parseInt(mealsTotalCount)) {
            if (parseInt(countFilledMeals) === 6) {
                $('.head-meal-plan__txt').replaceWith(
                    '<p class="head-meal-plan__txt">Switch to ' +
                    '<a class="link meal-plan-modal-switcher" data-next-meal="10" href="">10 meals plan</a>' +
                    '</p>'
                );
                $('.meal-plan-modal-switcher').off('click')
                $('.meal-plan-modal-switcher').on('click', function (e) {
                    e.preventDefault();
                    const nextMealPlan = $(this).attr('data-next-meal')
                    $('.plan-cards__item[data-meal-count=' + nextMealPlan + ']').parent('click')
                    $('.plan-cards__item[data-meal-count=' + nextMealPlan + ']').children('.plan-card__label').trigger('click')
                    $('.meals-count-selected').trigger('click')
                })
                $('.head-meal-plan__mobile-switch').css({'display': 'none'})
            }
            if (parseInt(countFilledMeals) === 10) {
                $('.head-meal-plan__txt').replaceWith(
                    '<p class="head-meal-plan__txt">Switch to ' +
                    '<a class="link meal-plan-modal-switcher" data-next-meal="14" href="">14 meals plan</a>' +
                    '</p>'
                );
                $('.meal-plan-modal-switcher').off('click')
                $('.meal-plan-modal-switcher').on('click', function (e) {
                    e.preventDefault();
                    const nextMealPlan = $(this).attr('data-next-meal')
                    $('.plan-cards__item[data-meal-count=' + nextMealPlan + ']').parent('click')
                    $('.plan-cards__item[data-meal-count=' + nextMealPlan + ']').children('.plan-card__label').trigger('click')
                    $('.meals-count-selected').trigger('click')
                })
                $('.head-meal-plan__mobile-switch').css({'display': 'none'})
            }
            if (parseInt(countFilledMeals) === 14) {
                $('.head-meal-plan__txt').replaceWith(
                    '<p class="head-meal-plan__txt">Don`t forget to' +
                    '<a style="color: #252728" href="' + ajaxSettingsMealPlan.site_url + '/groceries"> add groceries</a>' +
                    ' to your weekly plan' +
                    '</p>'
                )
            }
            mealPlanCounter.removeClass('meal-plan-counter--medium-filled')
            mealPlanCounter.addClass('meal-plan-counter--filled')
            startYourPlanButton.attr('style', 'display:inline-block;');
            exploreGroceriesLink.attr('style', 'display:none; text-align: center;');
            if(window.matchMedia('(max-width: 992px)').matches) {
                exploreGroceriesLink.attr('style', 'display:block; text-align: center;');
            }
            $('.meals-filled').text(countFilledMeals)
            $('.meals-plan-count').text(mealsTotalCount)
        } else {
            $('.head-meal-plan__txt').replaceWith(
                '<p class="head-meal-plan__txt">Add <span class="meals-left"></span> meals to your weekly plan</p>'
            )
            if (countFilledMeals >= 10 && $('.plan-cards__item[data-meal-count="14"]').hasClass('meals-count-selected')) {
                $('.meals-left').text(mealsLeft + ' more')
                $('.meals-filled').text(countFilledMeals)
                $('.meals-plan-count').text(mealsTotalCount)
            } else {
                $('.meals-left').text('at least ' + mealsLeft + ' more')
                $('.meals-filled').text(countFilledMeals)
                $('.meals-plan-count').text(mealsTotalCount)
            }
            mealPlanCounter.removeClass('meal-plan-counter--filled')
            mealPlanCounter.addClass('meal-plan-counter--medium-filled')
            startYourPlanButton.attr('style', 'display:none;');
            exploreGroceriesLink.attr('style', 'display:none; text-align: center;');
            if(window.matchMedia('(max-width: 992px)').matches) {
                exploreGroceriesLink.attr('style', 'display:none; text-align: center;');
            }
        }

        if (parseInt(countFilledMeals) === 0) {
            $('.head-meal-plan__txt').replaceWith(
                '<p class="head-meal-plan__txt">Add <span class="meals-left"></span> meals to your weekly plan</p>'
            )
            if (countFilledMeals >= 10 && $('.plan-cards__item[data-meal-count="14"]').hasClass('meals-count-selected')) {
                $('.meals-left').text(mealsLeft + ' more')
                $('.meals-left-mobile').text('')
            } else {
                $('.meals-left').text('at least ' + mealsLeft + ' more')
                $('.meals-left-mobile').text('')
            }
            mealPlanCounter.removeClass('meal-plan-counter--filled')
            mealPlanCounter.removeClass('meal-plan-counter--medium-filled')
            mealPlanCounter.addClass('meal-plan-counter--empty')
            startYourPlanButton.attr('style', 'display:none;');
            exploreGroceriesLink.attr('style', 'display:none; text-align: center;');
            if(window.matchMedia('(max-width: 992px)').matches) {
                exploreGroceriesLink.attr('style', 'display:none; text-align: center;');
            }
        }

        $('.message.message--full.message--warning').fadeOut()
        countMealsPlaceholders = $(this).attr('data-meal-count') - countFilledMeals
        $('.meal-plan-list__item.meal-plan-item.meal-plan-item--empty').remove()
        mealsLeftText.text('at least ' + countMealsPlaceholders + ' more')
        placeHoldersDrawer(countMealsPlaceholders)
        mealPlanCounter.text('' + countFilledMeals + '/' + mealsTotalCount)
        ratingCounter.attr('data-rate-value', countFilledMeals)
        ratingCounter.attr('data-rateit-max', mealsTotalCount)
        mealsLeft = mealsTotalCount - countFilledMeals
        $('.steps-checker').replaceWith(function (e) {
            let content = '<div class="steps-checker" >'
            for (let i = 0; i < countFilledMeals; i++) {
                content += '<div class="steps-filled"></div>'
            }
            for (let i = 0; i < mealsLeft; i++) {
                content += '<div class="steps-not-filled"></div>'
            }
            content += '</div>'
            return content
        })
    })

    let countMealsPlaceholders = $('.meals-count-selected').attr('data-meal-count') - countFilledMeals
    placeHoldersDrawer(countMealsPlaceholders)
    let mealsTotalCount = $('.meals-count-selected').attr('data-meal-count')
    mealsLeftText.text('at least ' + mealsTotalCount + ' more')
    mealPlanCounter.text('' + countFilledMeals + '/' + mealsTotalCount)
    ratingCounter.attr('data-rate-value', countFilledMeals)
    ratingCounter.attr('data-rateit-max', mealsTotalCount)

    mealPlanList.find($('.js-nice-number')).each(function (e) {
        countFilledMeals += parseInt($(this).val())
    })
    let mealsLeft = mealsTotalCount - countFilledMeals
    $('.meals-count-selected').click()
    mealPlanList.find($('.js-nice-number')).niceNumber({
        autoSize: false,
        buttonDecrement: '–',
        buttonIncrement: '+',
        buttonPosition: 'around',
    })

    function dataAttr() {
        $('.js-nice-number').siblings().each(function (index) {
            if ($(this).text() === '–' && parseInt($(this).siblings('.js-nice-number').val()) === parseInt('1')) {
                $(this).attr('data-before-remove', true)
            }
        })
    }

    dataAttr()
    decrementTreatment()
    incrementTreatment()

    $('.product-list').on('click', 'a.ajax_add_to_cart', function (e) {

        if (mealsLeft === 0 && parseInt(countFilledMeals) === 14) {
            $('.btn-modal[href="#js-modal-remove-item"]').click()
            return false
        }

        if (mealsLeft === 0 && parseInt(countFilledMeals) === 6) {
            $('.plan-cards__item[data-meal-count="10"]').click()
            $('.plan-cards__item[data-meal-count="10"]').find('.plan-card__box').click()

        }
        if (mealsLeft === 0 && parseInt(countFilledMeals) === 10) {
            $('.plan-cards__item[data-meal-count="14"]').click()
            $('.plan-cards__item[data-meal-count="14"]').find('.plan-card__box').click()
        }

        let flag = false
        const prodId = $(this).attr('data-product_id')
        const price = $(this).siblings($('.product-item__price'))
            .find('.price-box__current bdi')
            .text()
            .replace(/\D/ig, function () {
                let dotCount = 0
                return function ($0) {
                    if ($0 === '.' && !dotCount) {
                        dotCount += 1
                        return $0
                    }
                    return ''
                }
            }())
        const prodTitle = $(this).parent('.product-item__actions')
            .siblings($('.product-item__name'))
            .find('.product-item__name-link')
            .text()

        const prodLink = $(this).parent('.product-item__actions')
            .parent($('.product-item__info'))
            .siblings($('.product-item__name-link'))
            .attr('href')

        const prodImageHref = $(this).parent('.product-item__actions')
            .parent($('.product-item__info'))
            .siblings('.product-item__img-link')
            .children($('picture'))
            .find('img')
            .attr('src')

        // $.blockUI()
        // setTimeout(function () {
        //     $.unblockUI()
        // }, 500)

        $('.meal-plan-list__item.item-filled.meal-plan-item').each(function () {
            if ($(this).attr('data-product_id') === prodId) {
                const val = parseInt($(this).find($('.nice-number__field.js-nice-number')).val()) + 1
                $(this).replaceWith('<li class="meal-plan-list__item item-filled meal-plan-item" data-product_id="' + prodId + '">\n' +
                    '                            <figure class="meal-plan-item__img-box">\n' +
                    '                                <a class="meal-plan-item__img-link" href="' + prodLink + '" target="_blank">\n' +
                    '                                    <picture>\n' +
                    '                                        <source srcset="' + prodImageHref + '" type="image/webp">\n' +
                    '                                        <img class="meal-plan-item__img" src="' + prodImageHref + '" alt="">\n' +
                    '                                    </picture>\n' +
                    '                                </a>\n' +
                    '                            </figure>\n' +
                    '                            <div class="meal-plan-item__center">\n' +
                    '                                <p class="meal-plan-item__title">\n' +
                    '                                    <a href="' + prodLink + '" target="_blank">' + prodTitle + '</a></p>\n' +
                    '                                <input class="nice-number__field js-nice-number" readonly type="number" value="' + val + '" min="1" name="quantity">\n' +
                    '                            </div>\n' +
                    '                            <div class="meal-plan-item__right">\n' +
                    '                                <p class="meal-plan-item__price" data-price="' + price + '">' + parseFloat(price * val).toFixed(2) + '$</p>\n' +
                    '                            </div>\n' +
                    '                        </li>')
                mealsCount = mealPlanList.find($('.item-filled'))
                countFilledMeals++
                $('.meals-count-selected').click()
                if (countFilledMeals >= 10 && $('.plan-cards__item[data-meal-count="14"]').hasClass('meals-count-selected')) {
                    $('.meals-left').text(mealsLeft + ' more')
                } else {
                    $('.meals-left').text('at least ' + mealsLeft + ' more')
                }
                mealPlanList.find($('.js-nice-number')).niceNumber({
                    autoSize: false,
                    buttonDecrement: '–',
                    buttonIncrement: '+',
                    buttonPosition: 'around',
                })
                dataAttr()
                decrementTreatment()
                incrementTreatment()
                flag = true
                return null
            }
        })

        if (flag) return null

        flag = false
        $('.meal-plan-list__item.meal-plan-item').each(function () {
            if ($(this).attr('data-product_id') === prodId) {
                const val = parseInt($(this).find($('.nice-number__field.js-nice-number')).val()) + 1
                $(this).replaceWith('<li class="meal-plan-list__item item-filled meal-plan-item" data-product_id="' + prodId + '">\n' +
                    '                            <figure class="meal-plan-item__img-box">\n' +
                    '                                <a class="meal-plan-item__img-link" href="' + prodLink + '" target="_blank">\n' +
                    '                                    <picture>\n' +
                    '                                        <source srcset="' + prodImageHref + '" type="image/webp">\n' +
                    '                                        <img class="meal-plan-item__img" src="' + prodImageHref + '" alt="">\n' +
                    '                                    </picture>\n' +
                    '                                </a>\n' +
                    '                            </figure>\n' +
                    '                            <div class="meal-plan-item__center">\n' +
                    '                                <p class="meal-plan-item__title">\n' +
                    '                                    <a href="' + prodLink + '" target="_blank">' + prodTitle + '</a></p>\n' +
                    '                                <input class="nice-number__field js-nice-number" readonly type="number" value="' + val + '" min="1" name="quantity">\n' +
                    '                            </div>\n' +
                    '                            <div class="meal-plan-item__right">\n' +
                    '                                <p class="meal-plan-item__price" data-price="' + price + '">$' + parseFloat(price * val).toFixed(2) + '</p>\n' +
                    '                            </div>\n' +
                    '                        </li>')
                mealsCount = mealPlanList.find($('.item-filled'))
                countFilledMeals++
                $('.meals-count-selected').click()
                if (countFilledMeals >= 10 && $('.plan-cards__item[data-meal-count="14"]').hasClass('meals-count-selected')) {
                    $('.meals-left').text(mealsLeft + ' more')
                } else {
                    $('.meals-left').text('at least ' + mealsLeft + ' more')
                }
                mealPlanList.find($('.js-nice-number')).niceNumber({
                    autoSize: false,
                    buttonDecrement: '–',
                    buttonIncrement: '+',
                    buttonPosition: 'around',
                })
                dataAttr()
                decrementTreatment()
                incrementTreatment()
                flag = true
                return null
            }
        })
        if (flag) return null
        mealPlanList.append('<li class="meal-plan-list__item item-filled meal-plan-item" data-product_id="' + prodId + '">\n' +
            '                            <figure class="meal-plan-item__img-box">\n' +
            '                                <a class="meal-plan-item__img-link" href="' + prodLink + '" target="_blank">\n' +
            '                                    <picture>\n' +
            '                                        <source srcset="' + prodImageHref + '" type="image/webp">\n' +
            '                                        <img class="meal-plan-item__img" src="' + prodImageHref + '" alt="">\n' +
            '                                    </picture>\n' +
            '                                </a>\n' +
            '                            </figure>\n' +
            '                            <div class="meal-plan-item__center">\n' +
            '                                <p class="meal-plan-item__title">\n' +
            '                                    <a href="' + prodLink + '" target="_blank">' + prodTitle + '</a></p>\n' +
            '                                <input class="nice-number__field js-nice-number" readonly type="number" value="1" min="1" name="quantity">\n' +
            '                            </div>\n' +
            '                            <div class="meal-plan-item__right">\n' +
            '                                <p class="meal-plan-item__price" data-price="' + price + '">$' + parseFloat(price).toFixed(2) + '</p>\n' +
            '                            </div>\n' +
            '                        </li>')
        mealsCount = mealPlanList.find($('.item-filled'))
        countFilledMeals++
        $('.meals-count-selected').click()
        if (countFilledMeals >= 10 && $('.plan-cards__item[data-meal-count="14"]').hasClass('meals-count-selected')) {
            $('.meals-left').text(mealsLeft + ' more')
        } else {
            $('.meals-left').text('at least ' + mealsLeft + ' more')
        }

        mealPlanList.find($('.js-nice-number')).niceNumber({
            autoSize: false,
            buttonDecrement: '–',
            buttonIncrement: '+',
            buttonPosition: 'around',
        })
        dataAttr()
        decrementTreatment()
        incrementTreatment()
    })
})

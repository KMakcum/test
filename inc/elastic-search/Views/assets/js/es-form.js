jQuery(document).ready(function ($) {

    const form = $('#elastic-search-form')
    const input = $('#elastic-search-input')
    const searchSection = $('.search-2__body.search-body')

    function objectifyForm(formArray) {
        let returnArray = {}
        for (let i = 0; i < formArray.length; i++) {
            returnArray[formArray[i]['name']] = formArray[i]['value']
        }
        return returnArray
    }

    $(input).on('keyup', _.debounce(() => {
        if (input.val().length >= 3) {
            form.trigger('submit')
        }
    }, 400))

    $(form).on('submit', function (e) {
        e.preventDefault()
        let formData = new FormData()
        formData.append('action', 'es_form_handler')
        formData.append('nonce', AjaxSettingsES.ajax_nonce)
        formData.append('data', JSON.stringify(objectifyForm($(this).serializeArray())))
        $.ajax({
            url: AjaxSettingsES.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                $('#elastic-search-input').off('keypress')
                $('#elastic-search-input').on('keypress', function (e) {
                    if (e.keyCode === 13) {
                        const formData = new FormData();
                        formData.append('action', 'show_all_products_handler')
                        formData.append('nonce', AjaxSettingsES.ajax_nonce)
                        formData.append('search_string', JSON.stringify(objectifyForm($(form).serializeArray())))
                        formData.append('selector','')
                        $.ajax({
                            url: AjaxSettingsES.ajax_url,
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            dataType: 'json',
                            success: function (response) {
                                window.location.href = document.location.origin + '/search-results/';
                            }
                        })
                    }
                })

                const hits = response.data.hits
                if (hits.hits.length !== 0) {
                    const content = hits.hits
                    let sortedData = {}
                    sortedData.vitamins = []
                    sortedData.staples = []
                    sortedData.meals = []
                    let vitCounter = 0
                    let mealCounter = 0
                    let staplesCounter = 0
                    $(content).each(function (index, item) {
                        switch (item._source.category) {
                            case 'vitamins':
                                sortedData.vitamins[vitCounter] =
                                    {
                                        title: item._source.title,
                                        link: item._source.link,
                                        image_url: item._source.image ? item._source.image : ''
                                    }
                                vitCounter++
                                break
                            case 'staples':
                                sortedData.staples[staplesCounter] =
                                    {
                                        title: item._source.title,
                                        link: item._source.link,
                                        image_url: item._source.image ? item._source.image : ''
                                    }
                                staplesCounter++
                                break
                            case 'meals':
                                sortedData.meals[mealCounter] =
                                    {
                                        title: item._source.title,
                                        link: item._source.link,
                                        image_url: item._source.image ? item._source.image : ''
                                    }
                                mealCounter++
                                break
                            default:
                                break
                        }
                    })
                    let mealsItems = ''
                    if (sortedData.meals.length !== 0) {
                        mealsItems += '<ul class="search-results__found-products found-products">\n'
                        $(sortedData.meals).each(function (index, item) {
                            if (index < 3 && item) {
                                mealsItems +=
                                    '                                   <li class="found-products__item">\n' +
                                    '                                            <a class="found-products__link" href="' + item.link + '">\n' +
                                    '                                                <picture>\n' +
                                    '                                                    <img class="found-products__img" src="' + item.image_url + '" alt="">\n' +
                                    '                                                </picture>\n' +
                                    '                                                <div class="found-products__info">\n' +
                                    '                                                    <p class="found-products__name">' + item.title + '</p>\n' +
                                    '                                                </div>\n' +
                                    '                                            </a>\n' +
                                    '                                        </li>\n'
                            }
                        })
                        mealsItems += '                                    </ul>'
                        mealsItems += '<button id="allMealsLink" class="search-results__show-all link-2"">Show all results for Meals (' + sortedData.meals.length + ')</button>\n'

                    } else {
                        mealsItems +=
                            '                                    <p class="search-results__not-found">Nothing found</p>\n' +
                            '                                    <a href="' + AjaxSettingsES.site_url + '/product-category/meals" class="search-results__show-all link-2">Browse all Meals</a>\n'
                    }
                    let vitaminsItems = ''
                    if (sortedData.vitamins.length !== 0) {
                        vitaminsItems += '<ul class="search-results__found-products found-products">\n'
                        $(sortedData.vitamins).each(function (index, item) {
                            if (index < 3 && item) {
                                vitaminsItems +=
                                    '                                   <li class="found-products__item">\n' +
                                    '                                            <a class="found-products__link" href="' + item.link + '">\n' +
                                    '                                                <picture>\n' +
                                    '                                                    <img class="found-products__img" src="' + item.image_url + '" alt="">\n' +
                                    '                                                </picture>\n' +
                                    '                                                <div class="found-products__info">\n' +
                                    '                                                    <p class="found-products__name">' + item.title + '</p>\n' +
                                    '                                                </div>\n' +
                                    '                                            </a>\n' +
                                    '                                        </li>\n'
                            }
                        })
                        vitaminsItems += '                                    </ul>'
                        vitaminsItems += '<button id="allVitaminsLink" class="search-results__show-all link-2" type="button">Show all results for Vitamins (' + sortedData.vitamins.length + ')</button>\n'

                    } else {
                        vitaminsItems +=
                            '                                    <p class="search-results__not-found">Nothing found</p>\n' +
                            '                                    <a href="' + AjaxSettingsES.site_url + '/product-category/vitamins" class="search-results__show-all link-2" >Browse all Vitamins</a>\n'
                    }
                    let staplesItems = ''
                    if (sortedData.staples.length !== 0) {
                        staplesItems += '<ul class="search-results__found-products found-products">\n'
                        $(sortedData.staples).each(function (index, item) {
                            if (index < 3 && item) {
                                staplesItems +=
                                    '                                   <li class="found-products__item">\n' +
                                    '                                            <a class="found-products__link" href="' + item.link + '">\n' +
                                    '                                                <picture>\n' +
                                    '                                                    <img class="found-products__img" src="' + item.image_url + '" alt="">\n' +
                                    '                                                </picture>\n' +
                                    '                                                <div class="found-products__info">\n' +
                                    '                                                    <p class="found-products__name">' + item.title + '</p>\n' +
                                    '                                                </div>\n' +
                                    '                                            </a>\n' +
                                    '                                        </li>\n'
                            }
                        })
                        staplesItems += '                                    </ul>'
                        staplesItems += '<button id="allStaplesLink" class="search-results__show-all link-2" type="button">Show all results for Groceries (' + sortedData.staples.length + ')</button>\n'

                    } else {
                        staplesItems +=
                            '                                    <p class="search-results__not-found">Nothing found</p>\n' +
                            '                                    <a href="' + AjaxSettingsES.site_url + '/groceries/" class="search-results__show-all link-2">Browse all Groceries</a>\n'
                    }

                    searchSection.empty()
                    searchSection.append('<div class="search__body search-body">\n' +
                        '<ul class="search__results search-results">\n' +
                        '<li class="search-results__item">\n' +
                        '<p class="search-results__title">Meals</p>\n' +
                        mealsItems +
                        '</li>\n' +
                        '<li class="search-results__item">\n' +
                        '<p class="search-results__title">Vitamins</p>\n' +
                        vitaminsItems +
                        '</li>\n' +
                        '<li class="search-results__item">\n' +
                        '<p class="search-results__title">Groceries</p>\n' +
                        staplesItems +
                        '</li>\n' +
                        '</ul>\n' +
                        '</div>')
                    addLinkEventListener('#allMealsLink')
                    addLinkEventListener('#allStaplesLink')
                    addLinkEventListener('#allVitaminsLink')
                } else {
                    searchSection.empty()
                    searchSection.append('<div class="search__body search-body">\n' +
                        '<div class="no-results">\n' +
                        '                                <div class="no-results__search-state search-state">\n' +
                        '                                    <img class="search-state__img" src="' + AjaxSettingsES.stylesheet_dir + '/assets/img/base/search-no-results.svg" width="80" height="80" alt="">\n' +
                        '                                    <p class="search-state__txt">No results for ‘' + input.val() + '’</p>\n' +
                        '                                </div><!-- / .search-start .search-state -->\n' +
                        '                                <div class="no-results__browse-for browse-for">\n' +
                        '                                    <p class="browse-for__title">Browse for</p>\n' +
                        '                                    <ul class="browse-for__checkbox-list checkbox-list">\n' +
                        '                                        <li class="checkbox-list__item">\n' +
                        '                                            <label class="checkbox-item checkbox-item--type--5">\n' +
                        '                                                <input class="checkbox-item__field visually-hidden" type="radio" value="meals" name="browse_for">\n' +
                        '                                                <a class="checkbox-item__box" href="' + AjaxSettingsES.site_url + '/product-category/meals">\n' +
                        '                                                    <svg class="checkbox-item__icon" width="24" height="24" fill="#34A34F">\n' +
                        '                                                        <use href="#icon-meals-2"></use>\n' +
                        '                                                    </svg>\n' +
                        '                                                    Meals\n' +
                        '                                                </a>\n' +
                        '                                            </label><!-- / .checkbox-item -->\n' +
                        '                                        </li>\n' +
                        '                                        <li class="checkbox-list__item">\n' +
                        '                                            <label class="checkbox-item checkbox-item--type--5">\n' +
                        '                                                <input class="checkbox-item__field visually-hidden" type="radio" value="staples" name="browse_for">\n' +
                        '                                                <a class="checkbox-item__box" href="' + AjaxSettingsES.site_url + '/groceries/">\n' +
                        '                                                    <svg class="checkbox-item__icon" width="24" height="24" fill="#34A34F">\n' +
                        '                                                        <use href="#icon-staples"></use>\n' +
                        '                                                    </svg>\n' +
                        '                                                    Groceries\n' +
                        '                                                </a>\n' +
                        '                                            </label><!-- / .checkbox-item -->\n' +
                        '                                        </li>\n' +
                        '                                        <li class="checkbox-list__item">\n' +
                        '                                            <label class="checkbox-item checkbox-item--type--5">\n' +
                        '                                                <input class="checkbox-item__field visually-hidden" type="radio" value="vitamins" name="browse_for">\n' +
                        '                                                <a class="checkbox-item__box" href="' + AjaxSettingsES.site_url + '/product-category/vitamins">\n' +
                        '                                                    <svg class="checkbox-item__icon" width="24" height="24" fill="#34A34F">\n' +
                        '                                                        <use href="#icon-vitamins"></use>\n' +
                        '                                                    </svg>\n' +
                        '                                                    Vitamins\n' +
                        '                                                </a>\n' +
                        '                                            </label><!-- / .checkbox-item -->\n' +
                        '                                        </li>\n' +
                        '                                    </ul><!-- / .checkbox-list -->\n' +
                        '                                </div><!-- / .browse-for -->\n' +
                        '                                </div>\n' +
                        '                            </div><!-- / .no-results -->')
                }
            },
            error: function (response) {
                console.log(response, ' _error')
            }
        })
    })

    function addLinkEventListener(selector) {
        $(selector).on('click', function (e) {
            e.preventDefault()
            const formData = new FormData();
            formData.append('action', 'show_all_products_handler')
            formData.append('nonce', AjaxSettingsES.ajax_nonce)
            formData.append('search_string', JSON.stringify(objectifyForm($(form).serializeArray())))
            formData.append('selector', JSON.stringify(selector))
            $.ajax({
                url: AjaxSettingsES.ajax_url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    window.location.href = document.location.origin + '/search-results/';
                }
            })
        })
    }


})

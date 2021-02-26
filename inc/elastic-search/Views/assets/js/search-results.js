jQuery(document).ready(function ($) {
    const backButton = $('.back-to-search-results__button.control-button')
    const form = $('#elastic-search-form')
    const input = $('#elastic-search-input')
    const seeAllButton = $('.see-all-button')

    function objectifyForm(formArray) {
        let returnArray = {}
        for (let i = 0; i < formArray.length; i++) {
            returnArray[formArray[i]['name']] = formArray[i]['value']
        }
        return returnArray
    }

    seeAllButton.on('click', function (e) {
        e.preventDefault()
        const formData = new FormData();
        formData.append('action', 'show_all_products_handler')
        formData.append('nonce', AjaxSettingsES.ajax_nonce)
        formData.append('search_string', JSON.stringify(objectifyForm($(form).serializeArray())))
        formData.append('selector', JSON.stringify('#' + $(this).attr('id')));
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

    backButton.on('click', function (e) {
        e.preventDefault()
        const formData = new FormData();
        formData.append('action', 'show_all_products_handler')
        formData.append('nonce', AjaxSettingsES.ajax_nonce)
        formData.append('search_string', JSON.stringify(objectifyForm($(form).serializeArray())))
        formData.append('selector', '')
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


})
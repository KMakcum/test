jQuery(document).ready(function ($) {

    if (typeof userSettings !== 'undefined') {
        const userNameInput = $('input[name="user-name"]')
        const userEmailInput = $('input[name="user-email"]')
        const userPhoneInput = $('input[name="user-phone"]')
        if (userSettings.userName) {
            userNameInput.attr('type', 'hidden')
            userNameInput.val(userSettings.userName)
            $('label[for="modal-submit-question-name"]').css('display', 'none')
        }
        if (userSettings.userEmail) {
            userEmailInput.attr('type', 'hidden')
            userEmailInput.val(userSettings.userEmail)
            $('label[for="modal-submit-question-email"]').css('display', 'none')
        }
        if (userSettings.userPhone) {
            userPhoneInput.attr('type', 'hidden')
            userPhoneInput.val(userSettings.userPhone)
            $('label[for="modal-submit-question-phone"]').css('display', 'none')
        }
    }

    function objectifyForm(formArray) {
        let returnArray = {}
        for (let i = 0; i < formArray.length; i++) {
            returnArray[formArray[i]['name']] = formArray[i]['value']
        }
        return returnArray
    }

    let formData = new FormData()
    const formSubmitQuestion =
        ajaxSettings.ajax_form_page_slug === 'faq' || ajaxSettings.ajax_form_page_slug === 'user-verification'
        ? $('.modal-submit-question .data__form') : $('.contacts-main .contacts__form')
    const orderNumber = $(this).find('#modal-submit-question-order-number')
    const validation = {
        isNumber: function (str) {
            const pattern = /^\d+$/
            return pattern.test(str)
        },
        isMail: function (mail) {
            const pattern = /(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/
            return pattern.test(mail)
        }
    }
    Dropzone.options.userFilesDropzone =
        {
            previewsContainer: '.attachments__files', // Define the container to display the previews
            previewTemplate: `<div class="files__item file-row">
                                    <p class="file-row__name" data-dz-name></p>
                                    <p class="file-row__size" data-dz-size></p>
                                    <button class="file-row__remove control-button control-button--no-txt control-button--remove" data-dz-remove type="button">
                                        <svg class="control-button__icon" width="16" height="16" fill="#252728" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zM6.53 5.47a.75.75 0 0 0-1.06 1.06L6.94 8 5.47 9.47a.75.75 0 1 0 1.06 1.06L8 9.06l1.47 1.47a.75.75 0 1 0 1.06-1.06L9.06 8l1.47-1.47a.75.75 0 1 0-1.06-1.06L8 6.94 6.53 5.47z"/></svg>
                                    </button>
                                </div><!-- / .file-row -->`,
            createImageThumbnails: false,
            uploadMultiple: true,
            autoProcessQueue: false,
            parallelUploads: 10,
            maxFilesize: 5,
            maxFiles: 10,
            timeout: 180000,
            acceptedFiles: 'image/jpeg, image/png',
            accept: function (file, done) {
                formData.append('files[]', file)
            }
        }

    orderNumber.on('input', function (e) {
        $(this).removeAttr('style')
        $(this).parent($('.fields-list__item')).removeAttr('style')
        $(this).siblings($('.field-box__label')).removeAttr('style')
    })
    $('.contacts__form #modal-submit-question-email').on('input', function (e) {
        $(this).removeAttr('style')
        $(this).parent($('.fields-list__item')).removeAttr('style')
        $(this).siblings($('.field-box__label')).removeAttr('style')
    })

    formSubmitQuestion.on('submit', function (e) {
        e.preventDefault()
        if ( !validation.isMail($('#modal-submit-question-email', this).val()) && $('#modal-submit-question-email', this).val() !== '' ) {
            $('#modal-submit-question-email', this).parent().css({color: 'red'})
            $('#modal-submit-question-email', this).css({borderColor: 'red'})
            $('#modal-submit-question-email', this).siblings($('.field-box__label')).css({color: 'red'})
            return false
        }
        if (!validation.isNumber(orderNumber.val()) && orderNumber.val() !== '') {
            orderNumber.parent($('.fields-list__item')).css({color: 'red'})
            orderNumber.css({borderColor: 'red'})
            orderNumber.siblings($('.field-box__label')).css({color: 'red'})
            return false
        }
        formData.append('action', 'question_form_handler');
        formData.append('nonce', ajaxSettings.ajax_nonce)
        const zipCode = $('.sf_show_zip_picker').text()
        if (zipCode) {
            formData.append('zip_code', zipCode)
        }
        formData.append('data', JSON.stringify(objectifyForm($(this).serializeArray())))
        $.ajax({
            url: ajaxSettings.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
              if (ajaxSettings.ajax_form_page_slug === 'faq') {
                $('.modal-common__close').click();
                $('.submit-question__button').attr('href', '#js-modal-faq-thank-you')
                setTimeout(function () {
                    $('.submit-question__button')[0].click()
                }, 375)
              } else if( ajaxSettings.ajax_form_page_slug == 'contact-us' ) {
                  $( 'section.contacts' ).addClass('contacts--thank-you')
                  $( '.contacts__col:first-child>*:nth-child(-n+2)' ).hide();
                  $( '#js-modal-contact-us-thank-you' ).show();
              }
            },
            error: function (response) {
                console.log(response)
            }
        })
    })

    $('a[data-chat]').on('click', function (e) {
        window.Intercom('show')
    })
    let urlString = location.href;
    let url = new URL(urlString);
    if (url.searchParams.get('ask-question') === 'open') {
        setTimeout(function () {
            $.fancybox.open({
                src: '#js-modal-submit-question'
            })
        }, 200)
    }
})

jQuery(document).ready(function ($) {
    const resendLink = $('#verification-resend')
    const changeEmailForm = $('#changeEmailFrom')
    const resendInvalidActivationLink = $('#resendInvalidActivationLink')
    const changeEmailButtonSubmit = $('#changeEmailButton')
    const verificationCode1 = $('#verification-code-1')
    const verificationCode2 = $('#verification-code-2')
    const verificationCode3 = $('#verification-code-3')
    const verificationCode4 = $('#verification-code-4')
    const confirmationUl = $('.code-confirmation__code.code')
    const codeField = $('.code__field')

    function objectifyForm(formArray) {
        let returnArray = {}
        for (let i = 0; i < formArray.length; i++) {
            returnArray[formArray[i]['name']] = formArray[i]['value']
        }
        return returnArray
    }

    codeField.on('focus', function () {
        if ($(this).is('#verification-code-2') && verificationCode1.val() === '') {
            verificationCode1.trigger('focus')
            return false
        }
        if ($(this).is('#verification-code-3') && verificationCode1.val() === '') {
            verificationCode1.trigger('focus')
            return false
        }
        if ($(this).is('#verification-code-3') && verificationCode2.val() === '') {
            verificationCode2.trigger('focus')
            return false
        }
        if ($(this).is('#verification-code-4') && verificationCode1.val() === '') {
            verificationCode1.trigger('focus')
            return false
        }
        if ($(this).is('#verification-code-4') && verificationCode2.val() === '') {
            verificationCode2.trigger('focus')
            return false
        }
        if ($(this).is('#verification-code-4') && verificationCode3.val() === '') {
            verificationCode3.trigger('focus')
            return false
        }
    })

    verificationCode1.on('input', function (e) {
        confirmationUl.removeClass('code--success')
        confirmationUl.removeClass('code--error')
    })

    verificationCode4.on('input', function () {
        if (verificationCode1.val() !== '' &&
            verificationCode2.val() !== '' &&
            verificationCode3.val() !== '' &&
            verificationCode4.val() !== '') {
            let formData = new FormData()
            formData.append('action', 'verify_user_by_code_handler')
            formData.append('nonce', ajaxSettings.ajax_nonce)
            formData.append('verification_code', verificationCode1.val().toString() + verificationCode2.val().toString() + verificationCode3.val().toString() + verificationCode4.val().toString())
            formData.append('user_email', changeEmailButtonSubmit.attr('data-user-email'))
            $.ajax({
                url: ajaxSettings.ajax_url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    confirmationUl.addClass('code--success')
                    if (response.data.status === 202) {
                        window.location.href = response.data.redirect_url
                    }
                },
                error: function (response) {
                    confirmationUl.removeClass('code--success')
                    confirmationUl.addClass('code--error')
                }
            })
        }
    })

    $('#intercom').on('click', function (e) {
        window.Intercom('show')
    })

    resendInvalidActivationLink.on('click', function (e) {
        e.preventDefault()
        let formData = new FormData()
        const userEmail = $(this).attr('data-user-email')
        formData.append('action', 'resend_verification_code_handler')
        formData.append('nonce', ajaxSettings.ajax_nonce)
        formData.append('user_email', userEmail)
        $.ajax({
            url: ajaxSettings.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.data === 202) {
                    window.location.href = ajaxSettings.site_url + '/user-verification?sent=true&user-email=' + userEmail
                }
            },
            error: function (response) {
                console.log(response)
            }
        })
    })

    resendLink.on('click', function (e) {
        e.preventDefault()
        let formData = new FormData()
        const userEmail = $(this).attr('data-user-email')
        formData.append('action', 'resend_verification_code_handler')
        formData.append('nonce', ajaxSettings.ajax_nonce)
        formData.append('user_email', userEmail)
        $.ajax({
            url: ajaxSettings.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.data === 202) {
                    window.location.href = ajaxSettings.site_url + '/user-verification?sent=true&user-email=' + userEmail
                }
            },
            error: function (response) {
                console.log(response)
            }
        })
    })

    changeEmailForm.on('submit', function (e) {
        e.preventDefault()
        let formData = new FormData()
        formData.append('action', 'user_change_email_handler')
        formData.append('nonce', ajaxSettings.ajax_nonce)
        formData.append('old_user_email', changeEmailButtonSubmit.attr('data-user-email'))
        formData.append('data', JSON.stringify(objectifyForm($(this).serializeArray())))
        $.ajax({
            url: ajaxSettings.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.data === 202) {
                    window.location.href = ajaxSettings.site_url + '/user-verification?sent=true&user-email=' + $('#modal-change-email-value').val()
                }
            },
            error: function (response) {
                console.log(response)
            }
        })

    })

})

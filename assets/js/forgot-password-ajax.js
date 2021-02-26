jQuery(document).ready(function ($) {

  function objectifyForm (formArray) {
    let returnArray = {}
    for (let i = 0; i < formArray.length; i++) {
      returnArray[formArray[i]['name']] = formArray[i]['value']
    }
    return returnArray
  }

  const formCheckEmail = $('.reset-password-start .data__form')
  const formCheckEmailTokenInvalid = $('.reset-password-token-invalid .data__form')

  const sectionResetPasswordStart = $('.reset-password-start')
  const sectionResetPasswordSent = $('.reset-password-sent')
  const sectionResetPasswordError = $('.reset-password-start .message--full')
  const sectionResetPasswordTokenInvalid = $('.reset-password-token-invalid')
  const sectionResetPasswordErrorTokenInvalid = $('.reset-password-token-invalid .message--full')

  formCheckEmailTokenInvalid.on('submit', function (e) {
    e.preventDefault()
    sectionResetPasswordError.css({ display: 'none' })
    let formData = new FormData()
    formData.append('action', 'check_user_email')
    formData.append('nonce', ajaxSettings.ajax_nonce)
    formData.append('data', JSON.stringify(objectifyForm($(this).serializeArray())))
    $.ajax({
      url: ajaxSettings.ajax_url,
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function (response) {
        if (response.data['email_found']) {
          sectionResetPasswordTokenInvalid.fadeOut(function (e) {
            sectionResetPasswordSent.fadeIn()
          })
        } else {
          sectionResetPasswordErrorTokenInvalid.fadeIn()
        }
      },
    })
  })

  formCheckEmail.on('submit', function (e) {
    e.preventDefault()
    sectionResetPasswordError.css({ display: 'none' })
    let formData = new FormData()
    formData.append('action', 'check_user_email')
    formData.append('nonce', ajaxSettings.ajax_nonce)
    formData.append('data', JSON.stringify(objectifyForm($(this).serializeArray())))
    $.ajax({
      url: ajaxSettings.ajax_url,
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function (response) {
        if (response.data['email_found']) {
          sectionResetPasswordStart.fadeOut(function (e) {
            sectionResetPasswordSent.fadeIn()
          })
        } else {
          sectionResetPasswordError.fadeIn()
        }
      },
    })
  })
})

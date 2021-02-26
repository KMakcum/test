jQuery(document).ready(function ($) {

  function objectifyForm (formArray) {
    let returnArray = {}
    for (let i = 0; i < formArray.length; i++) {
      returnArray[formArray[i]['name']] = formArray[i]['value']
    }
    return returnArray
  }

  function getSearchParameters () {
    let prmstr = window.location.search.substr(1)
    return prmstr != null && prmstr != '' ? transformToAssocArray(prmstr) : {}
  }

  function transformToAssocArray (prmstr) {
    let params = {}
    let prmarr = prmstr.split('&')
    for (let i = 0; i < prmarr.length; i++) {
      let tmparr = prmarr[i].split('=')
      params[tmparr[0]] = tmparr[1]
    }
    return params
  }

  const formSetPassword = $('.reset-password-token-valid .data__form')
  const sectionNewPasswordHasBeenChanged = $('.reset-password-has-been-changed')
  const sectionNewPasswordTokenValid = $('.reset-password-token-valid')
  const sectionNewPasswordErrorTokenValid = $('.reset-password-token-valid .message--full:not(.callback--error)')
  const sectionNewPasswordWeakErrorTokenValid = $('.reset-password-token-valid .callback--error')

  formSetPassword.on('submit', function (e) {
    e.preventDefault()
    sectionNewPasswordErrorTokenValid.css({ display: 'none' })
    sectionNewPasswordWeakErrorTokenValid.css({ display: 'none' })
    const formPasswords = formSetPassword.serializeArray()

    // Check password strength
    // Get current pwd state
    let pwdBoxItems = $('#pr-box:eq(0) ul li')
    let checkedCount = 0
    let totalCount = 0
    // Check each point of pwd box
    pwdBoxItems.each(function () {
      if ($(this).find('.pr-ok').length) {
        checkedCount++;
      }
      totalCount++;
    });
    // \Check password strength

    if (formPasswords[0]['value'] === formPasswords[1]['value']) {
      let formData = new FormData()
      formData.append('action', 'set_user_password')
      formData.append('nonce', ajaxSettings.ajax_nonce)
      formData.append('password', JSON.stringify(objectifyForm($(this).serializeArray())))
      formData.append('user_data', JSON.stringify(getSearchParameters()))
      formData.append('totalC', totalCount)
      formData.append('checkedC', checkedCount)
      $.ajax({
        url: ajaxSettings.ajax_url,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
          if (response.data['password_changed']) {
            sectionNewPasswordTokenValid.fadeOut(function (e) {
              sectionNewPasswordHasBeenChanged.fadeIn()
            })
          }else {
            $('.callback--error .message__txt').text(response.data.message)
            sectionNewPasswordWeakErrorTokenValid.fadeIn()
          }
        },
        error: function (response) {
          window.location.replace(ajaxSettings.forgot_password_url)
        }
      })
    } else {
      sectionNewPasswordErrorTokenValid.fadeIn()
    }
  })
})

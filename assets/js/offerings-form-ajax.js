jQuery(document).ready(function ($) {
  function objectifyForm (formArray) {
    let returnArray = {}
    for (let i = 0; i < formArray.length; i++) {
      returnArray[formArray[i]['name']] = formArray[i]['value']
    }
    return returnArray
  }

  let formData = new FormData()
  const form = $('.offering-item__form')
  form.on('submit', function (e) {
    e.preventDefault()
    formData.append('action', 'offerings_form_handler')
    formData.append('nonce', ajaxSettingsOfferingsForm.ajax_nonce)
    const zipCode = $('.sf_show_zip_picker').text()
    if (zipCode) {
      formData.append('zip_code', zipCode)
    }
    formData.append('data', JSON.stringify(objectifyForm($(this).serializeArray())))
    $.ajax({
      url: ajaxSettingsOfferingsForm.ajax_url,
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function (response) {

      },
      error: function (response) {

      }
    })
  })
  let url = new URL(location.href);
  if (url.searchParams.get('chat-open') === 'open') {
    setTimeout(function () {
      window.Intercom('show')
    }, 200)
  }
})

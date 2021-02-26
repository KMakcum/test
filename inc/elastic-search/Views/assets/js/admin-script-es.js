jQuery(document).ready(function ($) {
  const reindexButton = $('#reindexES')

  const recurse = (data) => {
    let htmlRetStr = '<ul>'
    for (let key in data) {
      if (typeof (data[key]) == 'object' && data[key] != null) {
        let x = key * 1
        if (isNaN(x)) {
          htmlRetStr += '<li>' + key + ':<ul>'
        }
        htmlRetStr += recurse(data[key])
        htmlRetStr += '</ul></li>'
      } else {
        htmlRetStr += ('<li>' + key + ': &quot;' + data[key] + '&quot;</li  >')
      }
    }

    htmlRetStr += '</ul >'
    return (htmlRetStr)
  }

  reindexButton.on('click', function (e) {
    $('#ES-notice').empty()
    $('#ES-notice').addClass('d-none')
    const formData = new FormData()
    formData.append('action', 'reindex_es_handler')
    formData.append('nonce', settingsES.ajax_nonce)
    formData.append('data', JSON.stringify('reindex'))
    $.ajax({
      url: settingsES.ajax_url,
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function (response) {
        $('#ES-notice').removeClass('d-none')
        $('#ES-notice').html(recurse(response.data))
      },
      error: function (response) {
        console.log(response)
      }
    })

  })
})

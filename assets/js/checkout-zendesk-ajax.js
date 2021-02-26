jQuery(document).ready(function ($) {
  // function objectifyForm (formArray) {
  //   let returnArray = {}
  //   for (let i = 0; i < formArray.length; i++) {
  //     returnArray[formArray[i]['name']] = formArray[i]['value']
  //   }
  //   return returnArray
  // }
  const faqUl = $('.questions__accordion.accordion-extra.accordion-extra--contrast')
  const zendeskQATitle = $('.questions__title')
  const qaSection = $('.questions')
  let formData = new FormData()
  formData.append('action', 'category_qa_ajax_endpoint')
  formData.append('nonce', ajaxSettings.ajax_nonce)
  formData.append('data', JSON.stringify('checkout'))

  $.ajax({
    url: ajaxSettings.ajax_url,
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (response) {
      let qaList = ''
      response.data.forEach(function (item) {
        qaList += '        <li class="accordion-extra__item">\n' +
          '            <p class="accordion-extra__header accordion-extra__header-appended">\n' +
          '              ' + item.title + '\n' +
          '            </p>\n' +
          '            <div class="accordion-extra__content content content--small accordion-extra__content-appened">\n' +
          '              ' + item.body + '\n' +
          '            </div>\n' +
          '        </li>'
      })
      $(qaSection).fadeIn(function () {
        $(qaList).appendTo(faqUl).css('display', 'none').fadeIn()
        zendeskQATitle.text('Common questions')
        let accordionHeaders = document.querySelectorAll('.accordion-extra__header-appended')
        accordionHeaders.forEach((accordionHeader) => {
          accordionHeader.addEventListener('click', function () {
            let accordionItem = this.closest('.accordion-extra__item')
            let accordionContent = accordionItem.querySelector('.accordion-extra__content-appened')
            if (accordionItem.classList.contains('accordion-extra__item--open')) {
              accordionItem.classList.remove('accordion-extra__item--open')
            } else {
              accordionItem.classList.add('accordion-extra__item--open')
            }
            $(accordionContent).slideToggle(300)
          }, { passive: true })
        })
      })
    },
    error: function (response) {
      console.log(response)
    }
  })

  // const ratingForm = $('.rate-assessment__form')
  // ratingForm.on('submit', function (e) {
  //   e.preventDefault()
  //   formData.append('action', 'offerings_form_handler')
  //   formData.append('nonce', ajaxSettings.ajax_nonce)
  //   const zipCode = $('.sf_show_zip_picker').text()
  //   if (zipCode) {
  //     formData.append('zip_code', zipCode)
  //   }
  //   formData.append('data', JSON.stringify(objectifyForm($(this).serializeArray())))
  //   $.ajax({
  //     url: ajaxSettings.ajax_url,
  //     method: 'POST',
  //     data: formData,
  //     processData: false,
  //     contentType: false,
  //     dataType: 'json',
  //     success: function (response) {
  //
  //     },
  //     error: function (response) {
  //
  //     }
  //   })
  // })

})

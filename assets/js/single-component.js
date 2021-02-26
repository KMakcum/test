jQuery(document).ready(function ($) {
  $.blockUI.defaults.message = null
  $(document).ajaxStart($.blockUI).ajaxStop($.unblockUI)
  const faqUl = $('.zendesk-q-and-a__accordion.accordion-extra.accordion-extra--contrast')
  const zendeskQATitle = $('.zendesk-q-and-a__title')
  let formData = new FormData()
  formData.append('action', 'single_component_qa_ajax_endpoint')
  formData.append('nonce', ajaxSettings.ajax_nonce)
  formData.append('data', JSON.stringify('single-component'))

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
      $(qaList).appendTo(faqUl).css('display', 'none').fadeIn()
      zendeskQATitle.text('Q&A')
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
    },
    error: function (response) {
      console.log(response)
    }
  })

  $('#chat-toggle').on('click', function (e) {
    window.Intercom('show')
  })

})

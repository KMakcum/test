jQuery(document).ready(function ($) {
  const productCat = $('.zendesk-q-and-a').attr('data-product-cat')
  const faqUl = $('.zendesk-q-and-a__accordion.accordion-extra.accordion-extra--contrast')
  const zendeskQATitle = $('.zendesk-q-and-a__title')
  let formData = new FormData()
  formData.append('action', 'category_qa_ajax_endpoint')
  formData.append('nonce', ajaxSettings.ajax_nonce)
  formData.append('data', JSON.stringify(productCat))
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
      zendeskQATitle.text('FAQ')
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
      const submitQuestionButton = $('.head-feedback__button.button.submit-question__button.btn-modal')
      if (submitQuestionButton.length !== 0) {
        submitQuestionButton.css('display', 'block')
      }
    },
    error: function (response) {
      console.log(response)
    }
  })

})

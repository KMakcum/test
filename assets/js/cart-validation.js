jQuery(document).ready(function ($) {
  $.blockUI.defaults.message = null
  const form = $('.woocommerce-cart-form')
  let lastChanged = ''
  let leggalyMeals = 0
  let maxFlag = false;
  const mealCategoryList = $('#meals-category-list')

  $(document).on('click', '.nice-number button', function (e) {
    e.stopPropagation()
    e.preventDefault()

    let countFilledMeal = 0
    mealCategoryList.find($('.js-nice-number')).each(function (e) {
      countFilledMeal += parseInt($(this).val())
    });
    if (countFilledMeal > 14) {
      maxFlag = true;
    }

    $(this).siblings('input').trigger('change');
    return false
  })

  $(document).on('change', '#meals-category-list .js-nice-number', function (e) {
    lastChanged = $(this)
    return false
  })

  form.on('submit', function (e) {
    // Show loader
    activateLoader( 'main', 'body' );

    let countFilledMeal = 0
    mealCategoryList.find($('.js-nice-number')).each(function (e) {
      countFilledMeal += parseInt($(this).val())
    });
    
    if (countFilledMeal > 14) {
      e.preventDefault()
      
      window.location.reload();
    }

    if (!maxFlag) {
      let countFilledMeals = 0;
      mealCategoryList.find($('.js-nice-number')).each(function (e) {
        countFilledMeals += parseInt($(this).val())
      })
      if (countFilledMeals >= 15) {
        leggalyMeals = 0
        mealCategoryList.find($('.js-nice-number')).each(function () {
          if ($(this).attr('name') !== lastChanged.attr('name')) {
            leggalyMeals += Number($(this).val())
          }
        })
        if (Number(lastChanged.val()) >= 15) {
          lastChanged.val(14 - Number(leggalyMeals))
        } else if (Number(lastChanged.val()) < 14 && Number(lastChanged.val()) >= 10) {
          lastChanged.val(10 - Number(leggalyMeals))
        } else {
          lastChanged.val(6 - Number(leggalyMeals))
        }
        $('.btn-modal[href="#js-modal-remove-item"]').click()
        return true
      } else {
        $.blockUI()
        return true
      }
    } else {
      lastChanged.val(Number(lastChanged.val())-1)
      $.blockUI()
    }

  })
})

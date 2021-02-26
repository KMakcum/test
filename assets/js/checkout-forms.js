jQuery(document).ready(function($){
	// Add "optional" bottom flag for not required fields
	$('.optional-bottom-label').append('<span class="field-wr__credits">Optional</span>');

	$(document).on('click', 'button[data-step="Schedule-Your-First-Delivery"]', function(){
		// Move user name data from delivery to shipping form
		$('#shipping_first_name').val($('#billing_first_name').val());
		$('#shipping_last_name').val($('#billing_last_name').val());

		// Duplicate telephone value from delivery to billing form
		if ($('input[name="shipping_phone"]').val() == '') {
			$('input[name="shipping_phone"]').val($('input[name="billing_phone"]').val());
		}
	});

	$('.checkout__form').on('checkout_place_order', function () {
		// Update phone number on form submit, add country code to number
		$('.js-intl-phone').each(function(){
			var code = $(this).closest('.iti').find('.iti__selected-dial-code').text(),
			current_number = $(this).val();

			$(this).val(code + current_number);
		});
		return true;
	});

	// Mark all previous statuses for checkout thank you page
	if ($('.checkout-thanks').length) {
		$('.order-status__item--completed').prevAll().addClass('order-status__item--completed');
	}

	// Ajax for customer feedback form
	$('.rate-assessment__form').submit(function(){
		$.ajax({
			url: checkout_ajax.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'save_order_customer_note',
				form: $(this).serialize()
			},
			complete: function(data) {
				if ( typeof $(this).find('textarea').val() !== 'undefined') {
					$('.rate-assessment').slideUp('fast');
				}
			}
		});

		return false;
	});

	// Show/hide additional billing form
	$(document).on('click', 'button[data-step="Schedule-Your-First-Delivery"]', function(){
		if ($('#checkout-use-as-billing-address').is(':checked')) {
			$('.woocommerce-billing-fieldset').addClass('hide-billing');
			// Copy all fields to save data in order
			copyFormsFields();
		}
	});
	$('#checkout-use-as-billing-address').on('click', function(){
		if ($(this).is(':checked')) {
			$('.woocommerce-billing-fieldset').addClass('hide-billing');
			// Copy all fields to save data in order
			copyFormsFields();
		}else {
			$('.woocommerce-billing-fieldset').removeClass('hide-billing');
		}
	});

	// Copy form fields
	function copyFormsFields() {
		// Country
		$('#shipping_country').val($('#billing_country').val());
		// Address line 1
		$('#shipping_address_1').val($('#billing_address_1').val());
		// Address line 2
		$('#shipping_address_2').val($('#billing_address_2').val());
		// ZIP
		$('#shipping_postcode').val($('#billing_postcode').val());
		// City
		$('#shipping_city').val($('#billing_city').val());
		// State
		$('#shipping_state').val($('#billing_state').val());
		// Cell phone
		$('#checkout-shipping-phone').val($('#checkout-phone').val());
	}

	// Add focus class on card number and css using timeout (library issue)
	setTimeout(function(){ $('.card-exist, .cvv-exist').addClass('field-box__field--entered'); }, 500);

	// Fix for cards mask
	$('.card-exist').focusin(function(){
		if ($(this).val() == '') {
			$(this).removeClass($(this).attr('data-type'));
		}
	});

	// Remove disable status from pre-filled step
	if ($('.card-exist').length) {
		$('#Payment-Method .checkout-item__button').removeAttr('disabled');
	}

	// Enable step button after third symbol
	$('#checkout-cvc').on('keyup', function(){
		var current_value = $(this).val().replace(/\s/g, '');
		if (current_value.length >= 3) {
			$(this).closest('.checkout-list__item').find('.checkout-item__button').removeAttr('disabled').focus();
		}
	});
});





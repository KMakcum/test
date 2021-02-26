(function ($) {
	$(document).ready(function () {
		const clearBtn = $('.button-clear-js');
		const updateZipsBtn = $('.button-update-js');
		const resultBlock = $('.cache-result .response');
		const spinner = $('.cache-result .lds-spinner');
		const spinnerHideClass = 'lds-spinner--hide';

		clearBtn.on('click', function (evt) {
			evt.preventDefault();

			const formData = new FormData()
			formData.append('action', 'regenerate_cache')
			formData.append('nonce', settingsCache.ajax_nonce)

			$.ajax({
				type: 'POST',
				url: settingsCache.ajax_url,
				data: formData,
				processData: false,
				contentType: false,
				dataType: 'json',
				beforeSend: function () {
					clearBtn.attr('disabled', 'disabled');
					spinner.removeClass(spinnerHideClass);
				},
				complete: function () {
					clearBtn.removeAttr('disabled');
					spinner.addClass(spinnerHideClass);
				},
				success: function (response) {
					// if (response.success) {
					// 	resultBlock.html(`Updated: ${response.data.message} products`);
					// } else {
					// 	resultBlock.html('Error, look console.log');
					// 	console.log(response)
					// }
				},
			})

		});


		updateZipsBtn.on('click', function(evt) {
			evt.preventDefault();

			const formData = new FormData()
			formData.append('action', 'update_zip_codes')
			formData.append('nonce', settingsCache.ajax_nonce)

			$.ajax({
				type: 'POST',
				url: settingsCache.ajax_url,
				data: formData,
				processData: false,
				contentType: false,
				dataType: 'json',
				beforeSend: function () {
					clearBtn.attr('disabled', 'disabled');
					spinner.removeClass(spinnerHideClass);
				},
				complete: function () {
					clearBtn.removeAttr('disabled');
					spinner.addClass(spinnerHideClass);
				},
				success: function (response) {
				},
			})
		});

	});
})(jQuery);
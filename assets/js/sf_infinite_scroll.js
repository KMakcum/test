(function($) {
	'use strict';

	let loadingFlag = false;
	let loadingFinish = false;

	const scrollOptions = {
		scroll_item_selector: '.product',
		scroll_content_selector: '.product-list',
		is_shop: false,
		// loader: '/wp-content/themes/sf-theme/assets/loader.gif'
	}

	const mainOptions = {
		'lastPage': window.infi_scrol_ajaxurl.lastPage,
		'currentPage': 1,
		'currentCatalog': window.infi_scrol_ajaxurl.categoryId,
	};

	const call_ajax = function() {

		if ( mainOptions.currentPage == mainOptions.lastPage ) {
			loadingFinish = true;
		}

		let searchParams = new URLSearchParams(window.location.search);

		loadingFlag = true;

		const data = {
			action: 'load_catalog',
			nextPage: mainOptions.currentPage,
			order: searchParams.get('orderby'),
			catId: mainOptions.currentCatalog,
			taxonomy: window.infi_scrol_ajaxurl.taxonomy,
			allergies: `${decodeURIComponent(location.search.split('allergies=')[1])}`
		};

		// if (scrollOptions.loader) {
		// 	$(scrollOptions.scroll_content_selector).after('<div class="scroll-loader"><center><img src="' + scrollOptions.loader + '"/></center><br></div>')
		// }

		$.ajax({
			type: 'POST',
			url: main.ajaxurl,
			data: data,
			dataType: 'html',
			success: function (response) {

				if ( mainOptions.currentPage < mainOptions.lastPage ) {
					mainOptions.currentPage += 1;
				}

				$(scrollOptions.scroll_content_selector).append(response);

				// $('.scroll-loader').remove()

				setTimeout( function() { // Fix for mozilla scroll looping
					// Hide loader
					disactivateLoader( 'catalog', '.catalog-main' );
				}, 300 );

				loadingFlag = false;

				$('.products').trigger('ajax_product_load_end')

			}
		});
	};


	$(window).on('scroll touchstart', function () {
		let y = $(this);
		let productsBlock = document.querySelector(scrollOptions.scroll_content_selector);
		let footer = document.querySelector('.main-footer__content');

		if (!loadingFlag && !loadingFinish && y.scrollTop() >= productsBlock.scrollHeight - footer.scrollHeight - 300 && mainOptions.lastPage >= 1 ) {
			// Show loader
			activateLoader( 'catalog', '.catalog-main' );

			call_ajax();
		}
	});


})(jQuery);
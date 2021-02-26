// init amplitude
(function(e,t){var n=e.amplitude||{_q:[],_iq:{}};var r=t.createElement("script")
;r.type="text/javascript"
;r.integrity="sha384-girahbTbYZ9tT03PWWj0mEVgyxtZoyDF9KVZdL+R53PP5wCY0PiVUKq0jeRlMx9M"
;r.crossOrigin="anonymous";r.async=true
;r.src="https://cdn.amplitude.com/libs/amplitude-7.2.1-min.gz.js"
;r.onload=function(){if(!e.amplitude.runQueuedFunctions){
    console.log("[Amplitude] Error: could not load SDK")}}
;var i=t.getElementsByTagName("script")[0];i.parentNode.insertBefore(r,i)
;function s(e,t){e.prototype[t]=function(){
    this._q.push([t].concat(Array.prototype.slice.call(arguments,0)));return this}}
    var o=function(){this._q=[];return this}
    ;var a=["add","append","clearAll","prepend","set","setOnce","unset"]
    ;for(var c=0;c<a.length;c++){s(o,a[c])}n.Identify=o;var u=function(){this._q=[]
        ;return this}
    ;var l=["setProductId","setQuantity","setPrice","setRevenueType","setEventProperties"]
    ;for(var p=0;p<l.length;p++){s(u,l[p])}n.Revenue=u
    ;var d=["init","logEvent","logRevenue","setUserId","setUserProperties","setOptOut","setVersionName","setDomain","setDeviceId","enableTracking","setGlobalUserProperties","identify","clearUserProperties","setGroup","logRevenueV2","regenerateDeviceId","groupIdentify","onInit","logEventWithTimestamp","logEventWithGroups","setSessionId","resetSessionId"]
    ;function v(e){function t(t){e[t]=function(){
        e._q.push([t].concat(Array.prototype.slice.call(arguments,0)))}}
        for(var n=0;n<d.length;n++){t(d[n])}}v(n);n.getInstance=function(e){
        e=(!e||e.length===0?"$default_instance":e).toLowerCase()
        ;if(!n._iq.hasOwnProperty(e)){n._iq[e]={_q:[]};v(n._iq[e])}return n._iq[e]}
    ;e.amplitude=n})(window,document);

amplitude.getInstance().init('f7db71d1be69acf1bf51c3e9ad166be0', null, { includeUtm: true });

// jQuery amplitude tracking
(function ( $ ) {
    "use strict";

    $.fn.amplitude = function() {

        this.initialize = function() {
            this.bindGlobal();
            this.bindHome();
            this.bindCategory();
            this.bindProduct();
            this.bindCart();
        }

        this.bindGlobal = function() {
            var self = this;
            // header
            $('#menu-header li a').on('click', function() {
                self.track('Header Menu Click', 'page', $(this).html());
            });

            $('ul.main-nav__user-list li a').on('click', function() {
                self.track('Header User Menu Click', 'click', $(this).html());
            });

            $('a.main-nav__logo').on('click', function() {
                self.track('Header Back To Home', 'click', 'Site Logo');
            });

            $('a.main-nav__cart').on('click', function() {
                self.track('Header Cart', 'click', 'Header Cart');
            });

            $('button.feedback-btn').on('click', function() {
                self.track('Feedback', 'click', 'Feedback');
            });

            $('.intercom-lightweight-app-launcher').on('click', function() {
                self.track('Open Intercom Messenger', 'click', 'Open Intercom Messenger');
            });


            $('footer.main-footer a').on('click', function() {
                self.track('Footer Links Click', 'page', $(this).html());
            });
        }

        this.bindHome = function() {
            var self = this;

            $('.offer-slider a.lead__button').on('click', function() {
                self.track('Main Slider Click', 'slide', $(this).closest('.lead__txt').find('h2').text());
            });

            $('section.how-it-works a.how-it-works__button').on('click', function() {
                self.track('How It Works', 'click', 'How It Works');
            });

            $('.reviews-slider__nav button.slider-nav__button').on('click', function() {
                self.track('Reading Reviews Home Page', 'click', 'Reading Reviews Home Page');
            });

            $('.ready-to-start a.ready-to-start__button').on('click', function() {
                self.track('Ready To Start Home Page', 'click', 'Ready To Start');
            });

        }

        this.bindCategory = function() {
            var self = this;

            $('ul.products__list a.product-item__img-link').on('click', function() {
                self.track(
                    'Category Product Click',
                    'click',
                    $(this).next().find('p.product-item__name a').text()
                );
            });

            $('ul.products__list a.product-item__name-link').on('click', function() {
                self.track(
                    'Category Product Click',
                    'click',
                    $(this).text()
                );
            });

            $('ul.products__list .product-item__actions a.ajax_add_to_cart').on('click', function() {
                self.track(
                    'Category Product Add To Cart',
                    'click',
                    'Add to your plan - ' + $(this).closest('.product-item__name').find('a').text()
                );

            });

            $('ul.products__filter-list .product-item__actions a.ajax_add_to_cart').on('click', function() {
                self.track(
                    'Category Product Add To Cart',
                    'click',
                    'Add to your plan - ' + $(this).closest('.product-item__name').find('a').text()
                );
            });

            var filters = 'ul.products__filter-list li.filter-list__item button';
            $(filters).first().on('click', function() {
                self.track(
                    'Category Allergies Filter',
                    'click',
                    'Category Allergies Filter'
                );
            });

            $(filters).last().on('click', function() {
                self.track(
                    'Category Diet Filter',
                    'click',
                    'Category Diet Filter'
                );
            });

        }

        this.bindProduct = function() {
            var self = this;
            $('.product-card__gallery a.product-slider-big__link').on('click', function() {
                self.track(
                    'Product Gallery View',
                    'click',
                    'Product Gallery View'
                );
            });

            $('ul.customize__variations li a.variations__link').on('click', function() {
                self.track(
                    'Product Customize Click',
                    'click',
                    'Customize: ' + $(this).find('.variations__info h3').text()
                );
            });

            $('section.nutrition button.nutrition__button-full-info').on('click', function() {
                self.track(
                    'Product: View full nutrition info',
                    'click',
                    'Product: View full nutrition info'
                );
            });

            $('button.form-add-to-cart__button').on('click', function() {
                self.track(
                    'Product Add to cart',
                    'click',
                    'Product Add to cart'
                );
            });

            $('ul.product-card__social a').on('click', function() {
                self.track(
                    'Product Social Link',
                    'click',
                    $(this).attr('title')
                );
            });


            $('.product-group__slider a.product-item__img-link').on('click', function() {
                self.track(
                    'Product Similar Link',
                    'click',
                    $(this).next().find('.product-item__name a').text()
                );
            });

            $('.product-group__slider a.product-item__name-link').on('click', function() {
                self.track(
                    'Product Similar Name',
                    'click',
                    $(this).text()
                );
            });
        }

        this.bindCart = function() {
            var self = this;
            $('a.cart-totals__button').on('click', function() {
                self.track(
                    'Cart Go To Checkout',
                    'click',
                    'Cart Go To Checkout'
                );
            });

            $('a.empty-cart-box__button').on('click', function() {
                self.track(
                    'Empty Cart Box',
                    'click',
                    $(this).text()
                );
            });

            $('a.checkout-item__button').on('click', function() {
                self.track(
                    'Checkout Step',
                    'click',
                    'Checkout Step: ' + $(this).data('step')
                );
            });

            $('section.questions li.accordion-extra__item').on('click', function() {
                self.track(
                    'Checkout Questions',
                    'click',
                    'Checkout Question: ' + $(this).find('.accordion-extra__header').text()
                );
            });
        }

        this.track = function(event, paramName, selectedValue) {
            var now = new Date(),
                weekday = new Array(7);
            weekday[0] = "Sunday";
            weekday[1] = "Monday";
            weekday[2] = "Tuesday";
            weekday[3] = "Wednesday";
            weekday[4] = "Thursday";
            weekday[5] = "Friday";
            weekday[6] = "Saturday";

            var monthNames = ["January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];

            var onejan = new Date(now.getFullYear(), 0, 1),
                week = Math.ceil( (((now - onejan) / 86400000) + onejan.getDay() + 1) / 7 );

            var amplitudeParams = {
                cohort_day: weekday[now.getDay()],
                cohort_month: monthNames[now.getMonth()],
                cohort_week: week,
                currentPage: $('head title').text(),
                currentUrl: $(location).attr('href')
            };
            if (paramName) {
                amplitudeParams[paramName] = selectedValue;
            }

            amplitude.getInstance().logEvent(event, amplitudeParams);
        }

        return this.initialize();
    };
}(jQuery));

jQuery(document).ready(function($) {
    $(document).amplitude();
});

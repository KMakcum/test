<?php
?>

<main class="site-main catalog-main search-results-main">
    <div class="search-results-main__back back-to-search-results">
        <div class="container">
            <a class="back-to-search-results__button control-button" href="search-results-2.html">
                <svg class="control-button__icon" width="24" height="24" fill="#252728">
                    <use href="#icon-arrow-left"></use>
                </svg>
                Back to search results
            </a>
        </div>
    </div><!-- / .back-to-search-results -->

    <section class="products">
        <div class="container">
            <div class="products__head">
                <div class="products__title-and-page-info">
                    <h1 class="products__title">Meals</h1>
                    <span class="products__page-info"><span>148</span> / 256</span>
                </div>
                <div class="products__toggle toggle toggle--easy">
                    <input class="js-toggle-switch visually-hidden" type="checkbox" name="only-recommended"><!-- / .toggle-switch -->
                    <span class="toggle__txt">Recommended only</span>
                </div>
            </div>
            <div class="products__filter-and-sort">
                <ul class="products__filter-list filter-list">
                    <li class="filter-list__item">
                        <button class="filter-list__button" type="button">
                            Allergies <span class="filter-list__button-counter">2</span>
                        </button>
                        <form class="filter-list__dropdown products-filter" action="#" method="post">
                            <div class="header-products-filter">
                                <div class="header-products-filter__actions">
                                    <button class="header-products-filter__clear products-filter__clear" type="button">Clear</button>
                                    <p class="header-products-filter__title">Allergies</p>
                                    <button class="header-products-filter__close control-button control-button--no-txt control-button--close" type="button">
                                        <svg class="control-button__icon" width="24" height="24" fill="#252728">
                                            <use href="#icon-times"></use>
                                        </svg>
                                    </button>
                                </div>
                                <div class="header-products-filter__txt content">
                                    Applied based on your survey results. If you would like to
                                    modify this list <a href="#">re-take the survey</a> again.
                                </div>
                                <a class="header-products-filter__offer offer-card-2" href="survey.html">
                                    <div class="offer-card-2__body">
                                        <p class="offer-card-2__title">Personalize your experience</p>
                                        <span class="offer-card-2__button control-button control-button--small control-button--invert control-button--color--main">
                                                Take a Survey
                                                <svg class="control-button__icon" width="24" height="24" fill="#0A6629">
                                                    <use href="#icon-angle-rigth-light"></use>
                                                </svg>
                                            </span>
                                    </div>
                                    <picture>
                                        <source srcset="img/base/personalize-your-experience-2.webp" type="image/webp">
                                        <img class="offer-card-2__bg" src="img/base/personalize-your-experience-2.jpg" alt="">
                                    </picture>
                                </a><!-- / .offer-card-2 -->
                            </div><!-- / .header-products-filter -->
                            <div class="body-products-filter">
                                <ul class="body-products-filter__checkbox-list checkbox-list">
                                    <li class="checkbox-list__item">
                                        <label class="checkbox-item checkbox-item--type--2">
                                            <input class="checkbox-item__field visually-hidden" type="checkbox" name="allergy[cows-milk]">
                                            <span class="checkbox-item__box">
                                                    <svg class="checkbox-item__icon" width="48" height="48" fill="#252728">
                                                        <use href="#icon-cows-milk"></use>
                                                    </svg>
                                                </span>
                                            <span class="checkbox-item__txt">Cows milk</span>
                                        </label><!-- / .checkbox-item -->
                                    </li>
                                    <li class="checkbox-list__item">
                                        <label class="checkbox-item checkbox-item--type--2">
                                            <input class="checkbox-item__field visually-hidden" type="checkbox" name="allergy[eggs]">
                                            <span class="checkbox-item__box">
                                                    <svg class="checkbox-item__icon" width="48" height="48" fill="#252728">
                                                        <use href="#icon-eggs"></use>
                                                    </svg>
                                                </span>
                                            <span class="checkbox-item__txt">Eggs</span>
                                        </label><!-- / .checkbox-item -->
                                    </li>
                                    <li class="checkbox-list__item">
                                        <label class="checkbox-item checkbox-item--type--2">
                                            <input class="checkbox-item__field visually-hidden" type="checkbox" name="allergy[peanuts]" checked disabled>
                                            <span class="checkbox-item__box">
                                                    <svg class="checkbox-item__icon" width="48" height="48" fill="#252728">
                                                        <use href="#icon-peanuts"></use>
                                                    </svg>
                                                </span>
                                            <span class="checkbox-item__txt">Peanuts</span>
                                        </label><!-- / .checkbox-item -->
                                    </li>
                                    <li class="checkbox-list__item">
                                        <label class="checkbox-item checkbox-item--type--2">
                                            <input class="checkbox-item__field visually-hidden" type="checkbox" name="allergy[fish]">
                                            <span class="checkbox-item__box">
                                                    <svg class="checkbox-item__icon" width="48" height="48" fill="#252728">
                                                        <use href="#icon-fish"></use>
                                                    </svg>
                                                </span>
                                            <span class="checkbox-item__txt">Fish</span>
                                        </label><!-- / .checkbox-item -->
                                    </li>
                                    <li class="checkbox-list__item">
                                        <label class="checkbox-item checkbox-item--type--2">
                                            <input class="checkbox-item__field visually-hidden" type="checkbox" name="allergy[shellfish]">
                                            <span class="checkbox-item__box">
                                                    <svg class="checkbox-item__icon" width="48" height="48" fill="#252728">
                                                        <use href="#icon-shellfish"></use>
                                                    </svg>
                                                </span>
                                            <span class="checkbox-item__txt">Shellfish</span>
                                        </label><!-- / .checkbox-item -->
                                    </li>
                                    <li class="checkbox-list__item">
                                        <label class="checkbox-item checkbox-item--type--2">
                                            <input class="checkbox-item__field visually-hidden" type="checkbox" name="allergy[tree-nuts]" checked disabled>
                                            <span class="checkbox-item__box">
                                                    <svg class="checkbox-item__icon" width="48" height="48" fill="#252728">
                                                        <use href="#icon-tree-nuts"></use>
                                                    </svg>
                                                </span>
                                            <span class="checkbox-item__txt">Tree nuts</span>
                                        </label><!-- / .checkbox-item -->
                                    </li>
                                    <li class="checkbox-list__item">
                                        <label class="checkbox-item checkbox-item--type--2">
                                            <input class="checkbox-item__field visually-hidden" type="checkbox" name="allergy[wheat]">
                                            <span class="checkbox-item__box">
                                                    <svg class="checkbox-item__icon" width="48" height="48" fill="#252728">
                                                        <use href="#icon-wheat"></use>
                                                    </svg>
                                                </span>
                                            <span class="checkbox-item__txt">Wheat</span>
                                        </label><!-- / .checkbox-item -->
                                    </li>
                                    <li class="checkbox-list__item">
                                        <label class="checkbox-item checkbox-item--type--2">
                                            <input class="checkbox-item__field visually-hidden" type="checkbox" name="allergy[soy]">
                                            <span class="checkbox-item__box">
                                                    <svg class="checkbox-item__icon" width="48" height="48" fill="#252728">
                                                        <use href="#icon-soy"></use>
                                                    </svg>
                                                </span>
                                            <span class="checkbox-item__txt">Soy</span>
                                        </label><!-- / .checkbox-item -->
                                    </li>
                                </ul><!-- / .checkbox-list -->
                            </div><!-- / .body-products-filter -->
                            <div class="footer-products-filter">
                                <button class="footer-products-filter__clear products-filter__clear" type="button">Clear</button>
                                <span class="footer-products-filter__count">458 Results</span>
                                <button class="footer-products-filter__button button">Apply</button>
                            </div><!-- / .footer-products-filter -->
                        </form><!-- / .products-filter -->
                    </li>
                    <li class="filter-list__item">
                        <button class="filter-list__button" type="button">
                            Diet <span class="filter-list__button-counter">1</span>
                        </button>
                        <form class="filter-list__dropdown products-filter" action="#" method="post">
                            <div class="header-products-filter">
                                <div class="header-products-filter__actions">
                                    <button class="header-products-filter__clear products-filter__clear" type="button">Clear</button>
                                    <p class="header-products-filter__title">Preferred diet</p>
                                    <button class="header-products-filter__close control-button control-button--no-txt control-button--close" type="button">
                                        <svg class="control-button__icon" width="24" height="24" fill="#252728">
                                            <use href="#icon-times"></use>
                                        </svg>
                                    </button>
                                </div>
                                <div class="header-products-filter__txt content">
                                    Applied based on your survey results. If you would like to
                                    modify this list <a href="#">re-take the survey</a> again.
                                </div>
                                <a class="header-products-filter__offer offer-card-2" href="survey.html">
                                    <div class="offer-card-2__body">
                                        <p class="offer-card-2__title">Personalize your experience</p>
                                        <span class="offer-card-2__button control-button control-button--small control-button--invert control-button--color--main">
                                                Take a Survey
                                                <svg class="control-button__icon" width="24" height="24" fill="#0A6629">
                                                    <use href="#icon-angle-rigth-light"></use>
                                                </svg>
                                            </span>
                                    </div>
                                    <picture>
                                        <source srcset="img/base/personalize-your-experience-2.webp" type="image/webp">
                                        <img class="offer-card-2__bg" src="img/base/personalize-your-experience-2.jpg" alt="">
                                    </picture>
                                </a><!-- / .offer-card-2 -->
                            </div><!-- / .header-products-filter -->
                            <div class="body-products-filter">
                                <ul class="body-products-filter__checkboxes checkboxes checkboxes--columns--2">
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="mediterranean">
                                            <span class="checkbox-2__txt">Mediterranean</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="ketogenic_vegan">
                                            <span class="checkbox-2__txt">Ketogenic Vegan</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="paleo_autoimmune">
                                            <span class="checkbox-2__txt">Paleo/Autoimmune</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="detox">
                                            <span class="checkbox-2__txt">DETOX</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2 checkbox-2--disabled">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="low_carbohydrate" checked disabled>
                                            <span class="checkbox-2__txt">Low Carbohydrate</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="anti_candida">
                                            <span class="checkbox-2__txt">Anti-Candida</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="ketogenic">
                                            <span class="checkbox-2__txt">Ketogenic</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="specific_carbohydrate_diet">
                                            <span class="checkbox-2__txt">Specific Carbohydrate</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                </ul><!-- / .checkboxes -->
                            </div><!-- / .body-products-filter -->
                            <div class="footer-products-filter">
                                <button class="footer-products-filter__clear products-filter__clear" type="button">Clear</button>
                                <span class="footer-products-filter__count">458 Results</span>
                                <button class="footer-products-filter__button button">Apply</button>
                            </div><!-- / .footer-products-filter -->
                        </form><!-- / .products-filter -->
                    </li>
                    <li class="filter-list__item">
                        <button class="filter-list__button" type="button">Exclude</button>
                        <form class="filter-list__dropdown products-filter" action="#" method="post">
                            <div class="header-products-filter">
                                <div class="header-products-filter__actions">
                                    <button class="header-products-filter__clear products-filter__clear" type="button">Clear</button>
                                    <p class="header-products-filter__title">Exclude</p>
                                    <button class="header-products-filter__close control-button control-button--no-txt control-button--close" type="button">
                                        <svg class="control-button__icon" width="24" height="24" fill="#252728">
                                            <use href="#icon-times"></use>
                                        </svg>
                                    </button>
                                </div>
                                <div class="header-products-filter__txt content">
                                    Applied based on your survey results. If you would like to
                                    modify this list <a href="#">re-take the survey</a> again.
                                </div>
                                <a class="header-products-filter__offer offer-card-2" href="survey.html">
                                    <div class="offer-card-2__body">
                                        <p class="offer-card-2__title">Personalize your experience</p>
                                        <span class="offer-card-2__button control-button control-button--small control-button--invert control-button--color--main">
                                                Take a Survey
                                                <svg class="control-button__icon" width="24" height="24" fill="#0A6629">
                                                    <use href="#icon-angle-rigth-light"></use>
                                                </svg>
                                            </span>
                                    </div>
                                    <picture>
                                        <source srcset="img/base/personalize-your-experience-2.webp" type="image/webp">
                                        <img class="offer-card-2__bg" src="img/base/personalize-your-experience-2.jpg" alt="">
                                    </picture>
                                </a><!-- / .offer-card-2 -->
                            </div><!-- / .header-products-filter -->
                            <div class="body-products-filter">
                                <ul class="body-products-filter__checkboxes checkboxes checkboxes--columns--2">
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="mediterranean">
                                            <span class="checkbox-2__txt">Mediterranean</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="ketogenic_vegan">
                                            <span class="checkbox-2__txt">Ketogenic Vegan</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="paleo_autoimmune">
                                            <span class="checkbox-2__txt">Paleo/Autoimmune</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="detox">
                                            <span class="checkbox-2__txt">DETOX</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="low_carbohydrate">
                                            <span class="checkbox-2__txt">Low Carbohydrate</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="anti_candida">
                                            <span class="checkbox-2__txt">Anti-Candida</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="ketogenic">
                                            <span class="checkbox-2__txt">Ketogenic</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2">
                                            <input class="checkbox-2__field visually-hidden" type="checkbox" name="specific_carbohydrate_diet">
                                            <span class="checkbox-2__txt">Specific Carbohydrate</span>
                                        </label><!-- / .checkbox-2 -->
                                    </li><!-- / .checkbox -->
                                </ul><!-- / .checkboxes -->
                            </div><!-- / .body-products-filter -->
                            <div class="footer-products-filter">
                                <button class="footer-products-filter__clear products-filter__clear" type="button">Clear</button>
                                <span class="footer-products-filter__count">458 Results</span>
                                <button class="footer-products-filter__button button">Apply</button>
                            </div><!-- / .footer-products-filter -->
                        </form><!-- / .products-filter -->
                    </li>
                </ul><!-- / .filter-list -->
                <form class="products__sort-form" action="#" method="get">
                    <div class="custom-select js-custom-select ui dropdown">
                        <input type="hidden" name="sorting">
                        <div class="custom-select__txt text">Best For Me & Best Reviews</div>
                        <div class="custom-select__menu menu">
                            <div class="header">Sort by</div>
                            <div class="item active" data-value="best for me & best reviews">Best For Me & Best Reviews</div>
                            <div class="item" data-value="best for me">Best For Me</div>
                            <div class="item" data-value="by reviews">By Reviews</div>
                            <div class="item" data-value="by protein">By Protein</div>
                            <div class="item" data-value="price ascending">Price Ascending</div>
                            <div class="item" data-value="price descending">Price Descending</div>
                            <div class="item" data-value="calories ascending">Calories Ascending</div>
                            <div class="item" data-value="Calories Descending">Calories Descending</div>
                        </div>
                    </div><!-- / .custom-select -->
                </form>
            </div>
            <ul class="products__list product-list js-product-list">
                <li class="product-list__item product-item">
                    <a class="product-item__img-link" href="product.html">
                        <picture>
                            <source srcset="img/base/gallery-1.webp" type="image/webp">
                            <img class="product-item__img" src="img/base/gallery-1.jpg" alt="">
                        </picture>
                    </a>
                    <div class="product-item__info">
                        <p class="product-item__label label label--best">
                            <svg class="label__icon" width="16" height="16" fill="#34A34F">
                                <use href="#icon-cap"></use>
                            </svg>
                            Best for you
                        </p><!-- / .label -->
                        <p class="product-item__name">
                            <a class="product-item__name-link" href="product.html">Very Long Name Of Morroccan Chicken With Couscous</a>
                        </p>
                        <div class="product-item__review rating-and-review">
                            <div class="rating-and-review__rating rating js-rating--readonly--true" data-rate-value="4.5"></div>
                            <p class="rating-and-review__review">4.5/5 <a href="#">(38 reviews)</a></p>
                        </div><!-- / .rating-and-review -->
                        <div class="product-item__actions">
                            <p class="product-item__price price-box">
                                <ins class="price-box__current">$11.60</ins>
                                <del class="price-box__old">$13.90</del>
                                <span class="price-box__discount">40% off</span>
                            </p><!-- / .price-box -->
                            <a class="product-item__button button button--small add-button" href="#">
                                <span class="add-button__txt-1">Add to your plan</span>
                                <span class="add-button__txt-2">1 Item Added</span>
                                <svg class="add-button__icon" width="24" height="24" fill="#fff">
                                    <use href="#icon-check-circle-stroke"></use>
                                </svg>
                            </a><!-- / .add-button -->
                        </div>
                        <ul class="product-item__badges badges">
                            <li class="badges__item" data-tippy-content="Eggs-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-1"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Gluten-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-2"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Non-GMO">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-3"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Lactose-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-4"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Soy-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-5"></use>
                                </svg>
                            </li>
                        </ul><!-- / .badges -->
                    </div>
                </li><!-- / .product-item -->
                <li class="product-list__item product-item">
                    <a class="product-item__img-link" href="product.html">
                        <picture>
                            <source srcset="img/base/gallery-2.webp" type="image/webp">
                            <img class="product-item__img" src="img/base/gallery-2.jpg" alt="">
                        </picture>
                    </a>
                    <div class="product-item__info">
                        <div class="product-item__rating-extra rating-extra js-rating--readonly--true" data-rate-value="4"></div>
                        <p class="product-item__name">
                            <a class="product-item__name-link" href="product.html">Very Long Name Of Morroccan Chicken With Couscous</a>
                        </p>
                        <div class="product-item__review rating-and-review">
                            <div class="rating-and-review__rating rating js-rating--readonly--true" data-rate-value="4.5"></div>
                            <p class="rating-and-review__review">4.5/5 <a href="#">(38 reviews)</a></p>
                        </div><!-- / .rating-and-review -->
                        <div class="product-item__actions">
                            <p class="product-item__price price-box">
                                <ins class="price-box__current">$11.60</ins>
                                <del class="price-box__old">$13.90</del>
                                <span class="price-box__discount">40% off</span>
                            </p><!-- / .price-box -->
                            <a class="product-item__button button button--small add-button" href="#">
                                <span class="add-button__txt-1">Add to your plan</span>
                                <span class="add-button__txt-2">1 Item Added</span>
                                <svg class="add-button__icon" width="24" height="24" fill="#fff">
                                    <use href="#icon-check-circle-stroke"></use>
                                </svg>
                            </a><!-- / .add-button -->
                        </div>
                        <ul class="product-item__badges badges">
                            <li class="badges__item" data-tippy-content="Eggs-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-1"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Gluten-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-2"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Non-GMO">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-3"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Lactose-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-4"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Soy-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-5"></use>
                                </svg>
                            </li>
                        </ul><!-- / .badges -->
                    </div>
                </li><!-- / .product-item -->
                <li class="product-list__item product-item">
                    <a class="product-item__img-link" href="product.html">
                        <picture>
                            <source srcset="img/base/gallery-3.webp" type="image/webp">
                            <img class="product-item__img" src="img/base/gallery-3.jpg" alt="">
                        </picture>
                    </a>
                    <div class="product-item__info">
                        <div class="product-item__rating-extra rating-extra js-rating--readonly--true" data-rate-value="4"></div>
                        <p class="product-item__name">
                            <a class="product-item__name-link" href="product.html">Very Long Name Of Morroccan Chicken With Couscous</a>
                        </p>
                        <div class="product-item__review rating-and-review">
                            <div class="rating-and-review__rating rating js-rating--readonly--true" data-rate-value="4.5"></div>
                            <p class="rating-and-review__review">4.5/5 <a href="#">(38 reviews)</a></p>
                        </div><!-- / .rating-and-review -->
                        <div class="product-item__actions">
                            <p class="product-item__price price-box">
                                <ins class="price-box__current">$11.60</ins>
                                <del class="price-box__old">$13.90</del>
                                <span class="price-box__discount">40% off</span>
                            </p><!-- / .price-box -->
                            <a class="product-item__button button button--small add-button" href="#">
                                <span class="add-button__txt-1">Add to your plan</span>
                                <span class="add-button__txt-2">1 Item Added</span>
                                <svg class="add-button__icon" width="24" height="24" fill="#fff">
                                    <use href="#icon-check-circle-stroke"></use>
                                </svg>
                            </a><!-- / .add-button -->
                        </div>
                        <ul class="product-item__badges badges">
                            <li class="badges__item" data-tippy-content="Eggs-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-1"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Gluten-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-2"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Non-GMO">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-3"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Lactose-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-4"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Soy-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-5"></use>
                                </svg>
                            </li>
                        </ul><!-- / .badges -->
                    </div>
                </li><!-- / .product-item -->
                <li class="product-list__item product-item">
                    <a class="product-item__img-link" href="product.html">
                        <picture>
                            <source srcset="img/base/gallery-4.webp" type="image/webp">
                            <img class="product-item__img" src="img/base/gallery-4.jpg" alt="">
                        </picture>
                    </a>
                    <div class="product-item__info">
                        <div class="product-item__rating-extra rating-extra js-rating--readonly--true" data-rate-value="4"></div>
                        <p class="product-item__name">
                            <a class="product-item__name-link" href="product.html">Very Long Name Of Morroccan Chicken With Couscous</a>
                        </p>
                        <div class="product-item__review rating-and-review">
                            <div class="rating-and-review__rating rating js-rating--readonly--true" data-rate-value="4.5"></div>
                            <p class="rating-and-review__review">4.5/5 <a href="#">(38 reviews)</a></p>
                        </div><!-- / .rating-and-review -->
                        <div class="product-item__actions">
                            <p class="product-item__price price-box">
                                <ins class="price-box__current">$11.60</ins>
                                <del class="price-box__old">$13.90</del>
                                <span class="price-box__discount">40% off</span>
                            </p><!-- / .price-box -->
                            <a class="product-item__button button button--small add-button" href="#">
                                <span class="add-button__txt-1">Add to your plan</span>
                                <span class="add-button__txt-2">1 Item Added</span>
                                <svg class="add-button__icon" width="24" height="24" fill="#fff">
                                    <use href="#icon-check-circle-stroke"></use>
                                </svg>
                            </a><!-- / .add-button -->
                        </div>
                        <ul class="product-item__badges badges">
                            <li class="badges__item" data-tippy-content="Eggs-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-1"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Gluten-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-2"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Non-GMO">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-3"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Lactose-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-4"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Soy-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-5"></use>
                                </svg>
                            </li>
                        </ul><!-- / .badges -->
                    </div>
                </li><!-- / .product-item -->
                <li class="product-list__item product-item">
                    <a class="product-item__img-link" href="product.html">
                        <picture>
                            <source srcset="img/base/gallery-5.webp" type="image/webp">
                            <img class="product-item__img" src="img/base/gallery-5.jpg" alt="">
                        </picture>
                    </a>
                    <div class="product-item__info">
                        <div class="product-item__rating-extra rating-extra js-rating--readonly--true" data-rate-value="4"></div>
                        <p class="product-item__name">
                            <a class="product-item__name-link" href="product.html">Very Long Name Of Morroccan Chicken With Couscous</a>
                        </p>
                        <div class="product-item__review rating-and-review">
                            <div class="rating-and-review__rating rating js-rating--readonly--true" data-rate-value="4.5"></div>
                            <p class="rating-and-review__review">4.5/5 <a href="#">(38 reviews)</a></p>
                        </div><!-- / .rating-and-review -->
                        <div class="product-item__actions">
                            <p class="product-item__price price-box">
                                <ins class="price-box__current">$11.60</ins>
                                <del class="price-box__old">$13.90</del>
                                <span class="price-box__discount">40% off</span>
                            </p><!-- / .price-box -->
                            <a class="product-item__button button button--small add-button" href="#">
                                <span class="add-button__txt-1">Add to your plan</span>
                                <span class="add-button__txt-2">1 Item Added</span>
                                <svg class="add-button__icon" width="24" height="24" fill="#fff">
                                    <use href="#icon-check-circle-stroke"></use>
                                </svg>
                            </a><!-- / .add-button -->
                        </div>
                        <ul class="product-item__badges badges">
                            <li class="badges__item" data-tippy-content="Eggs-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-1"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Gluten-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-2"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Non-GMO">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-3"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Lactose-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-4"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Soy-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-5"></use>
                                </svg>
                            </li>
                        </ul><!-- / .badges -->
                    </div>
                </li><!-- / .product-item -->
                <li class="product-list__item product-item">
                    <a class="product-item__img-link" href="product.html">
                        <picture>
                            <source srcset="img/base/gallery-6.webp" type="image/webp">
                            <img class="product-item__img" src="img/base/gallery-6.jpg" alt="">
                        </picture>
                    </a>
                    <div class="product-item__info">
                        <p class="product-item__label label label--best">
                            <svg class="label__icon" width="16" height="16" fill="#34A34F">
                                <use href="#icon-cap"></use>
                            </svg>
                            Best for you
                        </p><!-- / .label -->
                        <p class="product-item__name">
                            <a class="product-item__name-link" href="product.html">Very Long Name Of Morroccan Chicken With Couscous</a>
                        </p>
                        <div class="product-item__review rating-and-review">
                            <div class="rating-and-review__rating rating js-rating--readonly--true" data-rate-value="4.5"></div>
                            <p class="rating-and-review__review">4.5/5 <a href="#">(38 reviews)</a></p>
                        </div><!-- / .rating-and-review -->
                        <div class="product-item__actions">
                            <p class="product-item__price price-box">
                                <ins class="price-box__current">$11.60</ins>
                                <del class="price-box__old">$13.90</del>
                                <span class="price-box__discount">40% off</span>
                            </p><!-- / .price-box -->
                            <a class="product-item__button button button--small add-button" href="#">
                                <span class="add-button__txt-1">Add to your plan</span>
                                <span class="add-button__txt-2">1 Item Added</span>
                                <svg class="add-button__icon" width="24" height="24" fill="#fff">
                                    <use href="#icon-check-circle-stroke"></use>
                                </svg>
                            </a><!-- / .add-button -->
                        </div>
                        <ul class="product-item__badges badges">
                            <li class="badges__item" data-tippy-content="Eggs-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-1"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Gluten-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-2"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Non-GMO">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-3"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Lactose-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-4"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Soy-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-5"></use>
                                </svg>
                            </li>
                        </ul><!-- / .badges -->
                    </div>
                </li><!-- / .product-item -->

                <li class="product-list__item product-item">
                    <a class="product-item__img-link" href="product.html">
                        <picture>
                            <source srcset="img/base/gallery-1.webp" type="image/webp">
                            <img class="product-item__img" src="img/base/gallery-1.jpg" alt="">
                        </picture>
                    </a>
                    <div class="product-item__info">
                        <p class="product-item__label label label--best">
                            <svg class="label__icon" width="16" height="16" fill="#34A34F">
                                <use href="#icon-cap"></use>
                            </svg>
                            Best for you
                        </p><!-- / .label -->
                        <p class="product-item__name">
                            <a class="product-item__name-link" href="product.html">Very Long Name Of Morroccan Chicken With Couscous</a>
                        </p>
                        <div class="product-item__review rating-and-review">
                            <div class="rating-and-review__rating rating js-rating--readonly--true" data-rate-value="4.5"></div>
                            <p class="rating-and-review__review">4.5/5 <a href="#">(38 reviews)</a></p>
                        </div><!-- / .rating-and-review -->
                        <div class="product-item__actions">
                            <p class="product-item__price price-box">
                                <ins class="price-box__current">$11.60</ins>
                                <del class="price-box__old">$13.90</del>
                                <span class="price-box__discount">40% off</span>
                            </p><!-- / .price-box -->
                            <a class="product-item__button button button--small add-button" href="#">
                                <span class="add-button__txt-1">Add to your plan</span>
                                <span class="add-button__txt-2">1 Item Added</span>
                                <svg class="add-button__icon" width="24" height="24" fill="#fff">
                                    <use href="#icon-check-circle-stroke"></use>
                                </svg>
                            </a><!-- / .add-button -->
                        </div>
                        <ul class="product-item__badges badges">
                            <li class="badges__item" data-tippy-content="Eggs-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-1"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Gluten-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-2"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Non-GMO">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-3"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Lactose-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-4"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Soy-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-5"></use>
                                </svg>
                            </li>
                        </ul><!-- / .badges -->
                    </div>
                </li><!-- / .product-item -->
                <li class="product-list__item product-item">
                    <a class="product-item__img-link" href="product.html">
                        <picture>
                            <source srcset="img/base/gallery-2.webp" type="image/webp">
                            <img class="product-item__img" src="img/base/gallery-2.jpg" alt="">
                        </picture>
                    </a>
                    <div class="product-item__info">
                        <div class="product-item__rating-extra rating-extra js-rating--readonly--true" data-rate-value="4"></div>
                        <p class="product-item__name">
                            <a class="product-item__name-link" href="product.html">Very Long Name Of Morroccan Chicken With Couscous</a>
                        </p>
                        <div class="product-item__review rating-and-review">
                            <div class="rating-and-review__rating rating js-rating--readonly--true" data-rate-value="4.5"></div>
                            <p class="rating-and-review__review">4.5/5 <a href="#">(38 reviews)</a></p>
                        </div><!-- / .rating-and-review -->
                        <div class="product-item__actions">
                            <p class="product-item__price price-box">
                                <ins class="price-box__current">$11.60</ins>
                                <del class="price-box__old">$13.90</del>
                                <span class="price-box__discount">40% off</span>
                            </p><!-- / .price-box -->
                            <a class="product-item__button button button--small add-button" href="#">
                                <span class="add-button__txt-1">Add to your plan</span>
                                <span class="add-button__txt-2">1 Item Added</span>
                                <svg class="add-button__icon" width="24" height="24" fill="#fff">
                                    <use href="#icon-check-circle-stroke"></use>
                                </svg>
                            </a><!-- / .add-button -->
                        </div>
                        <ul class="product-item__badges badges">
                            <li class="badges__item" data-tippy-content="Eggs-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-1"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Gluten-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-2"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Non-GMO">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-3"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Lactose-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-4"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Soy-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-5"></use>
                                </svg>
                            </li>
                        </ul><!-- / .badges -->
                    </div>
                </li><!-- / .product-item -->
                <li class="product-list__item product-item">
                    <a class="product-item__img-link" href="product.html">
                        <picture>
                            <source srcset="img/base/gallery-3.webp" type="image/webp">
                            <img class="product-item__img" src="img/base/gallery-3.jpg" alt="">
                        </picture>
                    </a>
                    <div class="product-item__info">
                        <div class="product-item__rating-extra rating-extra js-rating--readonly--true" data-rate-value="4"></div>
                        <p class="product-item__name">
                            <a class="product-item__name-link" href="product.html">Very Long Name Of Morroccan Chicken With Couscous</a>
                        </p>
                        <div class="product-item__review rating-and-review">
                            <div class="rating-and-review__rating rating js-rating--readonly--true" data-rate-value="4.5"></div>
                            <p class="rating-and-review__review">4.5/5 <a href="#">(38 reviews)</a></p>
                        </div><!-- / .rating-and-review -->
                        <div class="product-item__actions">
                            <p class="product-item__price price-box">
                                <ins class="price-box__current">$11.60</ins>
                                <del class="price-box__old">$13.90</del>
                                <span class="price-box__discount">40% off</span>
                            </p><!-- / .price-box -->
                            <a class="product-item__button button button--small add-button" href="#">
                                <span class="add-button__txt-1">Add to your plan</span>
                                <span class="add-button__txt-2">1 Item Added</span>
                                <svg class="add-button__icon" width="24" height="24" fill="#fff">
                                    <use href="#icon-check-circle-stroke"></use>
                                </svg>
                            </a><!-- / .add-button -->
                        </div>
                        <ul class="product-item__badges badges">
                            <li class="badges__item" data-tippy-content="Eggs-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-1"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Gluten-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-2"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Non-GMO">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-3"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Lactose-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-4"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Soy-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-5"></use>
                                </svg>
                            </li>
                        </ul><!-- / .badges -->
                    </div>
                </li><!-- / .product-item -->
                <li class="product-list__item product-item">
                    <a class="product-item__img-link" href="product.html">
                        <picture>
                            <source srcset="img/base/gallery-4.webp" type="image/webp">
                            <img class="product-item__img" src="img/base/gallery-4.jpg" alt="">
                        </picture>
                    </a>
                    <div class="product-item__info">
                        <div class="product-item__rating-extra rating-extra js-rating--readonly--true" data-rate-value="4"></div>
                        <p class="product-item__name">
                            <a class="product-item__name-link" href="product.html">Very Long Name Of Morroccan Chicken With Couscous</a>
                        </p>
                        <div class="product-item__review rating-and-review">
                            <div class="rating-and-review__rating rating js-rating--readonly--true" data-rate-value="4.5"></div>
                            <p class="rating-and-review__review">4.5/5 <a href="#">(38 reviews)</a></p>
                        </div><!-- / .rating-and-review -->
                        <div class="product-item__actions">
                            <p class="product-item__price price-box">
                                <ins class="price-box__current">$11.60</ins>
                                <del class="price-box__old">$13.90</del>
                                <span class="price-box__discount">40% off</span>
                            </p><!-- / .price-box -->
                            <a class="product-item__button button button--small add-button" href="#">
                                <span class="add-button__txt-1">Add to your plan</span>
                                <span class="add-button__txt-2">1 Item Added</span>
                                <svg class="add-button__icon" width="24" height="24" fill="#fff">
                                    <use href="#icon-check-circle-stroke"></use>
                                </svg>
                            </a><!-- / .add-button -->
                        </div>
                        <ul class="product-item__badges badges">
                            <li class="badges__item" data-tippy-content="Eggs-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-1"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Gluten-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-2"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Non-GMO">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-3"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Lactose-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-4"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Soy-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-5"></use>
                                </svg>
                            </li>
                        </ul><!-- / .badges -->
                    </div>
                </li><!-- / .product-item -->
                <li class="product-list__item product-item">
                    <a class="product-item__img-link" href="product.html">
                        <picture>
                            <source srcset="img/base/gallery-5.webp" type="image/webp">
                            <img class="product-item__img" src="img/base/gallery-5.jpg" alt="">
                        </picture>
                    </a>
                    <div class="product-item__info">
                        <div class="product-item__rating-extra rating-extra js-rating--readonly--true" data-rate-value="4"></div>
                        <p class="product-item__name">
                            <a class="product-item__name-link" href="product.html">Very Long Name Of Morroccan Chicken With Couscous</a>
                        </p>
                        <div class="product-item__review rating-and-review">
                            <div class="rating-and-review__rating rating js-rating--readonly--true" data-rate-value="4.5"></div>
                            <p class="rating-and-review__review">4.5/5 <a href="#">(38 reviews)</a></p>
                        </div><!-- / .rating-and-review -->
                        <div class="product-item__actions">
                            <p class="product-item__price price-box">
                                <ins class="price-box__current">$11.60</ins>
                                <del class="price-box__old">$13.90</del>
                                <span class="price-box__discount">40% off</span>
                            </p><!-- / .price-box -->
                            <a class="product-item__button button button--small add-button" href="#">
                                <span class="add-button__txt-1">Add to your plan</span>
                                <span class="add-button__txt-2">1 Item Added</span>
                                <svg class="add-button__icon" width="24" height="24" fill="#fff">
                                    <use href="#icon-check-circle-stroke"></use>
                                </svg>
                            </a><!-- / .add-button -->
                        </div>
                        <ul class="product-item__badges badges">
                            <li class="badges__item" data-tippy-content="Eggs-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-1"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Gluten-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-2"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Non-GMO">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-3"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Lactose-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-4"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Soy-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-5"></use>
                                </svg>
                            </li>
                        </ul><!-- / .badges -->
                    </div>
                </li><!-- / .product-item -->
                <li class="product-list__item product-item">
                    <a class="product-item__img-link" href="product.html">
                        <picture>
                            <source srcset="img/base/gallery-6.webp" type="image/webp">
                            <img class="product-item__img" src="img/base/gallery-6.jpg" alt="">
                        </picture>
                    </a>
                    <div class="product-item__info">
                        <p class="product-item__label label label--best">
                            <svg class="label__icon" width="16" height="16" fill="#34A34F">
                                <use href="#icon-cap"></use>
                            </svg>
                            Best for you
                        </p><!-- / .label -->
                        <p class="product-item__name">
                            <a class="product-item__name-link" href="product.html">Very Long Name Of Morroccan Chicken With Couscous</a>
                        </p>
                        <div class="product-item__review rating-and-review">
                            <div class="rating-and-review__rating rating js-rating--readonly--true" data-rate-value="4.5"></div>
                            <p class="rating-and-review__review">4.5/5 <a href="#">(38 reviews)</a></p>
                        </div><!-- / .rating-and-review -->
                        <div class="product-item__actions">
                            <p class="product-item__price price-box">
                                <ins class="price-box__current">$11.60</ins>
                                <del class="price-box__old">$13.90</del>
                                <span class="price-box__discount">40% off</span>
                            </p><!-- / .price-box -->
                            <a class="product-item__button button button--small add-button" href="#">
                                <span class="add-button__txt-1">Add to your plan</span>
                                <span class="add-button__txt-2">1 Item Added</span>
                                <svg class="add-button__icon" width="24" height="24" fill="#fff">
                                    <use href="#icon-check-circle-stroke"></use>
                                </svg>
                            </a><!-- / .add-button -->
                        </div>
                        <ul class="product-item__badges badges">
                            <li class="badges__item" data-tippy-content="Eggs-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-1"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Gluten-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-2"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Non-GMO">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-3"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Lactose-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-4"></use>
                                </svg>
                            </li>
                            <li class="badges__item" data-tippy-content="Soy-free">
                                <svg width="36" height="36" fill="#87898C">
                                    <use href="#icon-badge-5"></use>
                                </svg>
                            </li>
                        </ul><!-- / .badges -->
                    </div>
                </li><!-- / .product-item -->
            </ul><!-- / .product-list -->
        </div>
    </section><!-- / .products -->
</main><!-- / .site-main .catalog-main -->

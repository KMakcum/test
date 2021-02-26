<?php
switch ($args['category']) {
    case'Meals':
        $cat = 'meals';
        break;
    case'Groceries':
        $cat = 'staples';
        break;
    case'Vitamins & Supplements':
        $cat = 'vitamins';
        break;
}
?>
<section class="product-group product-group--extra product-group--not-found">
    <div class="container">
        <div class="product-group__head head-product-group head-product-group--extended">
            <h2 class="head-product-group__title">
                <?php echo __($args['category']) ?><sup
                        class="head-product-group__quantity">0</sup>
            </h2>
            <p class="head-product-group__not-found"><?php echo __('Nothing found', '') ?></p>
            <a class="head-product-group__button button button--extra-small button--rounded button--light"
               href="<?php echo get_site_url() . '/product-category/' . $cat ?>"><?php echo __('See all', '') ?></a>
        </div>
    </div>
</section><!-- / .product-group -->
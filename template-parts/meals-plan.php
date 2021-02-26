<?php  
$prod_cat_args = array(
    'taxonomy'    => 'product_cat',
    'orderby'     => 'id',
    'hide_empty'  => false,
    'parent'      => 0
);
$maels_panel_ids = [];
$woo_categories  = get_categories( $prod_cat_args );
$maels_panelcat  = carbon_get_theme_option("op_maels_panelcat");

foreach ( (array)$maels_panelcat as $item ) {
    $maels_panel_ids[] = $item['id'];
}
 
foreach ( $woo_categories as $woo_cat ) {

    if ( in_array( $woo_cat->term_id, $maels_panel_ids ) ) {
     
        $new_products = op_help()->subscriptions->sort_items_by_category( WC()->cart->get_cart(), $woo_cat );

        if ( ! empty( $new_products ) ) {
        ?>

            <div class="main-footer__meals-panel meals-panel">
                <div class="meals-panel__head">
                    <div class="meals-panel__trigger-and-txt">
                        <button class="meals-panel__trigger" type="button">
                            <svg width="16" height="16" fill="#0A6629">
                                <use href="#icon-angle-down"></use>
                            </svg>
                        </button>
                        <p class="meals-panel__txt">
                            <span class="meals-panel__quantity">12 meals plan</span>
                            <span class="meals-panel__discount">+ FREE Delivery + 3% off</span>
                        </p>
                    </div>
                    <ul class="meals-panel__list meals-list">
                        
        <?php
        }

        foreach ( (array)$new_products as $new_product ) {

            $_product = $new_product['data'];
            $img_url_thumb = ( empty( get_the_post_thumbnail_url( $_product->get_id(), 'thumbnail' ) ) ) ? site_url() . '/wp-content/uploads/woocommerce-placeholder.png' : get_the_post_thumbnail_url( $_product->get_id(), 'thumbnail' ) ;
        ?>

            <li class="meals-list__item">
                <picture>
                    <img class="meals-list__img" src="<?php echo $img_url_thumb; ?>" alt="<?php echo get_the_title( $_product->get_id() ); ?>">
                </picture>
            </li>

        <?php
        }

        if ( ! empty( $new_products ) ) {
        ?>

                    </ul><!-- / .meals-list -->
                    <div class="meals-panel__message message message--inline">
                        <svg class="message__icon" width="24" height="24" fill="#FFB300">
                            <use href="#icon-info"></use>
                        </svg>
                        <p class="message__txt">Don’t forget to buy groceries!</p>
                    </div>
                </div>
            </div><!-- / .meals-panel -->

        <?php
        }
    }
} 

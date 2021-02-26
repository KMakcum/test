<?php  
global $product, $variation, $cached_product;

$is_variation = ( ! empty( $variation ) ) ? true : false;
$this_object  = function () use ( $is_variation, $product, $variation ) {

	if ( $is_variation ) {
		return $variation;
	}

	return $product;
};
?>

<p class="footer-product-details__price price-box price-box--big">
    <?php
	    if ( '' === $cached_product['_price'] ) :

	        $price = apply_filters( 'woocommerce_empty_price_html', '', $this_object() );
	        echo '<ins class="price-box__current">' . $price . '</ins>';

	    elseif ( $this_object()->is_on_sale() ) : 

	        echo '<ins class="price-box__current">' . wc_price( $cached_product['_sale_price'] ) . $this_object()->get_price_suffix() . '</ins>';
	        echo '<del class="price-box__old">' . wc_price( $cached_product['_regular_price'] ) . $this_object()->get_price_suffix() . '</del>';
	        echo '<span class="price-box__discount">' . get_discount( $cached_product['_regular_price'], $cached_product['_sale_price'] ) . '% off</span>';

	    else :

	        $price = wc_price( $cached_product['_price'] ) . $this_object()->get_price_suffix();
	        echo '<ins class="price-box__current">' . $price . '</ins>';

	    endif;
    ?>
</p>
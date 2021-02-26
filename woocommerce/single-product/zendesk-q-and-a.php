<?php
global $product, $variation;

$terms = get_the_terms($product->get_id(), 'product_cat');
//$nterms = get_the_terms($product->ID, 'product_tag');
foreach ($terms as $term) {
    $product_cat_id = $term->term_id;
    $product_cat_name = $term->slug;
    break;
}
?>

<section class="product-card__section zendesk-q-and-a" data-product-cat= <?php echo $product_cat_name ?>>
    <h2 class="zendesk-q-and-a__title"></h2>
    <ul class="zendesk-q-and-a__accordion accordion-extra accordion-extra--contrast">
    </ul>
</section>


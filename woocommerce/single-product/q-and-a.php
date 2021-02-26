<?php 
global $product, $variation;

$is_variation = ! empty( $variation );
$this_object  = function () use ( $is_variation, $product, $variation ) {

    if ( $is_variation ) {
        return $variation;
    }

    return $product;
};
$qna_list = get_post_meta( $this_object()->get_id(), "op_qna_list" );

if ( ! empty( $qna_list ) ) :

$qna_list = array_map( 'get_post', $qna_list );
?>  

    <section class="product-card__section q-and-a">
        <h2 class="q-and-a__title">Q&A</h2>
        <ul class="q-and-a__accordion accordion-extra accordion-extra--contrast">

            <?php foreach ( $qna_list as $qna_key => $qna_item ) : ?>

                <li class="accordion-extra__item">
                    <p class="accordion-extra__header">
                        <?php echo esc_html( $qna_item->post_title ); ?>
                    </p>
                    <div class="accordion-extra__content content content--small">
                        <?php echo apply_filters( "the_content", $qna_item->post_content ); ?>
                    </div>
                </li>

            <?php endforeach; ?>

        </ul>
    </section>

<?php endif;
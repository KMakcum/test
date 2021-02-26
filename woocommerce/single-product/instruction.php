<?php
global $product, $variation, $cached_product;

//$is_variation = ( ! empty( $variation ) ) ? true : false;
//$this_object  = function () use ( $is_variation, $product, $variation ) {
//
//	if ( $is_variation ) {
//		return $variation;
//	}
//
//	return $product;
//};

$instruction_list = $cached_product['op_prepare_instructions'];
$time_for_prepare = $cached_product['op_preparing_time'];
$microwave        = $cached_product['op_microwave'];

if ( ! empty( $instruction_list ) ) :

	$instructions_list = explode( '|', $instruction_list );
	?>

    <section class="product-card__section instruction">
        <div class="instruction__head">
            <h2 class="instruction__title">Preparation instruction</h2>
            <ul class="instruction__meal-overview meal-overview">
				<?php if ( ! empty( $time_for_prepare ) ) { ?>
                    <li class="meal-overview__item" data-tippy-content="Badge name">
                        <svg class="meal-overview__icon" width="16" height="16" fill="#252728">
                            <use xlink:href="#icon-clock"></use>
                        </svg>
						<?php echo esc_html( $time_for_prepare ); ?> min
                    </li>
				<?php } ?>
				<?php if ( ! empty( $microwave ) && (int) $microwave ) { ?>
                    <li class="meal-overview__item" data-tippy-content="Badge name">
                        <svg class="meal-overview__icon" width="16" height="16" fill="#252728">
                            <use xlink:href="#icon-microvawe"></use>
                        </svg>
                        Microwave
                    </li>
				<?php } ?>
            </ul>
        </div>
        <div class="instruction__body">
            <ol class="instruction__numbered-list numbered-list">

				<?php foreach ( $instructions_list as $key => $instruction ) : ?>

                    <li class="numbered-list__item">
                        <span class="numbered-list__number"><?php echo $key + 1; ?></span>
                        <div class="numbered-list__txt content">
							<?php echo apply_filters( "the_content",
								$instruction ); ?>
                        </div>
                    </li>

				<?php endforeach; ?>

            </ol>
        </div>
    </section>

<?php endif;
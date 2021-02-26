<?php
global $product, $cached_product;

$description = $cached_product['post_content'];
if ( $description ) :
    ?>
    <div class="description__left nutrition__left content--small">
        <p class="description__cont"><?php echo $description; ?></p>
    </div>
    <?php
endif;
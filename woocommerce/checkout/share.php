<?php
$title = 'Meal Plan';
$current_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<section class="checkout-thanks__section share-meal-plan">
    <h2 class="share-meal-plan__title">Share Your Meal Plan With Friends</h2>
    <ul class="share-meal-plan__social social-links-2 social-links-2--big">
        <li class="social-links-2__item">
            <a class="social-links-2__link social-links-2__link--facebook" href="http://www.facebook.com/share.php?u=<?php echo $current_url; ?>&title=<?php echo $title; ?>" target="_blank" rel="noopener nofollow">
                <svg class="social-links-2__icon" width="24" height="24" fill="#87898C">
                    <use href="#icon-facebook"></use>
                </svg>
                <span class="social-links-2__tooltip">Facebook</span>
            </a>
        </li>
        <li class="social-links-2__item">
            <a class="social-links-2__link social-links-2__link--twitter" href="http://twitter.com/home?status=<?php echo $title; ?>+<?php echo $current_url; ?>" target="_blank" rel="noopener nofollow">
                <svg class="social-links-2__icon" width="24" height="24" fill="#87898C">
                    <use href="#icon-twitter"></use>
                </svg>
                <span class="social-links-2__tooltip">Twitter</span>
            </a>
        </li>
        <!-- <li class="social-links-2__item">
            <a class="social-links-2__link social-links-2__link--instagram" href="#" target="_blank" rel="noopener nofollow">
                <svg class="social-links-2__icon" width="24" height="24" fill="#87898C">
                    <use href="#icon-instagram"></use>
                </svg>
                <span class="social-links-2__tooltip">Instagram</span>
            </a>
        </li> -->
        <li class="social-links-2__item">
            <a class="social-links-2__link social-links-2__link--pinterest" onclick="window.open('https://www.pinterest.com/pin/create/button/?url=<?php echo $current_url; ?>&media={<?php echo get_template_directory_uri(); ?>/assets/img/base/logo.png}&description=<?php echo $title; ?>');" href="javascript:void(0);" target="_blank" rel="noopener nofollow">
                <svg class="social-links-2__icon" width="24" height="24" fill="#87898C">
                    <use href="#icon-pinterest"></use>
                </svg>
                <span class="social-links-2__tooltip">Pinterest</span>
            </a>
        </li>
    </ul>
</section>
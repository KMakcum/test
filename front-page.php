<?php

/**
 * Template Name: Front Page
 */

get_header(); 
?>

    <main class="site-main home-page-main">

        <?php the_title( '<h1 class="visually-hidden">', '</h1>' ); ?>

        <?php get_template_part('template-parts/front-page/main-slider'); ?>

        <?php get_template_part('template-parts/front-page/how-it-works'); ?>

        <?php get_template_part('template-parts/front-page/about-us'); ?>

        <?php get_template_part('template-parts/front-page/products'); ?>

        <?php get_template_part('template-parts/front-page/reviews'); ?>

        <?php get_template_part('template-parts/front-page/ready-to-start'); ?>

    </main>

<?php 
get_footer(); 
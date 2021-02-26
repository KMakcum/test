<?php

/**
 * Template Name: Our Story
 */

get_header(); 
?>

    <main class="site-main our-story-main">

        <?php get_template_part('template-parts/our-story/main'); ?>

        <?php get_template_part('template-parts/our-story/about-us'); ?>

        <?php get_template_part('template-parts/our-story/why-we-do-it'); ?>

        <?php get_template_part('template-parts/our-story/our', 'difference'); ?>

        <?php get_template_part('template-parts/our-story/our', 'philosophy'); ?>

        <?php get_template_part('template-parts/our-story/our', 'team'); ?>

        <?php get_template_part('template-parts/our-story/our', 'approach'); ?>

        <?php get_template_part('template-parts/our-story/convenience'); ?>

    </main>

<?php 
get_footer(); 
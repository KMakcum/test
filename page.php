<?php
get_header(); 

    while ( have_posts() ) :

      	the_post();

      	if ( is_woocommerce() || is_cart() || is_checkout() ) :
		?>

			<?php the_content(); ?>

  		<?php else: ?>

  			<main class="site-main">
		        <section class="info-page">
		            <div class="container">
		                <div class="info-page__content content content--font-weight--light">
		                    <h1><?php the_title(); ?></h1>

		                    <?php the_content(); ?>

		                </div>
		            </div>
		        </section>
		    </main>

  		<?php
  		endif;

    endwhile; // End of the loop.

get_footer(); 
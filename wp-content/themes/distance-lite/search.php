<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package distance lite
 */

get_header(); ?>
<div id="wrapper" class="animatedParent animateOnce">
    <div id="contentwrapper" class="animated fadeIn">
  <h1 class="entry-title">
		<?php printf( esc_html__( 'Search Results for: %s', 'distance-lite' ), '<span>' . get_search_query() . '</span>' ); ?>
  </h1>

  <div id="contentfull">
    	<?php if (have_posts()) : ?>

          <?php
              while ( have_posts() ) : the_post();
                  get_template_part( 'content', get_post_format() );
              endwhile;
          ?>
          <?php the_posts_pagination(); ?>
    	<?php else : ?>
    		<p class="center">
      			<?php esc_html_e( 'No results.', 'distance-lite' ); ?>
    		</p>
    	<?php endif; ?>
  	</div>
</div>
</div>
<?php get_footer(); ?>

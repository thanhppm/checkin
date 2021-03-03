<?php
/**
 * Template Name: Full Width
 *
 * @package distance lite
 */

get_header(); ?>

<div id="wrapper" class="animatedParent animateOnce">
  <div id="innerwhitebg" class="animated fadeInLeft"> </div>
    <div id="contentwrapper" class="animated fadeIn">
      <div class="pagetop">
          <h1 class="entry-title">
              <?php the_title(); ?>
          </h1>
          <?php the_excerpt(); ?>
      </div>
    <div id="contentfull">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <div class="post" id="post-<?php the_ID(); ?>">
        <div class="entry">
          <?php the_content(); ?>
          <?php edit_post_link(); ?>
          <?php wp_link_pages(array('before' => '<p><strong>'. esc_html__( 'Pages:', 'distance-lite' ) .'</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
          <?php comments_template(); ?>
        </div>
      </div>
      <?php endwhile; endif; ?>
    </div>
  </div>
</div>
<?php get_footer(); ?>

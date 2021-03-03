<?php
/**
 * The Header for our theme.
 *
 *
 * @package distance lite
 */
?>
<!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale = 1.0, maximum-scale=2.0, user-scalable=yes" />
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php if ( function_exists( 'wp_body_open' ) ) {
    wp_body_open();
} else {
    do_action( 'wp_body_open' );
} ?>

	<div id="container">

		<div id="header">

              <div id="logo">
        				<?php the_custom_logo(); ?>
        				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
        					<h1 class="site-title">
          						<?php bloginfo( 'name' ); ?>
        					</h1>
        				</a>
      				</div>

							<?php if ( has_nav_menu( 'main-menu' ) ) {
	    				wp_nav_menu(
							array(
								'theme_location' => 'main-menu',
								'container_id'   => 'mainmenu',
								'menu_class' 	 => 'superfish sf-menu',
								'fallback_cb'	 => false
							)
						);
	  					} ?>


							<div class="rightmenu">
										<?php if ( has_nav_menu( 'social' ) ) {
											wp_nav_menu(
											array(
												'theme_location'  => 'social',
												'container'       => 'div',
												'container_id'    => 'menu-social',
												'container_class' => 'menu',
												'menu_id'         => 'menu-social-items',
												'menu_class'      => 'menu-items',
												'depth'           => 1,
												'link_before'     => '<span class="screen-reader-text">',
												'link_after'      => '</span>',
												'fallback_cb'     => '',
											)
										);
										} ?>
					</div>


	</div>
	<?php if ( has_nav_menu( 'main-menu' ) ) {

wp_nav_menu(
	array(
		'theme_location' => 'main-menu',
		'container_class'   => 'mmenu',
		'menu_class' 	 => 'navmenu',
		'fallback_cb'	 => false
)
);
	} ?>

	<?php if (is_front_page()): ?>
		<div class="animatedParent animateOnce">
		<div class="headerbg animated fadeInLeft">
				<?php if ( is_active_sidebar( 'sidebar-2' )  ) : ?>
						<div id="topwidget" class="animated fadeIn">
							<?php dynamic_sidebar( 'sidebar-2' ); ?>
						</div>
				<?php endif ?>
				<?php if (has_header_image()) : ?>
					<img class="headerimage animated fadeIn" src="<?php header_image(); ?>" alt="<?php bloginfo( 'name' ); ?>" />
				<?php endif ?>
			</div>
		</div>
	</div>
	<?php endif ?>

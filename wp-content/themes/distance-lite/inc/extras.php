<?php
/**
 * Extra functions for this theme.
 *
 * @package distance lite
 */

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * @param array $args Configuration arguments.
 * @return array
 */
function distance_page_menu_args( $args ) {
	if ( ! isset( $args['show_home'] ) )
		$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'distance_page_menu_args' );

/**
* Defines new blog excerpt length and link text.
*/
if (!is_admin()) {
	function distance_new_excerpt_length($length) {
		return 38;
	}
	add_filter('excerpt_length', 'distance_new_excerpt_length');

	function distance_new_excerpt_more($more) {
		global $post;
		return '';
	}
	add_filter('excerpt_more', 'distance_new_excerpt_more');
}

/**
* Manages display of archive titles.
*/
function distance_get_the_archive_title( $title ) {
   if ( is_category() ) {
        $title = single_cat_title( '', false );
    } elseif ( is_tag() ) {
        $title = single_tag_title( '', false );
    } elseif ( is_author() ) {
        $title = '<span class="vcard">' . get_the_author() . '</span>';
    } elseif ( is_year() ) {
        $title = get_the_date( _x( 'Y', 'yearly archives date format','distance-lite' ) );
    } elseif ( is_month() ) {
        $title = get_the_date( _x( 'F Y', 'monthly archives date format','distance-lite' ) );
    } elseif ( is_day() ) {
        $title = get_the_date( _x( 'F j, Y', 'daily archives date format','distance-lite' ) );
    } elseif ( is_post_type_archive() ) {
        $title = post_type_archive_title( '', false );
    } elseif ( is_tax() ) {
        $title = single_term_title( '', false );
    } else {
        $title = esc_html__( 'Archives', 'distance-lite' );
    }
    return $title;
};
add_filter( 'get_the_archive_title', 'distance_get_the_archive_title', 10, 1 );

add_theme_support( 'html5', array( 'gallery', 'caption' ) );

function distance_skip_link() {
	echo '<a class="skip-link screen-reader-text" href="#contentwrapper">' . esc_html__( 'Skip to the content', 'distance-lite' ) . '</a>';
}
add_action( 'wp_body_open', 'distance_skip_link', 5 );

// display custom admin notice
function distance_admin_notice__success() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php esc_html_e( 'Thanks for installing Distance Lite! Want more features?','distance-lite'); ?> <a href="https://vivathemes.com/wordpress-theme/distance/" target="blank"><?php esc_html_e( 'Check out the Pro Version  &#8594;','distance-lite'); ?></a></p>
    </div>
    <?php
}
add_action( 'admin_notices', 'distance_admin_notice__success' );

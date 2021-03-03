<?php
/**
 *
 * @package distance lite
 */

/**
 * Setup the WordPress core custom header feature.
 */
function distance_custom_header_setup() {
	add_theme_support( 'custom-header', apply_filters( 'distance_custom_header_args', array(
		'width'         => 600,
		'height'        => 410,
		'uploads'       => true,
		'default-text-color'     => '333333',
		'wp-head-callback'       => 'distance_header_style',
	) ) );
}
add_action( 'after_setup_theme', 'distance_custom_header_setup' );

if ( ! function_exists( 'distance_header_style' ) ) :
        function distance_header_style() {
                wp_enqueue_style( 'distance-style', get_stylesheet_uri() );
                $header_text_color = get_header_textcolor();
                $position = "absolute";
                $clip ="rect(1px, 1px, 1px, 1px)";
                if ( ! display_header_text() ) {
                        $custom_css = '.site-title, .site-description {
                                position: '.$position.';
                                clip: '.$clip.';
                        }';
                } else{

                        $custom_css = 'h1.site-title, h2.site-description  {
                                color: #' . $header_text_color . ';
                        }';
                }
                wp_add_inline_style( 'distance-style', $custom_css );
        }
        add_action( 'wp_enqueue_scripts', 'distance_header_style' );

endif;

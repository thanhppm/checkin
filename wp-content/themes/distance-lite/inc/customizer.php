<?php
/**
 * Theme Customizer
 *
 * @package distance lite
 */
function distance_customize_register($wp_customize){

	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
}
function distance_customize_preview_js() {
	wp_enqueue_script( 'distance_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20130508', true );
}
add_action( 'customize_preview_init', 'distance_customize_preview_js' );

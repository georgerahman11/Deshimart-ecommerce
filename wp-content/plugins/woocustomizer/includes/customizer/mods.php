<?php
/**
 * Functions used to implement options
 *
 * @package Customizer Library WooCommerce Designer
 */

/**
 * Enqueue Google Fonts
 */
function woocustomizer_customizer_fonts() {

	// Font options
	$fonts = array(
		get_theme_mod( 'wcz-body-font', woocustomizer_library_get_default( 'wcz-body-font' ) ),
	);

	$font_uri = woocustomizer_library_get_google_font_uri( $fonts );

	// Load Google Fonts
	wp_enqueue_style( 'woocustomizer_customizer_fonts', $font_uri, array(), null, 'screen' );

}
add_action( 'wp_enqueue_scripts', 'woocustomizer_customizer_fonts' );

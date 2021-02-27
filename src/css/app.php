<?php
/**
 * @package snow-monkey-mega-menu
 * @author inc2734
 * @license GPL-2.0+
 */

use Framework\Helper;
use Inc2734\WP_Customizer_Framework\Style;
use Inc2734\WP_Customizer_Framework\Color;

if ( ! Helper::is_ie() ) {
	return;
}

$accent_color = get_theme_mod( 'accent-color' );
if ( ! $accent_color ) {
	return;
}

Style::register(
	[
		'.snow-monkey-mega-menu > .c-navbar__submenu',
		'.snow-monkey-mega-menu > .c-navbar__submenu::before',
	],
	'background-color: ' . $accent_color
);

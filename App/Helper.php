<?php
/**
 * @package snow-monkey-mega-menu
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\MegaMenu\App;

class Helper {

	/**
	 * Return all mega menus.
	 *
	 * @return array
	 */
	public static function get_all_mega_menus() {
		return array(
			''            => __( 'None', 'snow-monkey-mega-menu' ),
			'mega-menu-1' => __( 'Mega Menu 1', 'snow-monkey-mega-menu' ),
			'mega-menu-2' => __( 'Mega Menu 2', 'snow-monkey-mega-menu' ),
			'mega-menu-3' => __( 'Mega Menu 3', 'snow-monkey-mega-menu' ),
			'mega-menu-4' => __( 'Mega Menu 4', 'snow-monkey-mega-menu' ),
		);
	}
}

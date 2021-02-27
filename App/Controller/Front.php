<?php
/**
 * @package snow-monkey-mega-menu
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\MegaMenu\App\Controller;

use Framework\Helper;

class Front {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'walker_nav_menu_start_el', [ $this, '_item' ], 10, 3 );
		add_filter( 'wp_nav_menu_objects', [ $this, '_add_classes' ] );
		add_action( 'wp_enqueue_scripts', [ $this, '_enqueue_assets' ] );
		add_action( 'inc2734_wp_customizer_framework_load_styles', [ $this, '_load_styles' ] );
	}

	/**
	 * Add the post thumbnail to the nav item.
	 *
	 * @param string  $item_output The menu item's starting HTML output.
	 * @param WP_Post $item        Menu item data object.
	 * @param int     $depth       Depth of menu item. Used for padding.
	 * @return string
	 */
	public function _item( $item_output, $item, $depth ) {
		if ( 1 !== $depth ) {
			return $item_output;
		}

		$parent_item_id = $item->menu_item_parent;
		if ( ! $parent_item_id ) {
			return $item_output;
		}

		$mega_menu = get_post_meta( $parent_item_id, 'snow-monkey-mega-menu', true );
		if (
			'mega-menu-1' !== $mega_menu
			&& 'mega-menu-4' !== $mega_menu
		) {
			return $item_output;
		}

		$item_id = $item->object_id;
		if ( ! $item_id ) {
			return $item_output;
		}

		$thumbnail = '';
		if ( 'post_type' === $item->type ) {
			$thumbnail = get_the_post_thumbnail( $item_id );
		} elseif ( 'post_type_archive' === $item->type ) {
			$thumbnail = \Framework\Helper::get_the_post_type_archive_thumbnail( $item->object );
		} elseif ( 'taxonomy' === $item->type ) {
			$taxonomy = get_taxonomy( $item->object );
			if ( 'category' === $item->object || is_taxonomy_hierarchical( $taxonomy ) ) {
				$term      = get_term( $item_id, $taxonomy->name );
				$thumbnail = \Framework\Helper::get_the_category_thumbnail( $term );
			}
		}

		return preg_replace(
			'|(<a [^>]*?>)(.*?)(</a>)|ms',
			'$1<div class="snow-monkey-mega-menu__figure">' . $thumbnail . '</div><div class="snow-monkey-mega-menu__label">$2</div>$3',
			$item_output
		);
	}

	/**
	 * Add classes to the menu item.
	 *
	 * @param array $sorted_menu_items The menu items, sorted by each menu item's menu order.
	 * @return array
	 */
	public function _add_classes( $sorted_menu_items ) {
		foreach ( $sorted_menu_items as $index => $item ) {
			$mega_menu = get_post_meta( $item->ID, 'snow-monkey-mega-menu', true );
			if ( ! $mega_menu ) {
				continue;
			}

			$item->classes[]             = 'snow-monkey-mega-menu';
			$item->classes[]             = 'snow-monkey-mega-menu--' . $mega_menu;
			$item->classes               = array_unique( $item->classes );
			$sorted_menu_items[ $index ] = $item;
		}
		return $sorted_menu_items;
	}

	/**
	 * Enqueue assets.
	 */
	public function _enqueue_assets() {
		wp_enqueue_style(
			'snow-monkey-mega-menu',
			SNOW_MONKEY_MEGA_MENU_URL . '/dist/css/app.css',
			[ Helper::get_main_style_handle() ],
			filemtime( SNOW_MONKEY_MEGA_MENU_PATH . '/dist/css/app.css' )
		);
	}

	/**
	 * Load PHP files for styles
	 */
	public function _load_styles() {
		include_once( SNOW_MONKEY_MEGA_MENU_PATH . '/dist/css/app.php' );
	}
}

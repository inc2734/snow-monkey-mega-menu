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
		add_filter( 'walker_nav_menu_start_el', [ $this, '_item' ], 10, 4 );
		add_filter( 'wp_nav_menu_objects', [ $this, '_add_classes' ], 10, 2 );
		add_action( 'wp_enqueue_scripts', [ $this, '_enqueue_assets' ] );
		add_action( 'inc2734_wp_customizer_framework_load_styles', [ $this, '_load_styles' ] );
	}

	/**
	 * Add the post thumbnail to the nav item.
	 *
	 * @param string   $item_output The menu item's starting HTML output.
	 * @param WP_Post  $item        Menu item data object.
	 * @param int      $depth       Depth of menu item. Used for padding.
	 * @param stdClass $args        An object of wp_nav_menu() arguments.
	 * @return string
	 */
	public function _item( $item_output, $item, $depth, $args ) {
		if ( 'global-nav' !== $args->theme_location ) {
			return $item_output;
		}

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
		if ( 'post_type_archive' === $item->type && 'product' === $item->object ) {
			$post_type_object = get_post_type_object( $item->object );
			$image_url        = Helper\Page_Header\WooCommerce_Archive_Page_Header::get_image_url( $post_type_object );
		} elseif ( 'taxonomy' === $item->type && in_array( $item->object, [ 'product_cat', 'product_tag' ], true ) ) {
			$wp_term   = get_term( $item_id, $item->object );
			$image_url = Helper\Page_Header\WooCommerce_Term_Page_Header::get_image_url( $wp_term );
		} elseif ( 'post_type' === $item->type && 'product' === $item->object ) {
			$wp_post   = get_post( $item_id );
			$image_url = Helper\Page_Header\WooCommerce_Single_Page_Header::get_image_url( $wp_post );
		} elseif ( 'post_type' === $item->type ) {
			$wp_post   = get_post( $item_id );
			$image_url = Helper\Page_Header\Singular_Page_Header::get_image_url( $wp_post );
		} elseif ( 'taxonomy' === $item->type ) {
			$wp_term   = get_term( $item_id, $item->object );
			$image_url = Helper\Page_Header\Term_Page_Header::get_image_url( $wp_term );
		} elseif ( 'post_type_archive' === $item->type ) {
			$post_type_object = get_post_type_object( $item->object );
			$image_url        = Helper\Page_Header\Archive_Page_Header::get_image_url( $post_type_object );
		} else {
			$image_url = Helper\Page_Header\Default_Page_Header::get_image_url( null );
		}

		if ( ! empty( $image_url ) ) {
			$thumbnail = sprintf(
				'<img src="%1$s" alt="">',
				esc_url( $image_url )
			);
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
	 * @param array    $sorted_menu_items The menu items, sorted by each menu item's menu order.
	 * @param stdClass $args              An object containing wp_nav_menu() arguments.
	 * @return array
	 */
	public function _add_classes( $sorted_menu_items, $args ) {
		if ( 'global-nav' !== $args->theme_location ) {
			return $sorted_menu_items;
		}

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

		wp_enqueue_script(
			'snow-monkey-mega-menu',
			SNOW_MONKEY_MEGA_MENU_URL . '/dist/js/app.js',
			[ Helper::get_main_script_handle() ],
			filemtime( SNOW_MONKEY_MEGA_MENU_PATH . '/dist/js/app.js' ),
			true
		);
	}

	/**
	 * Load PHP files for styles
	 */
	public function _load_styles() {
		include_once( SNOW_MONKEY_MEGA_MENU_PATH . '/dist/css/app.php' );
	}
}

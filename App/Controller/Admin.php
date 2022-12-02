<?php
/**
 * @package snow-monkey-mega-menu
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\MegaMenu\App\Controller;

use Snow_Monkey\Plugin\MegaMenu\App;

class Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, '_add_custom_fields' ), 10, 3 );
		add_action( 'wp_update_nav_menu_item', array( $this, '_update' ), 10, 2 );
	}

	/**
	 * Fires just before the move buttons of a nav menu item in the menu editor.
	 *
	 * @param int     $item_id Menu item ID.
	 * @param WP_Post $item    Menu item data object.
	 * @param int     $depth   Depth of menu item. Used for padding.
	 */
	public function _add_custom_fields( $item_id, $item, $depth ) {
		if ( 0 !== $depth ) {
			return;
		}

		$mega_menu = get_post_meta( $item_id, 'snow-monkey-mega-menu', true );

		$options = App\Helper::get_all_mega_menus();
		?>
		<p class="description">
			<label for="snow-monkey-mega-menu[<?php echo esc_attr( $item_id ); ?>]"><?php esc_html_e( 'Mega menu setting', 'snow-monkey-mega-menu' ); ?></label>
			<select name="snow-monkey-mega-menu[<?php echo esc_attr( $item_id ); ?>]" class="widefat">
				<?php foreach ( $options as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $mega_menu ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="description"><?php esc_html_e( 'This is only reflected when menu Location is set to global navigation.', 'snow-monkey-mega-menu' ); ?></span>

			<?php wp_nonce_field( 'snow-monkey-mega-menu', 'snow-monkey-mega-menu-nonce' ); ?>
		</p>
		<?php
	}

	/**
	 * Fires after a navigation menu item has been updated.
	 *
	 * @param int $menu_id         ID of the updated menu.
	 * @param int $menu_item_db_id ID of the updated menu item.
	 */
	public function _update( $menu_id, $menu_item_db_id ) {
		$nonce = filter_input( INPUT_POST, 'snow-monkey-mega-menu-nonce' );
		if ( ! $nonce ) {
			return;
		}

		$verify_nonce = wp_verify_nonce( $nonce, 'snow-monkey-mega-menu' );
		if ( ! $verify_nonce ) {
			return;
		}

		$mega_menu = filter_input(
			INPUT_POST,
			'snow-monkey-mega-menu',
			FILTER_DEFAULT,
			array(
				'flags' => FILTER_REQUIRE_ARRAY,
			)
		);

		if ( $mega_menu && isset( $mega_menu[ $menu_item_db_id ] ) ) {
			update_post_meta( $menu_item_db_id, 'snow-monkey-mega-menu', $mega_menu[ $menu_item_db_id ] );
		} else {
			delete_post_meta( $menu_item_db_id, 'snow-monkey-mega-menu' );
		}
	}
}

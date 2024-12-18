<?php
/**
 * Plugin name: Snow Monkey Mega Menu
 * Version: 3.4.0
 * Descriptions: This plugin turns Snow Monkey's global navigation into a mega menu.
 * Tested up to: 6.7
 * Requires at least: 6.7
 * Requires PHP: 7.4
 * Requires Snow Monkey: 19.1.5
 * Description:
 * Author: inc2734
 * Author URI: https://2inc.org
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: snow-monkey-mega-menu
 *
 * @package snow-monkey-mega-menu
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\MegaMenu;

use Snow_Monkey\Plugin\MegaMenu\App\Controller;

define( 'SNOW_MONKEY_MEGA_MENU_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'SNOW_MONKEY_MEGA_MENU_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

class Bootstrap {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, '_bootstrap' ) );
	}

	/**
	 * Bootstrap.
	 */
	public function _bootstrap() {
		add_action( 'init', array( $this, '_load_textdomain' ) );

		new App\Updater();

		$theme = wp_get_theme( get_template() );
		if ( 'snow-monkey' !== $theme->template && 'snow-monkey/resources' !== $theme->template ) {
			add_action(
				'admin_notices',
				function () {
					?>
					<div class="notice notice-warning is-dismissible">
						<p>
							<?php esc_html_e( '[Snow Monkey Mega Menu] Needs the Snow Monkey.', 'snow-monkey-mega-menu' ); ?>
						</p>
					</div>
					<?php
				}
			);
			return;
		}

		$data = get_file_data(
			__FILE__,
			array(
				'RequiresSnowMonkey' => 'Requires Snow Monkey',
			)
		);

		if (
			isset( $data['RequiresSnowMonkey'] ) &&
			version_compare( $theme->get( 'Version' ), $data['RequiresSnowMonkey'], '<' )
		) {
			add_action(
				'admin_notices',
				function () use ( $data ) {
					?>
					<div class="notice notice-warning is-dismissible">
						<p>
							<?php
							echo esc_html(
								sprintf(
									// translators: %1$s: version.
									__(
										'[Snow Monkey Mega Menu] Needs the Snow Monkey %1$s or more.',
										'snow-monkey-mega-menu'
									),
									'v' . $data['RequiresSnowMonkey']
								)
							);
							?>
						</p>
					</div>
					<?php
				}
			);
			return;
		}

		/**
		 * Only existing header layouts should be supported,
		 * as original header layouts may cause unexpected problems.
		 */
		add_action(
			'after_setup_theme',
			function () {
				$header_layout        = get_theme_mod( 'header-layout' );
				$valid_header_layouts = array(
					false,
					'1row',
					'2row',
					'center',
					'simple',
					'left',
				);
				if ( ! in_array( $header_layout, $valid_header_layouts, true ) ) {
					return;
				}

				new Controller\Admin();
				new Controller\Front();
			}
		);
	}

	/**
	 * Load textdomain.
	 */
	public function _load_textdomain() {
		load_plugin_textdomain( 'snow-monkey-mega-menu', false, basename( __DIR__ ) . '/languages' );
	}
}

require_once SNOW_MONKEY_MEGA_MENU_PATH . '/vendor/autoload.php';
new Bootstrap();

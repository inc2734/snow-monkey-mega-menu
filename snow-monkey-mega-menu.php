<?php
/**
 * Plugin name: Snow Monkey Mega Menu
 * Version: 0.1.2
 * Tested up to: 5.7
 * Requires at least: 5.7
 * Requires PHP: 5.6
 * Requires Snow Monkey: 14.0.0
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

use Inc2734\WP_GitHub_Plugin_Updater\Bootstrap as Updater;
use Snow_Monkey\Plugin\MegaMenu\App\Controller;

define( 'SNOW_MONKEY_MEGA_MENU_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'SNOW_MONKEY_MEGA_MENU_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

class Bootstrap {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, '_bootstrap' ] );
	}

	/**
	 * Bootstrap.
	 */
	public function _bootstrap() {
		load_plugin_textdomain( 'snow-monkey-mega-menu', false, basename( __DIR__ ) . '/languages' );

		add_action( 'init', [ $this, '_activate_autoupdate' ] );

		$theme = wp_get_theme( get_template() );
		if ( 'snow-monkey' !== $theme->template && 'snow-monkey/resources' !== $theme->template ) {
			add_action(
				'admin_notices',
				function() {
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
			[
				'RequiresSnowMonkey' => 'Requires Snow Monkey',
			]
		);

		if (
			isset( $data['RequiresSnowMonkey'] ) &&
			version_compare( $theme->get( 'Version' ), $data['RequiresSnowMonkey'], '<' )
		) {
			add_action(
				'admin_notices',
				function() use ( $data ) {
					?>
					<div class="notice notice-warning is-dismissible">
						<p>
							<?php
							echo esc_html(
								sprintf(
									// translators: %1$s: version
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
			function() {
				$header_layout        = get_theme_mod( 'header-layout' );
				$valid_header_layouts = [
					'1row',
					'2row',
					'center',
					'simple',
					'left',
				];
				if ( ! in_array( $header_layout, $valid_header_layouts, true ) ) {
					return;
				}

				new Controller\Admin();
				new Controller\Front();
			}
		);
	}

	/**
	 * Activate auto update using GitHub
	 *
	 * @return void
	 */
	public function _activate_autoupdate() {
		new Updater(
			plugin_basename( __FILE__ ),
			'inc2734',
			'snow-monkey-mega-menu',
			[
				'homepage' => 'https://snow-monkey.2inc.org',
			]
		);
	}
}

require_once( SNOW_MONKEY_MEGA_MENU_PATH . '/vendor/autoload.php' );
new Bootstrap();

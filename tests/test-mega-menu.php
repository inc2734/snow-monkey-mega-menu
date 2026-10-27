<?php
/**
 * Mega menu integration tests.
 */
class MegaMenuTest extends WP_UnitTestCase {

	/**
	 * Verify that tests load the required theme and the plugin's front controller.
	 */
	public function test_theme_and_plugin_are_loaded() {
		$this->assertSame( 'snow-monkey', wp_get_theme()->get_template() );
		$this->assertTrue( class_exists( 'Framework\Helper' ) );

		$registered = false;
		foreach ( $GLOBALS['wp_filter']['wp_nav_menu_objects']->callbacks as $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( is_array( $callback['function'] ) && $callback['function'][0] instanceof \Snow_Monkey\Plugin\MegaMenu\App\Controller\Front ) {
					$registered = true;
				}
			}
		}
		$this->assertTrue( $registered, 'The plugin must register its menu filters during theme setup.' );
	}

	/**
	 * Verify rendered menu classes across all supported designs and locations.
	 */
	public function test_renders_mega_menu_variants_in_supported_locations() {
		foreach ( array( 'global-nav', 'drop-nav' ) as $location ) {
			foreach ( array( 'mega-menu-1', 'mega-menu-2', 'mega-menu-3', 'mega-menu-4' ) as $variant ) {
				list( $menu, $parent ) = $this->create_menu( $variant );
				update_post_meta( $parent, 'snow-monkey-mega-menu-alignfull', '1' );
				$html = $this->render_menu( $menu, $location );

				$this->assertStringContainsString( 'snow-monkey-mega-menu--' . $variant, $html );
				$this->assertStringContainsString( 'snow-monkey-mega-menu--alignfull', $html );
				$this->assertStringContainsString( 'Child link', $html );
			}
		}
	}

	/**
	 * Verify that ordinary navigation remains unaffected by mega menu settings.
	 */
	public function test_keeps_other_locations_and_unconfigured_items_unchanged() {
		list( $menu ) = $this->create_menu( 'mega-menu-1' );
		$this->assertStringNotContainsString( 'snow-monkey-mega-menu', $this->render_menu( $menu, 'footer-nav' ) );

		list( $menu ) = $this->create_menu( '' );
		$html         = $this->render_menu( $menu, 'global-nav' );
		$this->assertStringNotContainsString( 'snow-monkey-mega-menu', $html );
		$this->assertStringContainsString( 'Child link', $html );
	}

	/**
	 * Verify thumbnail markup while preserving the child link destination and label.
	 */
	public function test_renders_thumbnail_markup_only_for_image_variants() {
		$thumbnail = static function() {
			return '<img src="https://example.org/thumbnail.jpg" alt="Menu image">';
		};
		add_filter( 'snow_monkey_mega_menu_thumbnail', $thumbnail );
		try {
			foreach ( array( 'mega-menu-1', 'mega-menu-4' ) as $variant ) {
				list( $menu ) = $this->create_menu( $variant );
				$html         = $this->render_menu( $menu, 'global-nav' );
				$this->assertStringContainsString( 'snow-monkey-mega-menu__figure', $html );
				$this->assertStringContainsString( 'thumbnail.jpg', $html );
				$this->assertStringContainsString( 'snow-monkey-mega-menu__label">Child link</div>', $html );
				$this->assertStringContainsString( 'href="https://example.org/child"', $html );
			}
			list( $menu ) = $this->create_menu( 'mega-menu-2' );
			$this->assertStringNotContainsString( 'snow-monkey-mega-menu__figure', $this->render_menu( $menu, 'global-nav' ) );
		} finally {
			remove_filter( 'snow_monkey_mega_menu_thumbnail', $thumbnail );
		}
	}

	/**
	 * Create an actual WordPress menu with a parent and a child link.
	 *
	 * @param string $variant Mega menu design.
	 * @return array
	 */
	private function create_menu( $variant ) {
		$menu = wp_create_nav_menu( 'Test menu ' . wp_generate_uuid4() );
		$this->assertNotWPError( $menu );
		$parent = wp_update_nav_menu_item(
			$menu,
			0,
			array(
				'menu-item-title'  => 'Parent link',
				'menu-item-url'    => 'https://example.org/parent',
				'menu-item-status' => 'publish',
			)
		);
		$this->assertNotWPError( $parent );
		wp_update_nav_menu_item(
			$menu,
			0,
			array(
				'menu-item-title'     => 'Child link',
				'menu-item-url'       => 'https://example.org/child',
				'menu-item-parent-id' => $parent,
				'menu-item-status'    => 'publish',
			)
		);
		update_post_meta( $parent, 'snow-monkey-mega-menu', $variant );
		return array( $menu, $parent );
	}

	/**
	 * Render a menu using WordPress's real navigation filters and walker.
	 *
	 * @param int    $menu     Menu ID.
	 * @param string $location Theme location.
	 * @return string
	 */
	private function render_menu( $menu, $location ) {
		return wp_nav_menu(
			array(
				'menu'           => $menu,
				'theme_location' => $location,
				'echo'           => false,
				'fallback_cb'    => false,
			)
		);
	}
}

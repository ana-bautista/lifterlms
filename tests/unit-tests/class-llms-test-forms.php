<?php
/**
 * Test LLMS_Forms Singleton
 *
 * @package LifterLMS/Tests
 *
 * @group forms
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Forms extends LLMS_UnitTestCase {

	/**
	 * Setup the test
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->forms = LLMS_Forms::instance();

	}

	/**
	 * Teardown the test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();

		global $wpdb;
		$wpdb->delete( $wpdb->posts, array( 'post_type' => 'llms_form' ) );

	}

	/**
	 * Retrieve an array of form locations to run tests against.
	 *
	 * @since [version]
	 *
	 * @return string[]
	 */
	private function get_form_locs() {

		return array( 'checkout', 'registration', 'account' );

	}

	/**
	 * Assert that an array looks like a WordPress block array.
	 *
	 * @since [version]
	 *
	 * @param array $block Block settings array.
	 * @return void
	 */
	protected function assertIsABlock( $block ) {

		foreach ( array( 'blockName', 'attrs', 'innerBlocks', 'innerHTML', 'innerContent' ) as $prop ) {
			$this->assertTrue( array_key_exists( $prop, $block ), "Block is missing property {$prop}." );
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			foreach ( $block['innerBlocks'] as $innerBlock ) {
				$this->assertIsABlock( $innerBlock );
			}
		}

	}

	/**
	 * Assert that an array looks like a LifterLMS Form Field settings array.
	 *
	 * @since [version]
	 *
	 * @param array $field Field settings array.
	 * @return void
	 */
	protected function assertIsAField( $field ) {

		foreach ( array( 'id', 'name', 'type' ) as $prop ) {
			$this->assertTrue( array_key_exists( $prop, $field ), "Field is missing property {$prop}." );
		}

	}

	/**
	 * Test singleton instance.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_instance() {

		$this->assertClassHasStaticAttribute( 'instance', 'LLMS_Forms' );

	}

	/**
	 * Test creation for an invalid location.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_invalid_location() {

		$this->assertFalse( $this->forms->create( 'fake' ) );

	}

	/**
	 * Test creating/updating forms.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create() {

		$locs = array( 'checkout', 'registration', 'account' );
		$created = array();

		// Create new forms.
		foreach ( $locs as $loc ) {
			$id = $this->forms->create( $loc );
			$this->assertTrue( is_numeric( $id ) );
			$post = get_post( $id );
			$this->assertEquals( 'llms_form', $post->post_type );
			$this->assertEquals( $loc, get_post_meta( $post->ID, '_llms_form_location', true ) );

			$created[ $loc ] = $id;

		}

		// Locs already exist.
		foreach ( $locs as $loc ) {
			$this->assertFalse( $this->forms->create( $loc ) );
		}

		// Locs already exist and we want to update them.
		foreach ( $locs as $loc ) {
			$this->assertEquals( $created[$loc], $this->forms->create( $loc, true ) );
		}

	}

	/**
	 * Can't retrieve blocks for an invalid location.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_blocks_invalid_location() {

		$this->assertFalse( $this->forms->get_form_blocks( 'fake' ) );

	}

	/**
	 * Can't retrieve blocks for a location that hasn't been installed yet.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_blocks_not_installed() {

		foreach ( $this->get_form_locs() as $loc ) {
			$this->assertFalse( $this->forms->get_form_blocks( $loc ) );
		}

	}

	/**
	 * Test get_form_blocks() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_blocks() {

		foreach ( $this->get_form_locs() as $loc ) {

			$this->forms->create( $loc );
			$blocks = $this->forms->get_form_blocks( $loc );

			foreach ( $blocks as $block ) {
				$this->assertIsABlock( $block );
			}

		}


	}

	/**
	 * Can't retrieve fields for an invalid location.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_fields_invalid_loc() {
		$this->assertFalse( $this->forms->get_form_fields( 'fake' ) );
	}

	/**
	 * Can't retrieve fields for a location that hasn't been installed yet.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_fields_not_installed() {
		foreach ( $this->get_form_locs() as $loc ) {
			$this->assertFalse( $this->forms->get_form_fields( $loc ) );
		}
	}

	/**
	 * Test get_form_fields() method.
	 *
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @return void
	 */
	public function test_get_form_fields() {

		foreach ( $this->get_form_locs() as $loc ) {
			$this->forms->create( $loc );
			$fields = $this->forms->get_form_fields( $loc );

			foreach ( $fields as $field ) {
				$this->assertIsAField( $field );
			}
		}

	}

	/**
	 * Can't get form html for an invalid form.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_html_invalid() {

		$this->assertEquals( '', $this->forms->get_form_html( 'fake' ) );

	}

	/**
	 * Can't get form html for a form that hasn't been installed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_html_not_installed() {

		foreach ( $this->get_form_locs() as $loc ) {
			$this->assertEquals( '', $this->forms->get_form_html( $loc ) );
		}

	}

	/**
	 * Test get_form_html() method.
	 *
	 * @since [version]
	 *
	 * @todo  this test can assert a lot more and should.
	 *
	 * @return void
	 */
	public function test_get_form_html() {

		foreach ( $this->get_form_locs() as $loc ) {
			$this->forms->create( $loc );
			$html = $this->forms->get_form_html( $loc );

			$this->assertStringContains( '<div class="llms-form-field type-email', $html );
		}

	}

	/**
	 * Can't retrieve a post for an invalid location.
	 *
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @return void
	 */
	public function test_get_form_post_invalid() {

		$this->assertFalse( $this->forms->get_form_post( 'fake' ) );

	}

	/**
	 * Test get_form_post() for forms when they're not installed.
	 *
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @return void
	 */
	public function test_get_form_post_not_installed() {

		foreach ( $this->get_form_locs() as $loc ) {
			$this->assertFalse( $this->forms->get_form_post( $loc ) );
		}

	}

	/**
	 * Test get_form_post()
	 *
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @return void
	 */
	public function test_get_form_post() {

		foreach ( $this->get_form_locs() as $loc ) {
			$id = $this->forms->create( $loc );
			$this->assertEquals( get_post( $id ), $this->forms->get_form_post( $loc ) );
		}

	}

	/**
	 * Test get_locations() method.
	 *
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @return void
	 */
	public function test_get_locations() {

		$locs = $this->forms->get_locations();
		foreach ( $this->get_form_locs() as $loc ) {
			$this->assertArrayHasKey( $loc, $locs );
			$this->assertArrayHasKey( 'name', $locs[ $loc ] );
			$this->assertArrayHasKey( 'description', $locs[ $loc ] );
			$this->assertArrayHasKey( 'title', $locs[ $loc ] );
			$this->assertArrayHasKey( 'meta', $locs[ $loc ] );
		}

	}

	/**
	 * Test permalink retrieval for account updates.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_permalink_for_account() {

		LLMS_Install::create_pages();
		$form = get_post( $this->forms->create( 'account' ) );
		$link = LLMS_Unit_Test_Util::call_method( $this->forms, 'get_permalink', array( $form ) );
		$this->assertEquals( add_query_arg( 'edit-account', '', get_permalink( get_option( 'lifterlms_myaccount_page_id' ) ) ), $link );

	}

	/**
	 * Test permalink retrieval for checkout when no access plans exist.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_permalink_for_checkout_no_plans() {

		global $wpdb;
		$wpdb->delete( $wpdb->posts, array( 'post_type' => 'llms_access_plan' ) );

		LLMS_Install::create_pages();
		$form = get_post( $this->forms->create( 'checkout' ) );
		$link = LLMS_Unit_Test_Util::call_method( $this->forms, 'get_permalink', array( $form ) );
		$this->assertEquals( get_permalink( get_option( 'lifterlms_checkout_page_id' ) ), $link );

	}

	/**
	 * Test permalink retrieval for checkout with access plans.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_permalink_for_checkout_with_plans() {

		LLMS_Install::create_pages();
		$plan = $this->get_mock_plan();
		$form = get_post( $this->forms->create( 'checkout' ) );
		$link = LLMS_Unit_Test_Util::call_method( $this->forms, 'get_permalink', array( $form ) );
		$this->assertEquals( add_query_arg( 'plan', $plan->get( 'id' ), get_permalink( get_option( 'lifterlms_checkout_page_id' ) ) ), $link );

	}

	/**
	 * Test permalink retrieval for registration form when open registration is not enabled.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_permalink_for_registration_not_enabled() {

		$form = get_post( $this->forms->create( 'registration' ) );
		update_option( 'lifterlms_enable_myaccount_registration', 'no' );
		$link = LLMS_Unit_Test_Util::call_method( $this->forms, 'get_permalink', array( $form ) );
		$this->assertFalse( $link );

	}

	/**
	 * Test permalink retrieval for registration form when open registration is enabled.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_permalink_for_registration_enabled() {

		LLMS_Install::create_pages();
		$form = get_post( $this->forms->create( 'registration' ) );
		update_option( 'lifterlms_enable_myaccount_registration', 'yes' );
		$link = LLMS_Unit_Test_Util::call_method( $this->forms, 'get_permalink', array( $form ) );
		$this->assertEquals( get_permalink( get_option( 'lifterlms_myaccount_page_id' ) ), $link );

	}

	/**
	 * test the install() method.
	 *
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @return void
	 */
	public function test_install() {

		$installed = $this->forms->install();
		$this->assertEquals( 3, count( $installed ) );

		foreach( $installed as $id ) {
			$post = get_post( $id );
			$this->assertTrue( is_a( $post, 'WP_Post' ) );
			$this->assertEquals( 'llms_form', $post->post_type );
		}

		// Already installed.
		$installed = $this->forms->install();
		foreach ( $installed as $id ) {
			$this->assertFalse( $id );
		}

	}

	/**
	 * Test post type registration.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_register_post_type() {

		$this->assertTrue( post_type_exists( 'llms_form' ) );

	}

	/**
	 * Test custom meta prop registration.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_register_meta() {

		do_action( 'init' );

		global $wp_meta_keys;
		$this->assertArrayHasKey( 'post', $wp_meta_keys );
		$this->assertArrayHasKey( 'llms_form', $wp_meta_keys['post'] );

		foreach ( array( '_llms_form_location', '_llms_form_show_title' ) as $meta ) {
			$this->assertArrayHasKey( $meta, $wp_meta_keys['post']['llms_form'] );
		}

	}

	/**
	 * Test block field render function for non-field blocks.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_render_field_block_non_field_block() {

		$html = '<p>Fake paragraph content</p>';
		$blocks = parse_blocks( '<!-- wp:paragraph -->' . $html . '<!-- /wp:paragraph -->' );
		$this->assertEquals( $html, $this->forms->render_field_block( $html, $blocks[0] ) );

	}

	/**
	 * Test rendering a field block as a field.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_render_field_block() {

		$atts = array(
			'id' => 'field_id',
		);

		$blocks = parse_blocks( '<!-- wp:llms/form-field-text {"id":"field_id"} /-->' );

		$this->assertEquals( llms_form_field( $atts, false ), $this->forms->render_field_block( '', $blocks[0] ) );

	}

}
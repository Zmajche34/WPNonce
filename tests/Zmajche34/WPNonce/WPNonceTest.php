<?php
/**
 * Test the WPNonce class.
 *
 * @package Tests
 **/
use Brain\Monkey;
use Brain\Monkey\Functions;
use Brain\Monkey\WP\Filters;
use Zmajche34\WPNonce\WPNonce;

/**
 * Test class WpNonceTest
 **/
class WPNonceTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The lifetime
	 *
	 * @var int
	 **/
	public $lifetime;

	/**
	 * The action
	 *
	 * @var string
	 **/
	public $action;

	/**
	 * The request name
	 *
	 * @var string
	 **/
	public $request_name;

	/**
	 * The WPNonce object
	 * 
	 * @var WPNonce
	 **/
	public $nonce_object;

	/**
	 * Set up the test configuration.
	 **/
	public function setUp() {
		if ( ! defined( 'DAY_IN_SECONDS' ) ) {
			define( 'DAY_IN_SECONDS', 86400 );
		}
		parent::setUp();
		Monkey::setUpWP();
		$this->action   = 'action';
		$this->request_name  = 'request';
	}

	/**
	 * Tear down the test configuration.
	 **/
	public function tearDown() {
		Monkey::tearDownWP();
		parent::tearDown();
	}

	/**
	 * Check if WPNonce initializzes the data correctly.
	 */
	public function testConstructorWithLifetime() {
		$this->lifetime = DAY_IN_SECONDS;
		Filters::expectAdded( 'nonce_life' )->once();
		$this->nonce_object = new WPNonce(
			$this->action,
			$this->request_name,
			$this->lifetime
		);

		self::assertSame( $this->nonce_object->get_action(), $this->action );
		self::assertSame( $this->nonce_object->get_request_name(), $this->request_name );
		self::assertSame( $this->nonce_object->get_lifetime(), $this->lifetime );
		self::assertSame( $this->nonce_object->get_lifetime( DAY_IN_SECONDS ), $this->lifetime );
	}

	/**
	 * Check if WPNonce initializzes the data correctly, when lifetime is not set.
	 **/
	public function testConstructorWithoutLifetime() {
		$this->lifetime = null;
		Filters::expectAdded( 'nonce_life' )->never();
		$this->nonce_object = new WPNonce(
			$this->action,
			$this->request_name,
			$this->lifetime
		);
	}

	/**
	 * Check the create function.
	 */
	public function testCreate() {
		// wp_create_nonce is mocked with sha1
		Functions::when( 'wp_create_nonce' )->alias( 'sha1' );
		$this->lifetime = DAY_IN_SECONDS;
		$this->nonce_object = new WPNonce(
			$this->action,
			$this->request_name,
			$this->lifetime
		);
		$nonce = $this->nonce_object->create();

		self::assertSame( $nonce, $this->nonce_object->get_nonce() );
	}

	/**
	 * Test the create field function.
	 */
	public function testCreateField() {
		// wp_create_nonce is mocked with sha1
		Functions::when( 'wp_create_nonce' )->alias( 'sha1' );
		// wp_nonce_field is mocked by this function
		Functions::expect( 'wp_nonce_field' )->andReturnUsing( function ( $action, $request_name, $referer, $echo ) {
			$string = $action . $request_name;
			if ( $referer ) {
				$string .= 'referer';
			}
			return $string;
		} );

		$this->lifetime = DAY_IN_SECONDS;
		$this->nonce_object = new WPNonce(
			$this->action,
			$this->request_name,
			$this->lifetime
		);
		$field = $this->nonce_object->create_field();
		self::assertSame( $field, $this->action . $this->request_name );
		$field = $this->nonce_object->create_field( true );
		self::assertSame( $field, $this->action . $this->request_name . 'referer' );
	}

	/**
	 * Test the create URL function.
	 */
	public function testCreateURL() {
		// wp_create_nonce is mocked with sha1
		Functions::when( 'wp_create_nonce' )->alias( 'sha1' );
		// wp_nonce_url is mocked by this function
		Functions::expect( 'wp_nonce_url' )->andReturnUsing( function ( $url, $action, $request_name ) {
			return $url . $action . $request_name;
		} );

		$this->lifetime = DAY_IN_SECONDS;
		$this->nonce_object = new WPNonce(
			$this->action,
			$this->request_name,
			$this->lifetime
		);
		$url = 'http://example.com/';
		$url_with_nonce = $this->nonce_object->create_url( $url );
		self::assertSame( $url_with_nonce, $url . $this->action . $this->request_name );
		self::assertSame( $url_with_nonce, $this->nonce_object->get_url() );
	}

	/**
	 * Check the verify function.
	 */
	public function testVerification() {
		// wp_create_nonce is mocked with sha1
		Functions::when( 'wp_create_nonce' )->alias( 'sha1' );
		// wp_verify_nonce is mocked by this function
		Functions::expect( 'wp_verify_nonce' )->andReturnUsing( function ( $nonce, $action ) {
			return sha1( $action ) === $nonce ? 1 : false;
		} );
		// mock wp_unslash is mocked by this function
		Functions::expect( 'wp_unslash' )->andReturnUsing( function ( $string ) {
			return $string;
		} );
		// sanitize_text_field is mocked by this function
		Functions::expect( 'sanitize_text_field' )->andReturnUsing( function ( $string ) {
			return $string;
		} );

		$this->lifetime = DAY_IN_SECONDS;
		$this->nonce_object = new WPNonce(
			$this->action,
			$this->request_name,
			$this->lifetime
		);
		$nonce = $this->nonce_object->create();
		$valid = $this->nonce_object->verify( $nonce );
		self::assertTrue( $valid );

		// Check if nonce is not valid.
		$not_valid = $this->nonce_object->verify( 'not-valid' . $nonce );
		self::assertFalse( $not_valid );

		// Check auto-nonce assignment.
		$_REQUEST[ $this->request_name ] = $nonce;
		$this->nonce_object = new WPNonce(
			$this->action,
			$this->request_name,
			$this->lifetime
		);
		$valid = $this->nonce_object->verify();
		self::assertTrue( $valid );
	}

	/**
	 * Check the get nonce age function.
	 **/
	public function testGetNonceAge() {
		// wp_create_nonce is mocked with sha1
		Functions::when( 'wp_create_nonce' )->alias( 'sha1' );
		// wp_verify_nonce is mocked by this function
		Functions::expect( 'wp_verify_nonce' )->andReturnUsing( function ( $nonce, $action ) {
			return sha1( $action ) === $nonce ? 1 : false;
		} );
		// mock wp_unslash is mocked by this function
		Functions::expect( 'wp_unslash' )->andReturnUsing( function ( $string ) {
			return $string;
		} );
		// sanitize_text_field is mocked by this function
		Functions::expect( 'sanitize_text_field' )->andReturnUsing( function ( $string ) {
			return $string;
		} );

		$this->lifetime = DAY_IN_SECONDS;
		$this->nonce_object = new WPNonce(
			$this->action,
			$this->request_name,
			$this->lifetime
		);
		$nonce = $this->nonce_object->create();
		$age = $this->nonce_object->get_nonce_age( $nonce );
		self::assertSame( 1, $age );
	}
}

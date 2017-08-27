<?php
/**
 * WPNonce
 * The class with the implementation of the Nonce API system
 * in the object oriented way.
 *
 * @package zmajche34-wpnonce
 */
namespace Zmajche34\WPNonce;
/**
 * WPNonce class
 **/
class WPNonce implements IWPNonce {
	/**
	 * The name of the action
	 *
	 * @var string
	 **/
	private $action = '';

	/**
	 * The name of the request
	 *
	 * @var string
	 **/
	private $request_name = '';

	/**
	 * The nonce
	 *
	 * @var string
	 **/
	private $nonce = '';

	/**
	 * The lifetime of a nonce in seconds
	 *
	 * @var int
	 **/
	private $lifetime = DAY_IN_SECONDS;

	/**
	 * The field
	 *
	 * @var string
	 **/
	private $field = '';

	/**
	 * The URL
	 *
	 * @var string
	 **/
	private $url = '';

	/**
	 * The constructor for this class
	 *
	 * @param string $action       The action
	 * @param string $request_name The request name
	 * @param int    $lifetime     The lifetime
	 **/
	function __construct( string $action, string $request_name, int $lifetime = null ) {
		$this->set_action( $action );
		$this->set_request_name( $request_name );
		if ( null != $lifetime ) {
			$this->set_lifetime( $lifetime );
		}
	}

	/**
	 * Set the action
	 *
	 * @param string $action The action name
	 **/
	public function set_action( string $action ) {
		$this->action = $action;
	}

	/**
	 * Get the action
	 *
	 * @return string The action.
	 **/
	public function get_action() {
		return $this->action;
	}

	/**
	 * Set the request name
	 *
	 * @param string $request_name The request name
	 **/
	public function set_request_name( string $request_name ) {
		$this->request_name = $request_name;
	}

	/**
	 * Get the request name
	 *
	 * @return string The request name
	 **/
	public function get_request_name() {
		return $this->request_name;
	}

	/**
	 * Set the lifetime in this class and the current WP system
	 *
	 * @param int $lifetime The lifetime
	 **/
	public function set_lifetime( int $lifetime ) {
		$this->lifetime = $lifetime;
		add_filter( 'nonce_life', array( $this, 'get_lifetime' ) );
	}

	/**
	 * Get the lifetime
	 *
	 * @return int $lifetime The lifetime
	 **/
	public function get_lifetime() {
		return $this->lifetime;
	}

	/**
	 * Set the nonce
	 *
	 * @param string $nonce The nonce to verify
	 **/
	public function set_nonce( string $nonce ) {
		$this->nonce = $nonce;
	}

	/**
	 * Get the nonce
	 *
	 * @return string The nonce
	 **/
	public function get_nonce() {
		return $this->nonce;
	}

	/**
	 * Set the field
	 *
	 * @param  string $field   The field
	 **/
	public function set_field( string $field ) {
		$this->field = $field;
	}

	/**
	 * Get the field
	 *
	 * @return string The field
	 **/
	public function get_field() {
		return $this->field;
	}

	/**
	 * Set the URL
	 *
	 * @param string $url The URL
	 **/
	public function set_url( string $url ) {
		$this->url = $url;
	}

	/**
	 * Get the URL
	 *
	 * @return string The URL
	 **/
	public function get_url() {
		return $this->url;
	}

	/**
	 * Generate the nonce in the current WP system and set in this class
	 *
	 * @return string The generated nonce
	 **/
	public function create() {
		$this->set_nonce( wp_create_nonce( $this->get_action() ) );
		return $this->get_nonce();
	}

	/**
	 * Generate the field in the current WP system and set in this class
	 *
	 * @param boolean $referer Should a referer field be added
	 * @return string          The generated field
	 **/
	public function create_field( bool $referer = null ) {
		$referer = (bool) $referer;
		$this->create();
		$field = wp_nonce_field(
			$this->get_action(),
			$this->get_request_name(),
			$referer,
			false
		);
		$this->set_field( $field );
		return $this->get_field();
	}

	/**
	 * Generate the URL in the current WP system and set in this class
	 *
	 * @param string $url The URL on which the nonce is appended
	 * @return string The generated URL
	 **/
	public function create_url( string $url ) {
		$this->create();
		$generated_url = wp_nonce_url(
			$url,
			$this->get_action(),
			$this->get_request_name()
		);
		$this->set_url( $generated_url );
		return $this->get_url();
	}

	/**
	 * Verify the nonce
	 *
	 * @param string $nonce   The nonce to verify
	 * @return boolean        Validity of the given nonce
	 **/
	public function verify( string $nonce = null ) {
		return (bool) $this->get_nonce_age( $nonce );
	}

	/**
	 * Get the age of the nonce
	 *
	 * @param string $nonce The nonce to verify
	 * @return string       The possible values are 
	 *                      the nonce is "young" (1),
	 *                      the nonce is "old" (2) 
	 *                      or the nonce is invalid (false).
	 *                      "young" usually means 0 - half of lifetime
	 *                      "old" usually means half lifetime - lifetime
	 **/
	public function get_nonce_age( string $nonce = null ) {
		if ( null != $nonce ) {
			$this->set_nonce( $nonce );
		} else {
			$this->nonce_from_the_field();
		}
		$age = wp_verify_nonce(
			$this->get_nonce(),
			$this->get_action()
		);
		return $age;
	}

	/**
	 * Get nonce from the text field and set in this class
	 */
	private function nonce_from_the_field() {
		if ( isset( $_REQUEST[ $this->get_request_name() ] ) ) {
			$nonce = sanitize_text_field(
				wp_unslash( $_REQUEST[ $this->get_request_name() ] )
			);
			$this->set_nonce( $nonce );
		}
	}
}

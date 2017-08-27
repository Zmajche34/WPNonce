<?php
/**
 * IWPNonce
 * The interface for the WP Nonce API.
 *
 * @package zmajche34-wpnonce
 */

namespace Zmajche34\WPNonce;

/**
 * The Interface for WPNonce
 *
 * @package zmajche34-wpnonce
 */

interface IWPNonce {

	/**
	 * Set the action
	 *
	 * @param  string $action The new action
	 **/
	public function set_action( string $action );

	/**
	 * Get the action
	 *
	 * @return string The action
	 **/
	public function get_action();

	/**
	 * Set the request name
	 *
	 * @param  string $request_name The request name for $_REQUEST
	 **/
	public function set_request_name( string $request_name );

	/**
	 * Get the request name
	 *
	 * @return string The request name
	 **/
	public function get_request_name();

	/**
	 * Set a lifetime
	 *
	 * @param  int $lifetime The lifetime
	 **/
	public function set_lifetime( int $lifetime );

	/**
	 * Get the lifetime
	 *
	 * @return int     The lifetime
	 **/
	public function get_lifetime();

	/**
	 * Set the nonce
	 *
	 * @param  string $nonce The nonce
	 **/
	public function set_nonce( string $nonce );

	/**
	 * Get the nonce
	 *
	 * @return string The nonce
	 **/
	public function get_nonce();

	/**
	 * Set the URL
	 *
	 * @param  string $field   The field
	 **/
	public function set_field( string $field );

	/**
	 * Get the URL
	 *
	 * @return string The URL
	 **/
	public function get_field();

	/**
	 * Set the URL
	 *
	 * @param string $url The URL
	 **/
	public function set_url( string $url );

	/**
	 * Get the URL
	 *
	 * @return string The URL
	 **/
	public function get_url();

}

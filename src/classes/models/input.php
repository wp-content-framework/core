<?php
/**
 * WP_Framework Classes Models Input
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework\Classes\Models;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Input
 * @package WP_Framework\Classes\Models
 */
class Input implements \WP_Framework\Interfaces\Singleton {

	use \WP_Framework\Traits\Singleton;

	/**
	 * @var array $_input
	 */
	private $_input = null;

	/**
	 * @var string $_php_input
	 */
	private $_php_input = null;

	/**
	 * @return bool
	 */
	protected static function is_shared_class() {
		return true;
	}

	/**
	 * @return array
	 */
	public function all() {
		if ( ! isset( $this->_input ) ) {
			$this->_input = array_merge( $_GET, $_POST );
		}

		return $this->_input;
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_GET : $this->app->utility->array_get( $_GET, $key, $default );
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set_get( $key, $value ) {
		$_GET[ $key ] = $this->add_magic_quotes( $value );
	}

	/**
	 * @param string $key
	 */
	public function delete_get( $key ) {
		unset( $_GET[ $key ] );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function post( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_POST : $this->app->utility->array_get( $_POST, $key, $default );
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set_post( $key, $value ) {
		$_POST[ $key ] = $this->add_magic_quotes( $value );
	}

	/**
	 * @param string $key
	 */
	public function delete_post( $key ) {
		unset( $_POST[ $key ] );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function request( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_REQUEST : $this->app->utility->array_get( $_REQUEST, $key, $default );
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set_request( $key, $value ) {
		$_REQUEST[ $key ] = $this->add_magic_quotes( $value );
	}

	/**
	 * @param string $key
	 */
	public function delete_request( $key ) {
		unset( $_REQUEST[ $key ] );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function file( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_FILES : $this->app->utility->array_get( $_FILES, $key, $default );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function cookie( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_COOKIE : $this->app->utility->array_get( $_COOKIE, $key, $default );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function server( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_SERVER : $this->app->utility->array_get( $_SERVER, strtoupper( $key ), $default );
	}

	/**
	 * @param string $default
	 *
	 * @return string
	 */
	public function ip( $default = '0.0.0.0' ) {
		return $this->server( 'HTTP_X_FORWARDED_FOR', $this->server( 'REMOTE_ADDR', $default ) );
	}

	/**
	 * @param string $default
	 *
	 * @return string
	 */
	public function user_agent( $default = '' ) {
		return $this->server( 'HTTP_USER_AGENT', $default );
	}

	/**
	 * @param string $default
	 *
	 * @return string
	 */
	public function method( $default = 'GET' ) {
		return strtoupper( $this->server( 'REQUEST_METHOD', $this->request( '_method', $default ) ) );
	}

	/**
	 * @return bool
	 */
	public function is_post() {
		return ! in_array( $this->method(), [
			'GET',
			'HEAD',
		] );
	}

	/**
	 * @return bool|string
	 */
	public function php_input() {
		if ( ! isset( $this->_php_input ) ) {
			$this->_php_input = file_get_contents( 'php://input' );
		}

		return $this->_php_input;
	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	public function get_current_url( array $args = [] ) {
		$url = $this->get_current_host() . $this->get_current_path();
		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}

	/**
	 * @return string
	 */
	public function get_current_host() {
		return ( is_ssl() ? "https://" : "http://" ) . $this->server( 'HTTP_HOST' );
	}

	/**
	 * @return string
	 */
	public function get_current_path() {
		return $this->server( 'REQUEST_URI' );
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	private function add_magic_quotes( $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $item ) {
				$value[ $key ] = $this->add_magic_quotes( $item );
			}
		} else {
			$value = addslashes( $value );
		}

		return $value;
	}
}

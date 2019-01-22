<?php
/**
 * WP_Framework Interfaces Singleton
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework\Interfaces;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Interface Singleton
 * @package WP_Framework\Interfaces
 */
interface Singleton extends Readonly {

	/**
	 * @param \WP_Framework $app
	 *
	 * @return \WP_Framework\Traits\Singleton
	 */
	public static function get_instance( \WP_Framework $app );

	/**
	 * @param string $config_name
	 * @param string $suffix
	 *
	 * @return string
	 */
	public function get_slug( $config_name, $suffix = '-' );

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function is_filter_callable( $name );

	/**
	 * @param string $method
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function filter_callback( $method, array $args );

}

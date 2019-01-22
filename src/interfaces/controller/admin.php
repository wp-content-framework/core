<?php
/**
 * WP_Framework Interfaces Controller Admin
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework\Interfaces\Controller;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Interface Admin
 * @package WP_Framework\Interfaces\Controller
 */
interface Admin extends \WP_Framework\Interfaces\Controller, \WP_Framework\Interfaces\Nonce {

	/**
	 * @return string
	 */
	public function get_page_title();

	/**
	 * @return string
	 */
	public function get_menu_name();

	/**
	 * @param string $relative_namespace
	 */
	public function set_relative_namespace( $relative_namespace );

	/**
	 * @return string
	 */
	public function get_page_slug();

	/**
	 * @return string
	 */
	public function presenter();

	/**
	 * setup help
	 */
	public function setup_help();

}

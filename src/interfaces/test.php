<?php
/**
 * WP_Framework Interfaces Test
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
 * Interface Test
 * @package WP_Framework\Interfaces
 */
interface Test extends Singleton, Hook {

	/**
	 * @return string
	 */
	public function get_test_slug();

	/**
	 * @return bool
	 */
	public function has_dump_objects();

	/**
	 * @return array
	 */
	public function get_dump_objects();

}

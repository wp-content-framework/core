<?php
/**
 * WP_Framework Interfaces Controller
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
 * Interface Controller
 * @package WP_Framework\Interfaces
 */
interface Controller extends Singleton, Presenter {

	/**
	 * @return null|string|false
	 */
	public function get_capability();

}

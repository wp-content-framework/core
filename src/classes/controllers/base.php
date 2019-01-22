<?php
/**
 * WP_Framework Classes Controller Base
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework\Classes\Controllers;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Base
 * @package WP_Framework\Classes\Controllers
 */
abstract class Base implements \WP_Framework\Interfaces\Hook, \WP_Framework\Interfaces\Controller {

	use \WP_Framework\Traits\Hook, \WP_Framework\Traits\Controller;

}

<?php
/**
 * WP_Framework Classes Controller Admin Base
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework\Classes\Controllers\Admin;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Base
 * @package WP_Framework\Classes\Controllers\Admin
 */
abstract class Base extends \WP_Framework\Classes\Controllers\Base implements \WP_Framework\Interfaces\Controller\Admin, \WP_Framework\Interfaces\Admin {

	use \WP_Framework\Traits\Controller\Admin, \WP_Framework\Traits\Admin;

}

<?php
/**
 * WP_Framework_Core Tests Models Misc Validate
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Core\Tests\Models\Misc;

use WP_Framework_Common\Traits\Package;
use WP_Framework_Core\Traits\Singleton;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Validate
 * @package WP_Framework_Core\Tests\Models\Misc
 */
class Validate implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Helper\Validate {

	use Singleton, \WP_Framework_Core\Traits\Helper\Validate, Package;

	/**
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, array $arguments ) {
		return $this->$name( ...$arguments );
	}
}

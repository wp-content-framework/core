<?php
/**
 * WP_Framework Tests Models Misc Validate
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework\Tests\Models\Misc;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Validate
 * @package WP_Framework\Tests\Models\Misc
 */
class Validate implements \WP_Framework\Interfaces\Singleton, \WP_Framework\Interfaces\Helper\Validate {

	use \WP_Framework\Traits\Singleton, \WP_Framework\Traits\Helper\Validate;

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

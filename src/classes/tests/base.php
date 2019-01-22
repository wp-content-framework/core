<?php
/**
 * WP_Framework Tests Base
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework\Classes\Tests;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Base
 * @package WP_Framework\Classes\Tests
 */
abstract class Base extends \WP_Framework\Classes\Models\Test\Base implements \WP_Framework\Interfaces\Test {

	use \WP_Framework\Traits\Test;

	/** @var \WP_Framework */
	protected static $test_app;

	/**
	 * @param \WP_Framework $app
	 */
	public static function set_app( $app ) {
		static::$test_app = $app;
	}

	/**
	 * @throws \ReflectionException
	 */
	public final function setUp() {
		$class = get_called_class();
		if ( false === $class ) {
			$class = get_class();
		}
		$reflection = new \ReflectionClass( $class );
		$this->init( static::$test_app, $reflection );
		$this->_setup();
	}

	/**
	 * setup
	 */
	public function _setup() {

	}
}

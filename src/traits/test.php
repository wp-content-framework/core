<?php
/**
 * WP_Framework Traits Test
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework\Traits;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Trait Test
 * @package WP_Framework\Traits
 * @property \WP_Framework $app
 * @mixin \PHPUnit\Framework\TestCase
 */
trait Test {

	use Singleton, Hook;

	/**
	 * @var array $_objects
	 */
	private $_objects = [];

	/**
	 * Test constructor.
	 *
	 * @param mixed $arg1
	 * @param mixed $arg2
	 * @param mixed $arg3
	 *
	 * @throws \ReflectionException
	 */
	public function __construct( $arg1 = null, $arg2 = [], $arg3 = '' ) {
		$args = func_get_args();
		if ( count( $args ) > 1 && $args[0] instanceof \WP_Framework && $args[1] instanceof \ReflectionClass ) {
			// Singleton
			$this->init( ...$args );
		} elseif ( count( $args ) > 2 ) {
			// \PHPUnit_Framework_TestCase
			$reflectionClass = new \ReflectionClass( '\PHPUnit_Framework_TestCase' );
			if ( $arg1 !== null ) {
				$this->setName( $arg1 );
			}
			$data = $reflectionClass->getProperty( 'data' );
			$data->setAccessible( true );
			$data->setValue( $this, $arg2 );
			$data->setAccessible( false );
			$dataName = $reflectionClass->getProperty( 'dataName' );
			$dataName->setAccessible( true );
			$dataName->setValue( $this, $arg3 );
			$dataName->setAccessible( false );
		}
	}

	/**
	 * @return string
	 */
	public function get_test_slug() {
		return $this->get_file_slug();
	}

	/**
	 * @param mixed $obj
	 */
	protected function dump( $obj ) {
		$this->_objects[] = print_r( $obj, true );
	}

	/**
	 * @return bool
	 */
	public function has_dump_objects() {
		return ! empty( $this->_objects );
	}

	/**
	 * @return array
	 */
	public function get_dump_objects() {
		return $this->_objects;
	}
}

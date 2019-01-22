<?php
/**
 * WP_Framework Classes Models Test
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework\Classes\Models;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Test
 * @package WP_Framework\Classes\Models
 */
class Test implements \WP_Framework\Interfaces\Loader {

	use \WP_Framework\Traits\Loader;

	/**
	 * @var bool $_is_valid
	 */
	private $_is_valid = false;

	/**
	 * initialize
	 */
	protected function initialize() {
		if ( ! class_exists( '\PHPUnit_TextUI_Command' ) ) {
			$autoload = $this->app->define->lib_vendor_dir . DS . 'autoload.php';
			if ( ! file_exists( $autoload ) ) {
				return;
			}
			/** @noinspection PhpIncludeInspection */
			require_once $this->app->define->lib_vendor_dir . DS . 'autoload.php';

			if ( ! class_exists( '\PHPUnit_TextUI_Command' ) ) {
				return;
			}
		}

		$this->_is_valid = true;
	}

	/**
	 * @return bool
	 */
	public function is_valid() {
		return $this->_is_valid && count( $this->get_tests() ) > 0;
	}

	/**
	 * @return array
	 */
	private function get_tests() {
		if ( ! $this->_is_valid ) {
			return [];
		}

		return $this->get_class_list();
	}

	/**
	 * @return array
	 */
	public function get_test_class_names() {
		return $this->app->utility->array_map( $this->get_tests(), 'get_class_name' );
	}

	/**
	 * @return array
	 */
	protected function get_namespaces() {
		return [
			$this->app->define->plugin_namespace . '\\Classes\\Tests',
		];
	}

	/**
	 * @return string
	 */
	protected function get_instanceof() {
		return '\WP_Framework\Classes\Tests\Base';
	}

	/**
	 * @return array
	 */
	public function do_tests() {
		if ( ! $this->_is_valid ) {
			return [];
		}

		\WP_Framework\Classes\Tests\Base::set_app( $this->app );
		$results = [];
		foreach ( $this->get_tests() as $slug => $class ) {
			$results[] = $this->do_test( $class );
		}

		return $results;
	}

	/**
	 * @param \WP_Framework\Classes\Tests\Base $class
	 *
	 * @return array
	 */
	private function do_test( \WP_Framework\Classes\Tests\Base $class ) {
		$suite = new \PHPUnit_Framework_TestSuite( $class->get_class_name() );
		$suite->setBackupGlobals( false );
		$result = $suite->run();

		$dump = [];
		foreach ( $result->topTestSuite()->tests() as $item ) {
			if ( $item instanceof \WP_Framework\Interfaces\Test ) {
				$dump = array_merge( $dump, $item->get_dump_objects() );
			} elseif ( $item instanceof \PHPUnit_Framework_TestSuite_DataProvider ) {
				foreach ( $item->tests() as $item2 ) {
					if ( $item2 instanceof \WP_Framework\Interfaces\Test ) {
						$dump = array_merge( $dump, $item2->get_dump_objects() );
					}
				}
			}
		}

		return [
			$result->wasSuccessful(),
			$this->get_view( 'admin/include/test_result', [
				'result' => $result,
				'class'  => $class,
				'dump'   => $dump,
			] ),
		];
	}
}

<?php
/**
 * WP_Framework Models Utility Test
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework\Tests\Models;

/**
 * Class UtilityTest
 * @package WP_Framework\Tests\Models
 * @group technote
 * @group models
 */
class UtilityTest extends \WP_Framework\Tests\TestCase {

	/**
	 * @var \WP_Framework\Classes\Models\Utility $_utility
	 */
	private static $_utility;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$_utility = \WP_Framework\Classes\Models\Utility::get_instance( static::$app );
	}

	/**
	 * @dataProvider _test_flatten_provider
	 *
	 * @param array $array
	 * @param bool $preserve_keys
	 * @param array $expected
	 */
	public function test_flatten( $array, $preserve_keys, $expected ) {
		$this->assertEquals( $expected, static::$_utility->flatten( $array, $preserve_keys ) );
	}

	/**
	 * @return array
	 */
	public function _test_flatten_provider() {
		return [
			[
				[],
				false,
				[],
			],
			[
				[
					[ 'test1', 'test2' ],
					[ 'test3', 'test4' ],
				],
				false,
				[ 'test1', 'test2', 'test3', 'test4' ],
			],
			[
				[
					[ 'a' => 'test1', 'b' => 'test2' ],
					[ 'c' => 'test3', 'd' => 'test4' ],
				],
				false,
				[ 'test1', 'test2', 'test3', 'test4' ],
			],
			[
				[
					[ 'a' => 'test1', 'b' => 'test2' ],
					[ 'c' => 'test3', 'd' => 'test4' ],
				],
				true,
				[ 'a' => 'test1', 'b' => 'test2', 'c' => 'test3', 'd' => 'test4' ],
			],
			[
				[
					[ 'a' => 'test1', 'b' => 'test2' ],
					[ 'a' => 'test3', 'b' => 'test4' ],
				],
				true,
				[ 'a' => 'test3', 'b' => 'test4' ],
			],
		];
	}

	/**
	 * @dataProvider _test_array_get_provider
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $default
	 * @param mixed $expected
	 */
	public function test_array_get( $array, $key, $default, $expected ) {
		$this->assertEquals( $expected, static::$_utility->array_get( $array, $key, $default ) );
	}

	/**
	 * @return array
	 */
	public function _test_array_get_provider() {
		return [
			[
				[],
				'test',
				null,
				null,
			],
			[
				[
					'test1' => true,
					'test2' => 100,
				],
				'test2',
				false,
				100,
			],
			[
				[
					'test1' => true,
					'test2' => 100,
				],
				'test3',
				false,
				false,
			],
		];
	}

	/**
	 * @dataProvider _test_array_set_provider
	 * @depends      test_array_get
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_array_set( $array, $key, $value ) {
		static::$_utility->array_set( $array, $key, $value );
		$this->assertEquals( $value, static::$_utility->array_get( $array, $key ) );
	}

	/**
	 * @return array
	 */
	public function _test_array_set_provider() {
		return [
			[
				[],
				'test',
				null,
			],
			[
				[
					'test' => true,
				],
				'test',
				100,
			],
			[
				[
					'test' => true,
				],
				'test2',
				false,
			],
			[
				[
					'test' => true,
				],
				'test',
				[
					'test1' => true,
				],
			],
		];
	}

	/**
	 * @dataProvider _test_replace_provider
	 *
	 * @param string $string
	 * @param array $data
	 * @param string $expected
	 */
	public function test_replace( $string, $data, $expected ) {
		$this->assertEquals( $expected, static::$_utility->replace( $string, $data ) );
	}

	/**
	 * @return array
	 */
	public function _test_replace_provider() {
		return [
			[
				'test',
				[ 'a' => 'b' ],
				'test',
			],
			[
				'test1${a}test2',
				[ 'a' => 'b' ],
				'test1btest2',
			],
			[
				'test1${test}test2',
				[ 'test' => '' ],
				'test1test2',
			],
		];
	}

	/**
	 * @dataProvider _test_starts_with_provider
	 *
	 * @param $haystack
	 * @param $needle
	 * @param $expected
	 */
	public function test_starts_with( $haystack, $needle, $expected ) {
		$this->assertEquals( $expected, static::$_utility->starts_with( $haystack, $needle ) );
	}

	/**
	 * @return array
	 */
	public function _test_starts_with_provider() {
		return [
			[
				'abc',
				'a',
				true,
			],
			[
				'abc',
				'ab',
				true,
			],
			[
				'abc',
				'abc',
				true,
			],
			[
				'abc',
				'abcd',
				false,
			],
			[
				'abc',
				'b',
				false,
			],
			[
				'abc',
				'bcd',
				false,
			],
			[
				'',
				'',
				false,
			],
			[
				'',
				'a',
				false,
			],
			[
				'a',
				'',
				false,
			],
		];
	}

	/**
	 * @dataProvider _test_ends_with_provider
	 *
	 * @param $haystack
	 * @param $needle
	 * @param $expected
	 */
	public function test_ends_with( $haystack, $needle, $expected ) {
		$this->assertEquals( $expected, static::$_utility->ends_with( $haystack, $needle ) );
	}

	/**
	 * @return array
	 */
	public function _test_ends_with_provider() {
		return [
			[
				'abc',
				'c',
				true,
			],
			[
				'abc',
				'bc',
				true,
			],
			[
				'abc',
				'abc',
				true,
			],
			[
				'abc',
				'0abc',
				false,
			],
			[
				'abc',
				'b',
				false,
			],
			[
				'abc',
				'0ba',
				false,
			],
			[
				'',
				'',
				false,
			],
			[
				'',
				'a',
				false,
			],
			[
				'a',
				'',
				false,
			],
		];
	}
}
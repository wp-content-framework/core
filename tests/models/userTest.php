<?php
/**
 * WP_Framework Models User Test
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
 * Class UserTest
 * @package WP_Framework\Tests\Models
 * @group technote
 * @group models
 */
class UserTest extends \WP_Framework\Tests\TestCase {

	/**
	 * @var \WP_Framework\Classes\Models\User $_user
	 */
	private static $_user;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$_user = \WP_Framework\Classes\Models\User::get_instance( static::$app );
		foreach ( static::get_test_value() as $value ) {
			static::$_user->delete( $value[0], 1 );
		}
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		foreach ( static::get_test_value() as $value ) {
			static::$_user->delete( $value[0], 1 );
		}
	}

	/**
	 * @dataProvider _test_value_provider
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_set( $key, $value ) {
		$this->assertEquals( true, static::$_user->set( $key, $value, 1 ) );
	}

	/**
	 * @dataProvider _test_value_provider
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_get( $key, $value ) {
		$this->assertEquals( $value, static::$_user->get( $key, 1 ) );
	}

	/**
	 * @dataProvider _test_value_provider
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_delete(
		/** @noinspection PhpUnusedParameterInspection */
		$key, $value
	) {
		$this->assertEquals( true, static::$_user->delete( $key, 1 ) );
		$this->assertEquals( '', static::$_user->get( $key, 1 ) );
	}

	/**
	 * @return array
	 */
	private static function get_test_value() {
		return [
			[ 'technote_test_user_bool', true ],
			[ 'technote_test_user_int', 123 ],
			[ 'technote_test_user_float', 0.987 ],
			[ 'technote_test_user_string', 'test' ],
			[
				'technote_test_user_array',
				[
					'test1' => 'test1',
					'test2' => 2,
					'test3' => false,
				],
			],
			[ 'technote_test_user_null', null ],
		];
	}

	/**
	 * @return array
	 */
	public function _test_value_provider() {
		return static::get_test_value();
	}
}
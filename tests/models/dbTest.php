<?php
/**
 * WP_Framework Models Define Test
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework\Tests\Models;

require_once __DIR__ . DS . 'misc' . DS . 'db.php';

/**
 * Class DbTest
 * @package WP_Framework\Tests\Models
 * @group technote
 * @group models
 */
class DbTest extends \WP_Framework\Tests\TestCase {

	/**
	 * @var Misc\Db $_db
	 */
	private static $_db;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$_db = Misc\Db::get_instance( static::$app );
		static::$_db->drop( 'technote_test_table1' );
		static::$_db->drop( 'technote_test_table2' );
		static::$_db->setup( 'technote_test_table1', [
			'id'      => 'test_id',
			'columns' => [
				'value1' => [
					'type'    => 'VARCHAR(32)',
					'null'    => false,
					'default' => 'value1',
				],
				'value2' => [
					'type'    => 'INT(11)',
					'null'    => false,
					'default' => 2,
				],
				'value3' => [
					'type' => 'VARCHAR(32)',
				],
			],
			'index'   => [
				'key' => [
					'value1' => [ 'value1' ],
				],
			],
			'delete'  => 'logical',
		] );
		static::$_db->setup( 'technote_test_table2', [
			'columns' => [
				'value1' => [
					'type'    => 'VARCHAR(32)',
					'null'    => false,
					'default' => 'value1',
				],
				'value2' => [
					'type'    => 'INT(11)',
					'null'    => false,
					'default' => 2,
				],
				'value3' => [
					'type' => 'VARCHAR(32)',
				],
			],
			'index'   => [
				'key'    => [
					'value1' => [ 'value1' ],
				],
				'unique' => [
					'value' => [ 'value1', 'value2' ],
				],
			],
			'delete'  => 'physical',
		] );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		static::$_db->drop( 'technote_test_table1' );
		static::$_db->drop( 'technote_test_table2' );
	}

	public function test_table_not_exists() {
		$this->assertFalse( static::$_db->exists( 'technote_test_table1' ) );
		$this->assertFalse( static::$_db->exists( 'technote_test_table2' ) );
	}

	/**
	 * @depends test_table_not_exists
	 */
	public function test_table_update() {
		$results = static::$_db->_table_update( 'technote_test_table1' );
		$this->assertNotEmpty( $results );
		$results = static::$_db->_table_update( 'technote_test_table2' );
		$this->assertNotEmpty( $results );
	}

	/**
	 * @depends test_table_update
	 */
	public function test_table_exists() {
		$this->assertTrue( static::$_db->exists( 'technote_test_table1' ) );
		$this->assertTrue( static::$_db->exists( 'technote_test_table2' ) );
	}

	/**
	 * @depends test_table_exists
	 */
	public function test_column_check1() {
		$columns = static::$_db->columns( 'technote_test_table1' );
		$columns = array_combine( array_map( function ( $d ) {
			return $d['Field'];
		}, $columns ), $columns );
		$this->assertArrayHasKey( 'test_id', $columns );
		$this->assertArrayHasKey( 'value1', $columns );
		$this->assertArrayHasKey( 'value2', $columns );
		$this->assertArrayHasKey( 'value3', $columns );
		$this->assertArrayHasKey( 'deleted_at', $columns );
		$this->assertArrayHasKey( 'deleted_by', $columns );
	}

	/**
	 * @depends test_column_check1
	 */
	public function test_column_check2() {
		$columns = static::$_db->columns( 'technote_test_table2' );
		$columns = array_combine( array_map( function ( $d ) {
			return $d['Field'];
		}, $columns ), $columns );
		$this->assertArrayHasKey( 'technote_test_table2_id', $columns );
		$this->assertArrayHasKey( 'value1', $columns );
		$this->assertArrayHasKey( 'value2', $columns );
		$this->assertArrayHasKey( 'value3', $columns );
		$this->assertArrayNotHasKey( 'deleted_at', $columns );
		$this->assertArrayNotHasKey( 'deleted_by', $columns );
	}

	/**
	 * @depends test_column_check2
	 */
	public function test_table_update_same() {
		$results = static::$_db->_table_update( 'technote_test_table1' );
		$this->assertEmpty( $results );
		$results = static::$_db->_table_update( 'technote_test_table2' );
		$this->assertEmpty( $results );
	}

	/**
	 * @depends test_table_update_same
	 */
	public function test_table_update_define() {
		static::$_db->setup( 'technote_test_table2', [
			'columns' => [
				'value1' => [
					'type'    => 'VARCHAR(32)',
					'null'    => false,
					'default' => 'value1',
				],
				'value2' => [
					'type'    => 'INT(11)',
					'null'    => false,
					'default' => 2,
				],
				'value3' => [
					'type' => 'VARCHAR(32)',
				],
				'value4' => [
					'type' => 'INT(11)',
				],
			],
			'index'   => [
				'key'    => [
					'value1' => [ 'value1' ],
				],
				'unique' => [
					'value' => [ 'value1', 'value2' ],
				],
			],
			'delete'  => 'physical',
		] );
		$results = static::$_db->_table_update( 'technote_test_table2' );
		$this->assertNotEmpty( $results );
	}

	/**
	 * @depends test_table_update_define
	 */
	public function test_column_check3() {
		$columns = static::$_db->columns( 'technote_test_table2' );
		$columns = array_combine( array_map( function ( $d ) {
			return $d['Field'];
		}, $columns ), $columns );
		$this->assertArrayHasKey( 'value4', $columns );
	}

	/**
	 * @depends test_table_update_define
	 */
	public function test_insert() {
		$this->assertEquals( 1, static::$_db->insert( 'technote_test_table1', [
			'value1' => 'text1',
			'value2' => 1,
			'value3' => 'text3',
		] ) );
		$this->assertEquals( 1, static::$_db->insert( 'technote_test_table2', [
			'value3' => 'text1',
			'value4' => 1,
		] ) );
		$this->assertEquals( 1, static::$_db->insert( 'technote_test_table2', [
			'value2' => 10,
			'value3' => 'text2',
			'value4' => 2,
		] ) );
	}

	/**
	 * @depends test_table_update_define
	 */
	public function test_update() {
		$this->assertEquals( 1, static::$_db->update( 'technote_test_table2', [
			'value3' => 'text3',
			'value4' => 3,
		], [
			'id' => 1,
		] ) );
		$this->assertEquals( 0, static::$_db->update( 'technote_test_table2', [
			'value3' => 'text4',
			'value4' => 4,
		], [
			'id' => 10,
		] ) );
	}

	/**
	 * @depends test_table_update_define
	 */
	public function test_select() {
		$results = static::$_db->select( 'technote_test_table2', [
			'id' => 1,
		] );
		$this->assertNotEmpty( $results );
		$this->assertCount( 1, $results );
		$result = reset( $results );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'value3', $result );
		$this->assertArrayHasKey( 'value4', $result );
		$this->assertEquals( 'text3', $result['value3'] );
		$this->assertEquals( 3, $result['value4'] );
	}

	/**
	 * @depends test_table_update_define
	 */
	public function test_select2() {
		$results = static::$_db->select( 'technote_test_table2', [
			'id' => 10,
		] );
		$this->assertEmpty( $results );
	}

	/**
	 * @depends test_table_update_define
	 */
	public function test_delete() {
		$this->assertEquals( 1, static::$_db->delete( 'technote_test_table1', [
			'id' => 1,
		] ) );
		$this->assertEquals( 0, static::$_db->delete( 'technote_test_table1', [
			'id' => 1,
		] ) );
		$this->assertEquals( 1, static::$_db->delete( 'technote_test_table2', [
			'id' => 1,
		] ) );
		$this->assertEquals( 1, static::$_db->delete( 'technote_test_table2', [
			'id' => 2,
		] ) );
		$this->assertEquals( 0, static::$_db->delete( 'technote_test_table2', [
			'id' => 3,
		] ) );
	}

	/**
	 * @depends test_delete
	 */
	public function test_select3() {
		$results = static::$_db->select( 'technote_test_table1', [
			'id' => 1,
		] );
		$this->assertEmpty( $results );
		$results = static::$_db->select( 'technote_test_table2', [
			'id' => 1,
		] );
		$this->assertEmpty( $results );
	}
}
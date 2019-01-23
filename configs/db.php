<?php
/**
 * WP_Framework_Core Configs Db
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

return [

	// example
//	'test' => array(
//		'id'      => 'test_id',     // optional [default = $table_name . '_id']
//		'columns' => array(
//			'name'   => array(
//				'name'     => 'name',          // optional
//				'type'     => 'VARCHAR(32)',   // required
//				'unsigned' => false,          // optional [default = false]
//				'null'     => true,           // optional [default = true]
//				'default'  => null,           // optional [default = null]
//				'comment'  => '',             // optional
//			),
//			'value1' => array(
//				'type'    => 'VARCHAR(32)',
//				'null'    => false,
//				'default' => 'test',
//			),
//			'value2' => array(
//				'type'    => 'VARCHAR(32)',
//				'comment' => 'aaaa',
//			),
//			'value3' => array(
//				'type'    => 'INT(11)',
//				'null'    => false,
//				'comment' => 'bbb',
//			),
//		),
//		'index'   => array(
//			'key'    => array( // key index
//				'name' => array( 'name' ),
//			),
//			'unique' => array( // unique index
//				'value' => array( 'value1', 'value2' ),
//			),
//		),
//		'delete'  => 'logical', // physical or logical [default = physical]
//	),

	'__log' => [
		'columns' => [
			'level'             => [
				'type' => 'VARCHAR(32)',
				'null' => false,
			],
			'message'           => [
				'type' => 'TEXT',
				'null' => false,
			],
			'context'           => [
				'type' => 'LONGTEXT',
				'null' => true,
			],
			'file'              => [
				'type' => 'VARCHAR(255)',
				'null' => true,
			],
			'line'              => [
				'type'     => 'INT(11)',
				'unsigned' => true,
				'null'     => true,
			],
			'framework_version' => [
				'type' => 'VARCHAR(32)',
				'null' => false,
			],
			'plugin_version'    => [
				'type' => 'VARCHAR(32)',
				'null' => false,
			],
			'php_version'       => [
				'type' => 'VARCHAR(32)',
				'null' => false,
			],
			'wordpress_version' => [
				'type' => 'VARCHAR(32)',
				'null' => false,
			],
		],
		'index'   => [
			'key' => [
				'created_at' => [ 'created_at' ],
			],
		],
	],

];

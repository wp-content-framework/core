<?php
/**
 * WP_Framework Configs Setting
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

	'10' => [
		'Performance' => [
			'10' => [
				'minify_js'  => [
					'label'   => 'Whether to minify js which generated.',
					'type'    => 'bool',
					'default' => true,
				],
				'minify_css' => [
					'label'   => 'Whether to minify css which generated.',
					'type'    => 'bool',
					'default' => true,
				],
			],
		],
	],

	'999' => [
		'Others' => [
			'10' => [
				'admin_menu_position'     => [
					'label'   => 'Admin menu position',
					'type'    => 'int',
					'default' => 100,
					'min'     => 0,
				],
				'check_update'            => [
					'label'   => 'Whether to check develop update.',
					'type'    => 'bool',
					'default' => true,
				],
				'assets_version'          => [
					'label'   => 'Assets Version',
					'type'    => 'string',
					'default' => function ( $app ) {
						/** @var \WP_Framework $app */
						return $app->get_plugin_version();
					},
				],
				'use_admin_ajax'          => [
					'label'   => 'Use admin-ajax.php instead of wp-json.',
					'type'    => 'bool',
					'default' => false,
				],
				'get_nonce_check_referer' => [
					'label'   => 'Whether to check referer when get nonce.',
					'type'    => 'bool',
					'default' => true,
				],
				'check_referer_host'      => [
					'label'   => 'Server host name which used to check referer host name.',
					'default' => function ( $app ) {
						/** @var \WP_Framework $app */
						return $app->input->server( 'HTTP_HOST', '' );
					},
				],
				'is_valid_log'            => [
					'label'   => 'Whether log is valid or not.',
					'type'    => 'bool',
					'default' => function ( $app ) {
						/** @var \WP_Framework $app */
						return ! empty( $app->utility->definedv( 'WP_DEBUG' ) );
					},
				],
				'save___log_term'         => [
					'label'   => 'Save log term (set 0 to prevent save)',
					'default' => 30 * DAY_IN_SECONDS,
					'min'     => 0,
				],
				'delete___log_interval'   => [
					'label'   => 'Delete log interval',
					'default' => DAY_IN_SECONDS,
					'min'     => MINUTE_IN_SECONDS,
				],
				'capture_shutdown_error'  => [
					'label'   => 'Whether to capture shutdown error.',
					'type'    => 'bool',
					'default' => function ( $app ) {
						/** @var \WP_Framework $app */
						return ! empty( $app->get_config( 'config', 'capture_shutdown_error' ) );
					},
				],
			],
		],
	],

];
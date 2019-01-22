<?php
/**
 * WP_Framework Configs Filter
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

	'minify' => [
		'admin_print_footer_scripts' => [
			'output_js' => [ 999 ],
		],
		'admin_head'                 => [
			'output_css' => [ 999 ],
		],
		'admin_footer'               => [
			'output_css' => [ 999 ],
			'end_footer' => [ 999 ],
		],

		'wp_print_footer_scripts' => [
			'output_js'  => [ 999 ],
			'output_css' => [ 998 ],
			'end_footer' => [ 999 ],
		],
		'wp_print_styles'         => [
			'output_css' => [ 999 ],
		],
	],

	'db'   => [
		'switch_blog' => [
			'switch_blog' => [],
		],
	],

	'log'  => [
		'${prefix}app_initialize' => [
			'setup_shutdown' => [],
		],
	],

	'mail' => [
		'wp_mail_failed'    => [
			'wp_mail_failed' => [],
		],
		'wp_mail_from'      => [
			'wp_mail_from' => [],
		],
		'wp_mail_from_name' => [
			'wp_mail_from_name' => [],
		],
	],

	'uninstall' => [
		'${prefix}app_activated' => [
			'register_uninstall' => [],
		],
	],

	'upgrade'   => [
		'init'       => [
			'upgrade' => [],
		],
		'admin_init' => [
			'setup_update' => [],
		],
	],

	'loader->admin' => [
		'admin_menu'    => [
			'add_menu'  => [ 9 ],
			'sort_menu' => [ 11 ],
		],
		'admin_notices' => [
			'admin_notice' => [],
		],
	],

	'loader->api' => [
		'rest_api_init'     => [
			'register_rest_api' => [],
		],
		'admin_init'        => [
			'register_ajax_api' => [],
		],
		'wp_footer'         => [
			'register_script' => [],
		],
		'admin_footer'      => [
			'register_script' => [],
		],
		'rest_pre_dispatch' => [
			'rest_pre_dispatch' => [ 999 ],
		],
	],

];
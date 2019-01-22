<?php
/**
 * WP_Framework Configs Config
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

	// library version
	'library_version'                => '0.0.1',

	// main menu title
	'main_menu_title'                => '',

	// contact url
	'contact_url'                    => '',

	// twitter
	'twitter'                        => '',

	// github
	'github'                         => '',

	// db version
	'db_version'                     => '0.0.1',

	// update
	'update_info_file_url'           => '',

	// readme
	'readme_file_check_url'          => '',

	// menu image url
	'menu_image'                     => '',

	// api version
	'api_version'                    => 'v1',

	// default delete rule
	'default_delete_rule'            => 'physical',

	// prior default (to nullable)
	'prior_default'                  => false,

	// cache filter result
	'cache_filter_result'            => true,

	// cache filter exclude list
	'cache_filter_exclude_list'      => [],

	// prevent use log
	'prevent_use_log'                => false,

	// use custom post
	'use_custom_post'                => false,

	// use social login
	'use_social_login'               => false,

	// capture shutdown error
	'capture_shutdown_error'         => defined( 'WP_DEBUG' ) && WP_DEBUG,

	// target shutdown error
	'target_shutdown_error'          => E_ALL & ~E_NOTICE & ~E_WARNING,

	// log level (for developer)
	'log_level'                      => [
		'error' => [
			'is_valid_log'  => true,
			'is_valid_mail' => true,
			'roles'         => [
				// 'administrator',
			],
			'emails'        => [
				// 'test@example.com',
			],
		],
		'info'  => [
			'is_valid_log'  => true,
			'is_valid_mail' => false,
			'roles'         => [
				// 'administrator',
			],
			'emails'        => [
				// 'test@example.com',
			],
		],
		// set default level
		''      => 'info',
	],

	// suppress setting help contents
	'suppress_setting_help_contents' => false,

	// setting page title
	'setting_page_title'             => 'Dashboard',

	// setting page priority
	'setting_page_priority'          => 0,

	// setting page slug
	'setting_page_slug'              => 'setting',

	// suppress log messages
	'suppress_log_messages'          => [
		'Non-static method WP_Feed_Cache::create() should not be called statically',
	],

];
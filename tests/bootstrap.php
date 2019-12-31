<?php
/**
 * WP_Framework Test Bootstrap
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'ABSPATH' ) ) {
	if ( ! defined( 'DS' ) ) {
		define( 'DS', DIRECTORY_SEPARATOR );
	}

	/**
	 * @param string $dir
	 *
	 * @return string
	 */
	function ___find_wp_blog_header( $dir ) {
		foreach ( scandir( $dir ) as $item ) {
			$path = $dir . DS . $item;
			if ( is_file( $path ) && 'wp-blog-header.php' === $item ) {
				return $path;
			}
		}

		if ( '/' !== $dir ) {
			return ___find_wp_blog_header( dirname( $dir ) );
		}

		return '';
	}

	/** Loads the WordPress Environment and Template */
	$wp_blog_header = ___find_wp_blog_header( __DIR__ );
	if ( empty( $wp_blog_header ) ) {
		fwrite(
			STDERR,
			'wp-blog-header.php not found.' . PHP_EOL
		);

		die( 1 );
	}

	// to prevent error
	$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
	$_SERVER['SERVER_PORT']     = '80';
	$_SERVER['HTTP_HOST']       = '';
	$_SERVER['QUERY_STRING']    = '';
	$_SERVER['REQUEST_METHOD']  = 'GET';
	$_SERVER['SERVER_ADDR']     = '127.0.0.1';
	$_SERVER['SERVER_NAME']     = 'localhost';
	$_SERVER['REQUEST_TIME']    = time();
	$_SERVER['REMOTE_ADDR']     = '127.0.0.1';
	$_SERVER['REQUEST_URI']     = '/';
	$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36';

	// load wordpress
	/** @noinspection PhpIncludeInspection */
	require_once $wp_blog_header;

	// load
	require_once __DIR__ . DS . 'TestCase.php';
}

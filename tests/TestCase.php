<?php
/**
 * WP_Framework_Core Models Define Test
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Core\Tests;

/**
 * Class TestCase
 * @package WP_Framework_Core\Tests
 */
class TestCase extends \PHPUnit\Framework\TestCase {

	/**
	 * @var \WP_Framework|\Phake_IMock
	 */
	protected static $app;

	/**
	 * @var string
	 */
	protected static $plugin_name;

	/**
	 * @var string
	 */
	protected static $plugin_file;

	public static function setUpBeforeClass() {
		static::$app = \Phake::mock( '\WP_Framework' );
		\Phake::when( static::$app )->get_library_directory()->thenReturn( dirname( dirname( __FILE__ ) ) );
		static::$plugin_name      = md5( uniqid() );
		static::$plugin_file      = __FILE__;
		static::$app->plugin_name = static::$plugin_name;
		static::$app->plugin_file = static::$plugin_file;
		static::$app->slug_name   = static::$plugin_name;
		static::$app->define      = \WP_Framework_Common\Classes\Models\Define::get_instance( static::$app );
		static::$app->input       = \WP_Framework_Common\Classes\Models\Input::get_instance( static::$app );
		static::$app->utility     = \WP_Framework_Common\Classes\Models\Utility::get_instance( static::$app );
		static::$app->user        = \WP_Framework_Common\Classes\Models\User::get_instance( static::$app );
	}
}
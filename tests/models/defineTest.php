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

/**
 * Class DefineTest
 * @package WP_Framework\Tests\Models
 * @group technote
 * @group models
 */
class DefineTest extends \WP_Framework\Tests\TestCase {

	/**
	 * @var \WP_Framework\Classes\Models\Define $_define
	 */
	private static $_define;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$_define = static::$app->define;
	}

	public function test_lib_property() {
		$this->assertEquals( WP_CONTENT_FRAMEWORK, static::$_define->lib_name );
		$this->assertEquals( ucfirst( WP_CONTENT_FRAMEWORK ), static::$_define->lib_namespace );
		$this->assertNotEmpty( static::$_define->lib_dir );
		$this->assertNotEmpty( static::$_define->lib_assets_dir );
		$this->assertNotEmpty( static::$_define->lib_src_dir );
		$this->assertNotEmpty( static::$_define->lib_configs_dir );
		$this->assertNotEmpty( static::$_define->lib_views_dir );
		$this->assertNotEmpty( static::$_define->lib_languages_dir );
		$this->assertNotEmpty( static::$_define->lib_vendor_dir );
		$this->assertNotEmpty( static::$_define->lib_assets_url );
	}

	public function test_plugin_property() {
		$this->assertEquals( static::$plugin_name, static::$_define->plugin_name );
		$this->assertEquals( ucfirst( static::$plugin_name ), static::$_define->plugin_namespace );
		$this->assertNotEmpty( static::$_define->plugin_file );
		$this->assertNotEmpty( static::$_define->plugin_dir );
		$this->assertNotEmpty( static::$_define->plugin_dir_name );
		$this->assertNotEmpty( static::$_define->plugin_base_name );
		$this->assertNotEmpty( static::$_define->plugin_assets_dir );
		$this->assertNotEmpty( static::$_define->plugin_src_dir );
		$this->assertNotEmpty( static::$_define->plugin_configs_dir );
		$this->assertNotEmpty( static::$_define->plugin_views_dir );
		$this->assertEmpty( static::$_define->plugin_languages_dir );
		$this->assertNotEmpty( static::$_define->plugin_logs_dir );
		$this->assertNotEmpty( static::$_define->plugin_assets_url );
	}
}
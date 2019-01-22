<?php
/**
 * WP_Framework Models Config Test
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
 * Class ConfigTest
 * @package WP_Framework\Tests\Models
 * @group technote
 * @group models
 */
class ConfigTest extends \WP_Framework\Tests\TestCase {

	/**
	 * @var \WP_Framework\Classes\Models\Config $_config
	 */
	private static $_config;

	/**
	 * @var string $_config_file
	 */
	private static $_config_file;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$_config = \WP_Framework\Classes\Models\Config::get_instance( static::$app );

		static::$_config_file = 'technote_test_config';
		touch( static::$app->define->lib_configs_dir . DS . static::$_config_file . '.php' );
		file_put_contents( static::$app->define->lib_configs_dir . DS . static::$_config_file . '.php', <<< EOS
<?php

return array(

	'test1' => 'test1',
	'test2' => 'test2',

);

EOS
		);

		if ( ! file_exists( static::$app->define->plugin_configs_dir ) ) {
			mkdir( static::$app->define->plugin_configs_dir, true );
		}
		touch( static::$app->define->plugin_configs_dir . DS . static::$_config_file . '.php' );
		file_put_contents( static::$app->define->plugin_configs_dir . DS . static::$_config_file . '.php', <<< EOS
<?php

return array(

	'test2' => 'test3',
	'test4' => 'test4',

);

EOS
		);
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		if ( file_exists( static::$app->define->plugin_configs_dir . DS . static::$_config_file . '.php' ) ) {
			unlink( static::$app->define->plugin_configs_dir . DS . static::$_config_file . '.php' );
		}
		if ( file_exists( static::$app->define->lib_configs_dir . DS . static::$_config_file . '.php' ) ) {
			unlink( static::$app->define->lib_configs_dir . DS . static::$_config_file . '.php' );
		}
	}

	public function test_get_only_lib_config() {
		$this->assertEquals( 'test1', static::$_config->get( static::$_config_file, 'test1' ) );
	}

	public function test_overwrite_config() {
		$this->assertEquals( 'test3', static::$_config->get( static::$_config_file, 'test2' ) );
	}

	public function test_get_only_plugin_config() {
		$this->assertEquals( 'test4', static::$_config->get( static::$_config_file, 'test4' ) );
	}

	public function test_default() {
		$this->assertEquals( 'test6', static::$_config->get( static::$_config_file, 'test5', 'test6' ) );
	}
}
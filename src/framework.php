<?php
/**
 * WP_Framework
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	return;
}
define( 'WP_FRAMEWORK_IS_MOCK', false );

/**
 * Class WP_Framework
 * @property bool $is_theme
 * @property string $original_plugin_name
 * @property string $plugin_name
 * @property string $slug_name
 * @property string $plugin_file
 * @property \WP_Framework\Classes\Models\Define $define
 * @property \WP_Framework\Classes\Models\Config $config
 * @property \WP_Framework\Classes\Models\Setting $setting
 * @property \WP_Framework\Classes\Models\Option $option
 * @property \WP_Framework\Classes\Models\Device $device
 * @property \WP_Framework\Classes\Models\Minify $minify
 * @property \WP_Framework\Classes\Models\Filter $filter
 * @property \WP_Framework\Classes\Models\User $user
 * @property \WP_Framework\Classes\Models\Post $post
 * @property \WP_Framework\Classes\Models\Loader $loader
 * @property \WP_Framework\Classes\Models\Log $log
 * @property \WP_Framework\Classes\Models\Input $input
 * @property \WP_Framework\Classes\Models\Db $db
 * @property \WP_Framework\Classes\Models\Uninstall $uninstall
 * @property \WP_Framework\Classes\Models\Session $session
 * @property \WP_Framework\Classes\Models\Utility $utility
 * @property \WP_Framework\Classes\Models\Test $test
 * @property \WP_Framework\Classes\Models\Upgrade $upgrade
 * @property \WP_Framework\Classes\Models\Social $social
 * @property \WP_Framework\Classes\Models\Custom_Post $custom_post
 * @property \WP_Framework\Classes\Models\Mail $mail
 * @method void main_init()
 * @method bool has_initialized()
 * @method string get_plugin_version()
 * @method string|false get_text_domain()
 * @method string translate( string $value )
 * @method mixed get_config( string $name, string $key, mixed $default = null )
 * @method mixed get_option( string $key, mixed $default = '' )
 * @method mixed get_session( string $key, mixed $default = '' )
 * @method mixed set_session( string $key, mixed $value, int | null $duration = null )
 * @method bool user_can( null | string | false $capability = null )
 * @method void log( string $message, mixed $context = null, string $level = '' )
 * @method void add_message( string $message, string $group = '', bool $error = false, bool $escape = true )
 * @method string get_page_slug( string $file )
 * @method mixed get_shared_object( string $key, string | null $target = null )
 * @method void set_shared_object( string $key, mixed $object, string | null $target = null )
 * @method bool isset_shared_object( string $key, string | null $target = null )
 * @method void delete_shared_object( string $key, string | null $target = null )
 * @method array|string get_plugin_data( string | null $key = null )
 */
class WP_Framework {

	/**
	 * @var \WP_Framework[] $_instances
	 */
	private static $_instances = [];

	/**
	 * @var string $_latest_library_version
	 */
	private static $_latest_library_version = null;

	/**
	 * @var string $_latest_library_directory
	 */
	private static $_latest_library_directory = null;

	/**
	 * @var string $_library_version
	 */
	private $_library_version;

	/**
	 * @var string $_library_directory
	 */
	private $_library_directory;

	/**
	 * @var bool $_plugins_loaded
	 */
	private $_plugins_loaded = false;

	/**
	 * @var \WP_Framework\Classes\Models\Main $_main
	 */
	private $_main;

	/**
	 * @var bool $_is_uninstall
	 */
	private $_is_uninstall = false;

	/**
	 * @var bool $is_theme
	 */
	public $is_theme = false;

	/**
	 * @var string $original_plugin_name
	 */
	public $original_plugin_name;

	/**
	 * @var string $plugin_name
	 */
	public $plugin_name;

	/**
	 * @var string $plugin_file
	 */
	public $plugin_file;

	/**
	 * @var string $slug_name
	 */
	public $slug_name;

	/**
	 * WP_Framework constructor.
	 *
	 * @param string $plugin_name
	 * @param string $plugin_file
	 * @param string|null $slug_name
	 */
	private function __construct( $plugin_name, $plugin_file, $slug_name ) {
		$theme_dir                  = str_replace( '/', DS, WP_CONTENT_DIR . DS . 'theme' );
		$this->is_theme             = preg_match( "#\A{$theme_dir}#", str_replace( '/', DS, $plugin_file ) ) > 0;
		$this->original_plugin_name = $plugin_name;
		$this->plugin_file          = $plugin_file;
		$this->plugin_name          = strtolower( $this->original_plugin_name );
		$this->slug_name            = ! empty( $slug_name ) ? strtolower( $slug_name ) : $this->plugin_name;

		$this->setup_library_version();
		$this->setup_actions();
	}

	/**
	 * @param string $name
	 *
	 * @return \WP_Framework\Interfaces\Singleton
	 * @throws \OutOfRangeException
	 */
	public function __get( $name ) {
		return $this->get_main()->__get( $name );
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		return $this->get_main()->__isset( $name );
	}

	/**
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		return $this->get_main()->$name( ...$arguments );
	}

	/**
	 * @param string $name
	 * @param array $arguments
	 */
	public static function __callStatic( $name, $arguments ) {
		if ( preg_match( '#register_uninstall_(.+)\z#', $name, $matches ) ) {
			$plugin_base_name = $matches[1];
			self::uninstall( $plugin_base_name );
		}
	}

	/**
	 * @return \WP_Framework\Classes\Models\Main|\WP_Framework\Interfaces\Singleton
	 */
	private function get_main() {
		if ( ! isset( $this->_main ) ) {
			$required = [
				'Interfaces\Readonly',
				'Interfaces\Singleton',
				'Traits\Readonly',
				'Traits\Singleton',
				'Classes\Models\Main',
			];
			$dir      = self::$_latest_library_directory . DS . 'src';
			foreach ( $required as $item ) {
				$path = $dir . DS . str_replace( '\\', DS, strtolower( $item ) ) . '.php';
				if ( is_readable( $path ) ) {
					/** @noinspection PhpIncludeInspection */
					require_once $path;
				}
			}
			$this->_main = \WP_Framework\Classes\Models\Main::get_instance( $this );
		}

		return $this->_main;
	}

	/**
	 * @param string $plugin_name
	 * @param string $plugin_file
	 * @param string|null $slug_name
	 *
	 * @return WP_Framework
	 */
	public static function get_instance( $plugin_name, $plugin_file, $slug_name = null ) {
		if ( ! isset( self::$_instances[ $plugin_name ] ) ) {
			$instances                        = new static( $plugin_name, $plugin_file, $slug_name );
			self::$_instances[ $plugin_name ] = $instances;

			$latest  = self::$_latest_library_version;
			$version = $instances->_library_version;
			if ( ! isset( $latest ) || version_compare( $latest, $version, '<' ) ) {
				self::$_latest_library_version   = $version;
				self::$_latest_library_directory = $instances->_library_directory;
			}
		}

		return self::$_instances[ $plugin_name ];
	}

	/**
	 * setup library version
	 */
	private function setup_library_version() {
		$library_directory = dirname( $this->plugin_file ) . DS . 'vendor' . DS . 'wp-content-framework' . DS . 'core';
		$config_path       = $library_directory . DS . 'configs' . DS . 'config.php';

		if ( is_readable( $config_path ) ) {
			/** @noinspection PhpIncludeInspection */
			$config = include $config_path;
			if ( ! is_array( $config ) || empty( $config['library_version'] ) ) {
				$library_version = '0.0.0';
			} else {
				$library_version = $config['library_version'];
			}
		} else {
			$library_version   = '0.0.0';
			$library_directory = dirname( WP_FRAMEWORK_BOOTSTRAP );
		}
		$this->_library_version   = $library_version;
		$this->_library_directory = $library_directory;
	}

	/**
	 * setup actions
	 */
	private function setup_actions() {
		if ( $this->is_theme ) {
			add_action( 'after_setup_theme', function () {
				$this->plugins_loaded();
			} );

			add_action( 'after_switch_theme', function () {
				$this->plugins_loaded();
				$this->main_init();
				$this->filter->do_action( 'app_activated', $this );
			} );

			add_action( 'switch_theme', function () {
				$this->filter->do_action( 'app_deactivated', $this );
			} );
		} else {
			add_action( 'plugins_loaded', function () {
				$this->plugins_loaded();
			} );

			add_action( 'activated_plugin', function ( $plugin ) {
				$this->plugins_loaded();
				$this->main_init();
				if ( $this->define->plugin_base_name === $plugin ) {
					$this->filter->do_action( 'app_activated', $this );
				}
			} );

			add_action( 'deactivated_plugin', function ( $plugin ) {
				if ( $this->define->plugin_base_name === $plugin ) {
					$this->filter->do_action( 'app_deactivated', $this );
				}
			} );
		}

		add_action( 'init', function () {
			$this->main_init();
		}, 1 );
	}

	/**
	 * load basic files
	 */
	private function plugins_loaded() {
		if ( $this->_plugins_loaded ) {
			return;
		}
		$this->_plugins_loaded = true;

		spl_autoload_register( function ( $class ) {
			return $this->get_main()->load_class( $class );
		} );

		$this->load_functions();
	}

	/**
	 * load functions file
	 */
	private function load_functions() {
		if ( $this->is_theme ) {
			return;
		}
		$functions = $this->define->plugin_dir . DS . 'functions.php';
		if ( is_readable( $functions ) ) {
			/** @noinspection PhpIncludeInspection */
			require_once $functions;
		}
	}

	/**
	 * @param string $plugin_base_name
	 */
	private static function uninstall( $plugin_base_name ) {
		$app = self::find_plugin( $plugin_base_name );
		if ( ! isset( $app ) ) {
			return;
		}

		$app->_is_uninstall = true;
		$app->plugins_loaded();
		$app->main_init();
		$app->uninstall->uninstall();
	}

	/**
	 * @param string $plugin_base_name
	 *
	 * @return \WP_Framework|null
	 */
	private static function find_plugin( $plugin_base_name ) {
		/** @var \WP_Framework $instance */
		foreach ( self::$_instances as $plugin_name => $instance ) {
			if ( $instance->is_theme ) {
				continue;
			}
			$instance->plugins_loaded();
			if ( $instance->define->plugin_base_name === $plugin_base_name ) {
				return $instance;
			}
		}

		return null;
	}

	/**
	 * @return string
	 */
	public function get_library_directory() {
		return self::$_latest_library_directory;
	}

	/**
	 * @return string
	 */
	public function get_library_version() {
		return self::$_latest_library_version;
	}

	/**
	 * @return bool
	 */
	public function is_uninstall() {
		return $this->_is_uninstall;
	}
}

if ( ! defined( 'PHPUNIT_COMPOSER_INSTALL' ) ) {
	require_once __DIR__ . DS . 'classes' . DS . 'wp-rest-request.php';
	require_once __DIR__ . DS . 'classes' . DS . 'wp-rest-response.php';
}

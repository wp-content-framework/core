<?php
/**
 * WP_Framework
 *
 * @version 0.0.2
 * @author technote-space
 * @since 0.0.1
 * @since 0.0.2 Added: send_mail の追加 (#4)
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
 * @property string $plugin_dir
 * @property string $relative_path
 *
 * @property \WP_Framework_Common\Classes\Models\Define $define
 * @property \WP_Framework_Common\Classes\Models\Config $config
 * @property \WP_Framework_Common\Classes\Models\Setting $setting
 * @property \WP_Framework_Common\Classes\Models\Filter $filter
 * @property \WP_Framework_Common\Classes\Models\Uninstall $uninstall
 * @property \WP_Framework_Common\Classes\Models\Utility $utility
 * @property \WP_Framework_Common\Classes\Models\Upgrade $upgrade
 * @property \WP_Framework_Common\Classes\Models\Option $option
 * @property \WP_Framework_Common\Classes\Models\User $user
 * @property \WP_Framework_Common\Classes\Models\Input $input
 * @property \WP_Framework_Db\Classes\Models\Db $db
 * @property \WP_Framework_Log\Classes\Models\Log $log
 * @property \WP_Framework_Admin\Classes\Models\Admin $admin
 * @property \WP_Framework_Api\Classes\Models\Api $api
 * @property \WP_Framework_Presenter\Classes\Models\Minify $minify
 * @property \WP_Framework_Mail\Classes\Models\Mail $mail
 * @property \WP_Framework_Test\Classes\Models\Test $test
 * @property \WP_Framework_Cron\Classes\Models\Cron $cron
 * @property \WP_Framework_Custom_Post\Classes\Models\Custom_Post $custom_post
 * @property \WP_Framework_Device\Classes\Models\Device $device
 * @property \WP_Framework_Session\Classes\Models\Session $session
 * @property \WP_Framework_Social\Classes\Models\Social $social
 * @property \WP_Framework_Post\Classes\Models\Post $post
 *
 * @method void main_init()
 * @method bool has_initialized()
 * @method string get_plugin_version()
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
 * @method bool send_mail( string $to, string $subject, string | array $body, string | false $text = false )
 */
class WP_Framework {

	/**
	 * @var \WP_Framework[] $_instances
	 */
	private static $_instances = [];

	/**
	 * @var array $_framework_package_versions (package => version)
	 */
	private static $_framework_package_versions = [];

	/**
	 * @var array $_framework_package_plugin_names (package => plugin_name)
	 */
	private static $_framework_package_plugin_names = [];

	/**
	 * @var bool $_is_framework_initialized
	 */
	private static $_is_framework_initialized = false;

	/**
	 * @var \WP_Framework\Package_Base[]
	 */
	private static $_packages = [];

	/**
	 * @var array $_package_versions (package => version)
	 */
	private $_package_versions;

	/**
	 * @var array $_package_directories (package => directory)
	 */
	private $_package_directories;

	/**
	 * @var \WP_Framework\Package_Base[]
	 */
	private $_available_packages;

	/**
	 * @var string $_framework_root_directory
	 */
	private $_framework_root_directory;

	/**
	 * @var bool $_plugins_loaded
	 */
	private $_plugins_loaded = false;

	/**
	 * @var \WP_Framework_Core\Classes\Main $_main
	 */
	private $_main;

	/**
	 * @var bool $_is_uninstall
	 */
	private $_is_uninstall = false;

	/**
	 * @var array $readonly_properties
	 */
	private $_readonly_properties = [
		'is_theme'             => false,
		'original_plugin_name' => '',
		'plugin_name'          => '',
		'slug_name'            => '',
		'plugin_file'          => '',
		'plugin_dir'           => '',
		'relative_path'        => '',
	];

	/** @var bool $_is_allowed_access */
	private $_is_allowed_access = false;

	/**
	 * WP_Framework constructor.
	 *
	 * @param string $plugin_name
	 * @param string $plugin_file
	 * @param string|null $slug_name
	 * @param string|null $relative
	 */
	private function __construct( $plugin_name, $plugin_file, $slug_name, $relative ) {
		$this->_is_allowed_access   = true;
		$theme_dir                  = str_replace( '/', DS, WP_CONTENT_DIR . DS . 'theme' );
		$relative                   = ! empty( $relative ) ? trim( $relative ) : null;
		$this->is_theme             = preg_match( "#\A{$theme_dir}#", str_replace( '/', DS, $plugin_file ) ) > 0;
		$this->original_plugin_name = $plugin_name;
		$this->plugin_file          = $plugin_file;
		$this->plugin_dir           = dirname( $plugin_file );
		$this->relative_path        = empty( $relative ) ? '' : ( trim( str_replace( '/', DS, $relative ), DS ) . DS );
		$this->plugin_name          = strtolower( $this->original_plugin_name );
		$this->slug_name            = ! empty( $slug_name ) ? strtolower( $slug_name ) : $this->plugin_name;
		$this->_is_allowed_access   = false;

		$this->setup_framework_version();
		$this->setup_actions();
	}

	/**
	 * @param string $name
	 *
	 * @return mixed
	 * @throws \OutOfRangeException
	 */
	public function __get( $name ) {
		if ( isset( $this->_readonly_properties[ $name ] ) ) {
			return $this->_readonly_properties[ $name ];
		}

		return $this->get_main()->__get( $name );
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 *
	 * @throws \OutOfRangeException
	 */
	public function __set( $name, $value ) {
		if ( $this->_is_allowed_access && array_key_exists( $name, $this->_readonly_properties ) ) {
			$this->_readonly_properties[ $name ] = $value;
		} else {
			throw new \OutOfRangeException( sprintf( 'you cannot access %s->%s.', static::class, $name ) );
		}
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
	 * @param string $plugin_name
	 * @param string|null $plugin_file
	 * @param string|null $slug_name
	 * @param string|null $relative
	 *
	 * @return WP_Framework
	 */
	public static function get_instance( $plugin_name, $plugin_file = null, $slug_name = null, $relative = null ) {
		if ( ! isset( self::$_instances[ $plugin_name ] ) ) {
			if ( empty( $plugin_file ) ) {
				self::wp_die( '$plugin_file is required.', __FILE__, __LINE__ );
			}
			$instances                        = new static( $plugin_name, $plugin_file, $slug_name, $relative );
			self::$_instances[ $plugin_name ] = $instances;
			self::update_framework_packages( $instances );
		}

		return self::$_instances[ $plugin_name ];
	}

	/**
	 * @return \WP_Framework\Package_Base[]
	 */
	public function get_packages() {
		if ( ! isset( $this->_available_packages ) ) {
			$packages                   = $this->get_package_names();
			$this->_package_directories = [];
			foreach ( self::$_packages as $package => $instance ) {
				if ( in_array( $package, $packages ) ) {
					$this->_available_packages[ $package ] = $instance;
				}
			}
		}

		return $this->_available_packages;
	}

	/**
	 * @return string[]
	 */
	public function get_package_names() {
		if ( ! $this->_plugins_loaded ) {
			self::wp_die( 'framework is not ready.', __FILE__, __LINE__ );
		}

		return array_keys( $this->_package_versions );
	}

	/**
	 * @return string[]
	 */
	public function get_package_directories() {
		if ( ! isset( $this->_package_directories ) ) {
			$this->_package_directories = [];
			foreach ( $this->get_packages() as $package => $instance ) {
				$this->_package_directories[ $package ] = $instance->get_dir();
			}
		}

		return $this->_package_directories;
	}

	/**
	 * @param string $package
	 *
	 * @return bool
	 */
	public function is_valid_package( $package ) {
		return isset( $this->_available_packages[ $package ] );
	}

	/**
	 * @param string $package
	 *
	 * @return \WP_Framework\Package_Base
	 */
	public function get_package_instance( $package = 'core' ) {
		if ( ! isset( $this->_available_packages[ $package ] ) ) {
			self::wp_die( 'package is not available.', __FILE__, __LINE__ );
		}

		return $this->_available_packages[ $package ];
	}

	/**
	 * @param string $package
	 *
	 * @return string
	 */
	public function get_package_directory( $package = 'core' ) {
		$dirs = $this->get_package_directories();
		if ( ! isset( $dirs[ $package ] ) ) {
			self::wp_die( [ 'package is not included.', 'package name: ' . $package ], __FILE__, __LINE__ );
		}

		return $dirs[ $package ];
	}

	/**
	 * @param string $package
	 *
	 * @return string
	 */
	public function get_package_version( $package = 'core' ) {
		if ( ! $this->_plugins_loaded ) {
			self::wp_die( 'framework is not ready.', __FILE__, __LINE__ );
		}
		if ( ! isset( $this->_package_versions[ $package ] ) ) {
			self::wp_die( [ 'package is not included.', 'package name: ' . $package ], __FILE__, __LINE__ );
		}

		return self::$_framework_package_versions[ $package ];
	}

	/**
	 * @return string
	 */
	public function get_framework_version() {
		return $this->get_package_version();
	}

	/**
	 * @return bool
	 */
	public function is_uninstall() {
		return $this->_is_uninstall;
	}

	/**
	 * @param string|array $message
	 * @param string $file
	 * @param int $line
	 * @param string $title
	 */
	public static function wp_die( $message, $file, $line, $title = '' ) {
		! is_array( $message ) and $message = [ '[wp content framework]', $message ];
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$message[] = 'File: ' . $file;
			$message[] = 'Line: ' . $line;
		}
		$message = '<ul><li>' . implode( '</li><li>', $message ) . '</li></ul>';
		wp_die( $message, $title );
		exit;
	}

	/**
	 * @return \WP_Framework_Core\Classes\Main|\WP_Framework_Core\Interfaces\Singleton
	 */
	private function get_main() {
		if ( ! $this->_plugins_loaded ) {
			self::wp_die( 'framework is not ready.', __FILE__, __LINE__ );
		}
		if ( ! isset( $this->_main ) ) {
			$required = [
				'Classes\Main',
			];
			$dir      = $this->get_package_directory() . DS . 'src';
			foreach ( $required as $item ) {
				$path = $dir . DS . str_replace( '\\', DS, strtolower( $item ) ) . '.php';
				if ( is_readable( $path ) ) {
					/** @noinspection PhpIncludeInspection */
					require_once $path;
				}
			}
			$this->_main = \WP_Framework_Core\Classes\Main::get_instance( $this );
		}

		return $this->_main;
	}

	/**
	 * setup framework version
	 */
	private function setup_framework_version() {
		$composer = $this->plugin_dir . DS . 'composer.lock';
		if ( ! file_exists( $composer ) || ! is_readable( $composer ) ) {
			self::wp_die( 'composer.lock not found.', __FILE__, __LINE__ );
		}
		$json = json_decode( file_get_contents( $composer ), true );
		if ( empty( $json ) ) {
			self::wp_die( 'composer.lock is invalid.', __FILE__, __LINE__ );
		}

		$versions = [];
		foreach ( $json['packages'] as $package ) {
			$name     = $package['name'];
			$exploded = explode( '/', $name );
			if ( count( $exploded ) !== 2 || WP_FRAMEWORK_VENDOR_NAME !== $exploded[0] ) {
				continue;
			}

			$version                                = ltrim( $package['version'], 'v.' );
			$versions[ strtolower( $exploded[1] ) ] = $version;
		}
		if ( ! isset( $versions['core'] ) ) {
			self::wp_die( 'composer.lock is invalid.', __FILE__, __LINE__ );
		}
		$this->_framework_root_directory = $this->plugin_dir . DS . $this->relative_path . 'vendor' . DS . WP_FRAMEWORK_VENDOR_NAME;
		$this->_package_versions         = $versions;
	}

	/**
	 * @param \WP_Framework $app
	 */
	private static function update_framework_packages( \WP_Framework $app ) {
		foreach ( $app->_package_versions as $package => $version ) {
			if ( ! isset( self::$_framework_package_versions[ $package ] ) || version_compare( self::$_framework_package_versions[ $package ], $version, '<' ) ) {
				self::$_framework_package_versions[ $package ]     = $version;
				self::$_framework_package_plugin_names[ $package ] = $app->original_plugin_name;
			}
		}
	}

	/**
	 * initialize framework
	 */
	private static function initialize_framework() {
		require_once dirname( WP_FRAMEWORK_BOOTSTRAP ) . DS . 'package_base.php';
		$priority = [];
		$packages = [];
		foreach ( self::$_framework_package_plugin_names as $package => $plugin_name ) {
			$app       = self::$_instances[ $plugin_name ];
			$directory = $app->_framework_root_directory . DS . $package;
			$path      = $directory . DS . 'package_' . $package . '.php';
			if ( ! is_readable( $path ) ) {
				self::wp_die( sprintf( 'invalid package [%s]', $package ), __FILE__, __LINE__ );
			}
			/** @noinspection PhpIncludeInspection */
			require_once $path;

			$class = '\WP_Framework\Package_' . ucwords( $package, '_' );
			if ( ! class_exists( $class ) ) {
				self::wp_die( sprintf( 'invalid package [%s]', $package ), __FILE__, __LINE__ );
			}

			$version = self::$_framework_package_versions[ $package ];
			/** @var \WP_Framework\Package_Base $class */
			$packages[ $package ] = $class::get_instance( $app, $package, $directory, $version );
			$priority[ $package ] = $packages[ $package ]->get_priority();
		}
		array_multisort( $priority, $packages );
		self::$_packages = [];
		foreach ( $packages as $package ) {
			/** @var \WP_Framework\Package_Base $package */
			self::$_packages[ $package->get_package() ] = $package;
		}
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

		if ( ! self::$_is_framework_initialized ) {
			self::$_is_framework_initialized = true;
			self::initialize_framework();
		}

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
}

if ( ! defined( 'PHPUNIT_COMPOSER_INSTALL' ) ) {
	require_once __DIR__ . DS . 'classes' . DS . 'wp-rest-request.php';
	require_once __DIR__ . DS . 'classes' . DS . 'wp-rest-response.php';
}

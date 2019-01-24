<?php
/**
 * WP_Framework_Core Classes Main
 *
 * @version 0.0.4
 * @author technote-space
 * @since 0.0.1
 * @since 0.0.2 Added: send_mail の追加 (#4)
 * @since 0.0.4 Fixed: 複数プラグインでの利用への対応 (#8)
 * @since 0.0.4 Changed: 利用できないプロパティへのアクセスの動作変更 (#9)
 * @since 0.0.5 Improved: クラス読み込みの改善 (#13)
 * @since 0.0.5 Fixed: プラグインの名前空間のクラスが読みこまれない (#14)
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Core\Classes;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Main
 * @package WP_Framework_Core\Classes
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
 */
class Main {

	/**
	 * @since 0.0.4 #8
	 * @var Main[] $_instances
	 */
	private static $_instances = [];

	/**
	 * @var array $_shared_object
	 */
	private static $_shared_object = [];

	/**
	 * @var \WP_Framework $app
	 */
	protected $app;

	/**
	 * @var bool $_initialized
	 */
	private $_initialized = false;

	/**
	 * @var array $_plugin_data
	 */
	private $_plugin_data;

	/**
	 * @var array $_properties
	 */
	private $_properties;

	/**
	 * @var array $_property_instances
	 */
	private $_property_instances = [];

	/**
	 * @since 0.0.5 #13
	 * @var string $_namespace_prefix
	 */
	private $_namespace_prefix = WP_CONTENT_FRAMEWORK . '_';

	/**
	 * @since 0.0.4 #8
	 *
	 * @param \WP_Framework $app
	 *
	 * @return Main
	 */
	public static function get_instance( \WP_Framework $app ) {
		! isset( self::$_instances[ $app->plugin_name ] ) and self::$_instances[ $app->plugin_name ] = new self( $app );

		return self::$_instances[ $app->plugin_name ];
	}

	/**
	 * Main constructor.
	 *
	 * @param \WP_Framework $app
	 */
	private function __construct( \WP_Framework $app ) {
		$this->app = $app;
		$this->initialize();
	}

	/**
	 * @param string $name
	 *
	 * @return \WP_Framework_Core\Interfaces\Singleton
	 * @throws \OutOfRangeException
	 */
	public function __get( $name ) {
		return $this->get( $name );
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		return array_key_exists( $name, $this->_properties );
	}

	/**
	 * initialize
	 */
	protected function initialize() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$this->_plugin_data = $this->app->is_theme ? wp_get_theme() : get_plugin_data( $this->app->plugin_file, false, false );
		$this->_properties  = [];
		foreach ( $this->app->get_packages() as $package ) {
			$this->_properties = array_merge( $this->_properties, $package->get_config( 'map' ) );
		}
	}

	/**
	 * @since 0.0.4 #9
	 *
	 * @param string $name
	 *
	 * @return \WP_Framework_Core\Interfaces\Singleton
	 */
	public function get( $name ) {
		if ( isset( $this->_properties[ $name ] ) ) {
			$class = $this->_properties[ $name ];
			if ( ! isset( $this->_property_instances[ $class ] ) ) {
				/** @var \WP_Framework_Core\Interfaces\Singleton $class */
				try {
					$this->_property_instances[ $class ] = $class::get_instance( $this->app );
				} catch ( \Exception $e ) {
					\WP_Framework::wp_die( $e->getMessage(), __FILE__, __LINE__ );
				}
			}

			return $this->_property_instances[ $class ];
		}
		\WP_Framework::wp_die( $name . ' is undefined.', __FILE__, __LINE__ );

		return null;
	}

	/**
	 * @since 0.0.5 #13, #14
	 *
	 * @param string $class
	 *
	 * @return bool
	 */
	public function load_class( $class ) {
		$class = ltrim( $class, '\\' );
		$dirs  = null;

		if ( isset( $this->_property_instances[ $this->_properties['define'] ] ) && preg_match( "#\A{$this->define->plugin_namespace}#", $class ) ) {
			$class = preg_replace( "#\A{$this->define->plugin_namespace}#", '', $class );
			$dirs  = $this->define->plugin_src_dir;
		} else {
			if ( preg_match( "#\A{$this->_namespace_prefix}#", $class ) ) {
				foreach ( $this->app->get_packages() as $package ) {
					if ( $package->load_class( $class ) ) {
						return true;
					}
				}
			}
		}

		if ( isset( $dirs ) ) {
			$class = ltrim( $class, '\\' );
			$class = strtolower( $class );
			! is_array( $dirs ) and $dirs = [ $dirs ];
			foreach ( $dirs as $dir ) {
				$path = $dir . DS . str_replace( '\\', DS, $class ) . '.php';
				if ( is_readable( $path ) ) {
					/** @noinspection PhpIncludeInspection */
					require_once $path;

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * main init
	 */
	public function main_init() {
		if ( $this->_initialized ) {
			return;
		}
		$this->_initialized = true;

		$this->filter->do_action( 'app_initialize', $this );
		$this->setup_property();
		$this->filter->do_action( 'app_initialized', $this );
	}

	/**
	 * setup property
	 */
	private function setup_property() {
		if ( $this->app->is_uninstall() ) {
			foreach ( $this->_properties as $name => $class ) {
				if ( $this->app->is_valid_package( $name ) ) {
					continue;
				}
				$this->$name;
			}
			$this->uninstall->get_class_list();
		}
	}

	/**
	 * @return bool
	 */
	public function has_initialized() {
		return $this->_initialized;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function is_loaded( $name ) {
		return isset( $this->_property_instances[ $this->app->plugin_name ][ $name ] );
	}

	/**
	 * @param string|null $key
	 *
	 * @return array|string
	 */
	public function get_plugin_data( $key = null ) {
		return empty( $key ) ? $this->_plugin_data : $this->_plugin_data[ $key ];
	}

	/**
	 * @return string
	 */
	public function get_plugin_version() {
		return $this->get_plugin_data( 'Version' );
	}

	/**
	 * @param string $name
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get_config( $name, $key, $default = null ) {
		return $this->config->get( $name, $key, $default );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get_option( $key, $default = '' ) {
		return $this->option->get( $key, $default );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get_session( $key, $default = null ) {
		return $this->session->get( $key, $default );
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param int|null $duration
	 */
	public function set_session( $key, $value, $duration = null ) {
		$this->session->set( $key, $value, $duration );
	}

	/**
	 * @param null|string|false $capability
	 *
	 * @return bool
	 */
	public function user_can( $capability = null ) {
		return $this->user->user_can( $capability );
	}

	/**
	 * @param string $message
	 * @param mixed $context
	 * @param string $level
	 */
	public function log( $message, $context = null, $level = '' ) {
		if ( ! $this->app->is_valid_package( 'log' ) ) {
			return;
		}
		if ( $message instanceof \Exception ) {
			$this->log->log( $message->getMessage(), isset( $context ) ? $context : $message->getTraceAsString(), empty( $level ) ? 'error' : $level );
		} elseif ( $message instanceof \WP_Error ) {
			$this->log->log( $message->get_error_message(), isset( $context ) ? $context : $message->get_error_data(), empty( $level ) ? 'error' : $level );
		} else {
			$this->log->log( $message, $context, $level );
		}
	}

	/**
	 * @param string $message
	 * @param string $group
	 * @param bool $error
	 * @param bool $escape
	 */
	public function add_message( $message, $group = '', $error = false, $escape = true ) {
		if ( ! $this->app->is_valid_package( 'admin' ) ) {
			return;
		}
		if ( ! isset( $this->admin ) ) {
			add_action( 'admin_notices', function () use ( $message, $group, $error, $escape ) {
				$this->admin->add_message( $message, $group, $error, $escape );
			}, 9 );
		} else {
			$this->admin->add_message( $message, $group, $error, $escape );
		}
	}

	/**
	 * @since 0.0.2 #4
	 *
	 * @param string $to
	 * @param string $subject
	 * @param string|array $body
	 * @param string|false $text
	 *
	 * @return bool
	 */
	public function send_mail( $to, $subject, $body, $text = false ) {
		if ( ! $this->app->is_valid_package( 'mail' ) ) {
			return false;
		}

		return $this->mail->send( $to, $subject, $body, $text );
	}

	/**
	 * @param string $file
	 *
	 * @return string
	 */
	public function get_page_slug( $file ) {
		return basename( $file, '.php' );
	}

	/**
	 * @param string $key
	 * @param string|null $target
	 *
	 * @return mixed
	 */
	public function get_shared_object( $key, $target = null ) {
		! isset( $target ) and $target = $this->app->plugin_name;

		return isset( self::$_shared_object[ $target ][ $key ] ) ? self::$_shared_object[ $target ][ $key ] : null;
	}

	/**
	 * @param string $key
	 * @param mixed $object
	 * @param string|null $target
	 */
	public function set_shared_object( $key, $object, $target = null ) {
		! isset( $target ) and $target = $this->app->plugin_name;
		self::$_shared_object[ $target ][ $key ] = $object;
	}

	/**
	 * @param string $key
	 * @param string|null $target
	 *
	 * @return bool
	 */
	public function isset_shared_object( $key, $target = null ) {
		! isset( $target ) and $target = $this->app->plugin_name;

		return isset( self::$_shared_object[ $target ] ) && array_key_exists( $key, self::$_shared_object[ $target ] );
	}

	/**
	 * @param string $key
	 * @param string|null $target
	 */
	public function delete_shared_object( $key, $target = null ) {
		! isset( $target ) and $target = $this->app->plugin_name;
		unset( self::$_shared_object[ $target ][ $key ] );
	}
}

<?php
/**
 * WP_Framework Classes Models Main
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework\Classes\Models;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Main
 * @package WP_Framework\Classes\Models
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
 */
class Main implements \WP_Framework\Interfaces\Singleton {

	use \WP_Framework\Traits\Singleton;

	/**
	 * @var bool $_lib_language_loaded
	 */
	private static $_lib_language_loaded = false;

	/**
	 * @var array $_shared_object
	 */
	private static $_shared_object = [];

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
	private $_properties = [
		'define'      => '\WP_Framework\Classes\Models\Define',
		'config'      => '\WP_Framework\Classes\Models\Config',
		'setting'     => '\WP_Framework\Classes\Models\Setting',
		'option'      => '\WP_Framework\Classes\Models\Option',
		'device'      => '\WP_Framework\Classes\Models\Device',
		'minify'      => '\WP_Framework\Classes\Models\Minify',
		'filter'      => '\WP_Framework\Classes\Models\Filter',
		'user'        => '\WP_Framework\Classes\Models\User',
		'post'        => '\WP_Framework\Classes\Models\Post',
		'loader'      => '\WP_Framework\Classes\Models\Loader',
		'log'         => '\WP_Framework\Classes\Models\Log',
		'input'       => '\WP_Framework\Classes\Models\Input',
		'db'          => '\WP_Framework\Classes\Models\Db',
		'uninstall'   => '\WP_Framework\Classes\Models\Uninstall',
		'session'     => '\WP_Framework\Classes\Models\Session',
		'utility'     => '\WP_Framework\Classes\Models\Utility',
		'test'        => '\WP_Framework\Classes\Models\Test',
		'upgrade'     => '\WP_Framework\Classes\Models\Upgrade',
		'social'      => '\WP_Framework\Classes\Models\Social',
		'custom_post' => '\WP_Framework\Classes\Models\Custom_Post',
		'mail'        => '\WP_Framework\Classes\Models\Mail',
	];

	/**
	 * @var array $_property_instances
	 */
	private $_property_instances = [];

	/**
	 * @param string $name
	 *
	 * @return \WP_Framework\Interfaces\Singleton
	 * @throws \OutOfRangeException
	 */
	public function __get( $name ) {
		if ( isset( $this->_properties[ $name ] ) ) {
			if ( ! isset( $this->_property_instances[ $name ] ) ) {
				/** @var \WP_Framework\Interfaces\Singleton $class */
				$class                              = $this->_properties[ $name ];
				$this->_property_instances[ $name ] = $class::get_instance( $this->app );
			}

			return $this->_property_instances[ $name ];
		}
		throw new \OutOfRangeException( $name . ' is undefined.' );
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
	}

	/**
	 * @param string $class
	 *
	 * @return bool
	 */
	public function load_class( $class ) {
		$class = ltrim( $class, '\\' );
		$dir   = null;

		if ( ! isset( $this->_property_instances['define'] ) ) {
			$namespace = WP_CONTENT_FRAMEWORK;
			if ( preg_match( "#\A{$namespace}#", $class ) ) {
				$class = preg_replace( "#\A{$namespace}#", '', $class );
				$dir   = $this->app->get_library_directory() . DS . 'src';
			}
		} elseif ( preg_match( "#\A{$this->define->plugin_namespace}#", $class ) ) {
			$class = preg_replace( "#\A{$this->define->plugin_namespace}#", '', $class );
			$dir   = $this->define->plugin_src_dir;
		} elseif ( preg_match( "#\A{$this->define->lib_namespace}#", $class ) ) {
			$class = preg_replace( "#\A{$this->define->lib_namespace}#", '', $class );
			$dir   = $this->define->lib_src_dir;
		}

		if ( isset( $dir ) ) {
			$class = ltrim( $class, '\\' );
			$class = strtolower( $class );
			$path  = $dir . DS . str_replace( '\\', DS, $class ) . '.php';
			if ( is_readable( $path ) ) {
				/** @noinspection PhpIncludeInspection */
				require_once $path;

				return true;
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
		$this->setup_textdomain();
		$this->setup_settings();
		$this->filter->do_action( 'app_initialized', $this );
	}

	/**
	 * setup property
	 */
	private function setup_property() {
		if ( $this->app->is_uninstall() ) {
			foreach ( $this->_properties as $name => $class ) {
				$this->$name;
			}
			$this->uninstall->get_class_list();
		} else {
			if ( $this->get_config( 'config', 'use_custom_post' ) ) {
				$this->custom_post;
			}
			if ( $this->get_config( 'config', 'use_social_login' ) ) {
				$this->social;
			}
		}
	}

	/**
	 * setup textdomain
	 */
	private function setup_textdomain() {
		if ( ! self::$_lib_language_loaded ) {
			self::$_lib_language_loaded = true;
			load_plugin_textdomain( $this->define->lib_textdomain, false, $this->define->lib_languages_rel_path );
		}

		$text_domain = $this->get_text_domain();
		if ( ! empty( $text_domain ) ) {
			load_plugin_textdomain( $text_domain, false, $this->define->plugin_languages_rel_path );
		}
	}

	/**
	 * setup settings
	 */
	private function setup_settings() {
		if ( defined( 'WP_FRAMEWORK_MOCK_REST_REQUEST' ) && WP_FRAMEWORK_MOCK_REST_REQUEST ) {
			$this->setting->remove_setting( 'use_admin_ajax' );
		}
		if ( $this->loader->api->is_empty() ) {
			$this->setting->remove_setting( 'use_admin_ajax' );
			$this->setting->remove_setting( 'get_nonce_check_referer' );
			$this->setting->remove_setting( 'check_referer_host' );
		}
		$key = $this->app->is_theme ? 'ThemeURI' : 'PluginURI';
		if ( ! empty( $this->_plugin_data[ $key ] ) && $this->utility->starts_with( $this->_plugin_data[ $key ], 'https://wordpress.org' ) ) {
			$this->setting->edit_setting( 'check_update', 'default', false );
		}
		if ( ! $this->log->is_valid() ) {
			$this->setting->remove_setting( 'save___log_term' );
			$this->setting->remove_setting( 'delete___log_interval' );
			$this->setting->remove_setting( 'capture_shutdown_error' );
		}
		if ( $this->get_config( 'config', 'prevent_use_log' ) ) {
			$this->setting->remove_setting( 'is_valid_log' );
		}
	}

	/**
	 * @return bool
	 */
	public function has_initialized() {
		return $this->_initialized;
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
	 * @return string|false
	 */
	public function get_text_domain() {
		return $this->define->plugin_textdomain;
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	public function translate( $value ) {
		$text_domain = $this->get_text_domain();
		if ( ! empty( $text_domain ) ) {
			$translated = __( $value, $text_domain );
			if ( $value !== $translated ) {
				return $translated;
			}
		}

		return __( $value, $this->define->lib_textdomain );
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
		if ( ! isset( $this->loader->admin ) ) {
			add_action( 'admin_notices', function () use ( $message, $group, $error, $escape ) {
				$this->loader->admin->add_message( $message, $group, $error, $escape );
			}, 9 );
		} else {
			$this->loader->admin->add_message( $message, $group, $error, $escape );
		}
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

<?php
/**
 * WP_Framework
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

use WP_Framework\Package_Base;
use WP_Framework_Admin\Classes\Models\Admin;
use WP_Framework_Api\Classes\Models\Api;
use WP_Framework_Cache\Classes\Models\Cache;
use WP_Framework_Common\Classes\Models\Array_Utility;
use WP_Framework_Common\Classes\Models\Config;
use WP_Framework_Common\Classes\Models\Define;
use WP_Framework_Common\Classes\Models\Deprecated;
use WP_Framework_Common\Classes\Models\File_Utility;
use WP_Framework_Common\Classes\Models\Filter;
use WP_Framework_Common\Classes\Models\Input;
use WP_Framework_Common\Classes\Models\Option;
use WP_Framework_Common\Classes\Models\Setting;
use WP_Framework_Common\Classes\Models\String_Utility;
use WP_Framework_Common\Classes\Models\System;
use WP_Framework_Common\Classes\Models\Uninstall;
use WP_Framework_Common\Classes\Models\User;
use WP_Framework_Common\Classes\Models\Utility;
use WP_Framework_Core\Classes\Main;
use WP_Framework_Core\Interfaces\Package;
use WP_Framework_Cron\Classes\Models\Cron;
use WP_Framework_Custom_Post\Classes\Models\Custom_Post;
use WP_Framework_Db\Classes\Models\Db;
use WP_Framework_Device\Classes\Models\Device;
use WP_Framework_Editor\Classes\Models\Editor;
use WP_Framework_Log\Classes\Models\Log;
use WP_Framework_Mail\Classes\Models\Mail;
use WP_Framework_Post\Classes\Models\Post;
use WP_Framework_Presenter\Classes\Models\Drawer;
use WP_Framework_Presenter\Classes\Models\Minify;
use WP_Framework_Session\Classes\Models\Session;
use WP_Framework_Social\Classes\Models\Social;
use WP_Framework_Update\Classes\Models\Update;
use WP_Framework_Update_Check\Classes\Models\Update_Check;
use WP_Framework_Upgrade\Classes\Models\Upgrade;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
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
 * @property string $package_file
 *
 * @property Define $define
 * @property Config $config
 * @property Setting $setting
 * @property Filter $filter
 * @property Uninstall $uninstall
 * @property Utility $utility
 * @property Array_Utility $array
 * @property String_Utility $string
 * @property File_Utility $file
 * @property Option $option
 * @property User $user
 * @property Input $input
 * @property Deprecated $deprecated
 * @property System $system
 * @property Db $db
 * @property Log $log
 * @property Admin $admin
 * @property Api $api
 * @property Drawer $drawer
 * @property Minify $minify
 * @property Mail $mail
 * @property \WP_Framework_Test\Classes\Models\Test $test
 * @property Cron $cron
 * @property Custom_Post $custom_post
 * @property Device $device
 * @property Editor $editor
 * @property Session $session
 * @property Social $social
 * @property Post $post
 * @property Update $update
 * @property Update_Check $update_check
 * @property Upgrade $upgrade
 * @property Cache $cache
 *
 * @method void main_init()
 * @method bool has_initialized()
 * @method array get_mapped_class( string $class )
 * @method mixed get_config( string $name, string | null $key = null, mixed $default = null )
 * @method mixed get_option( string $key, mixed $default = '' )
 * @method mixed get_session( string $key, mixed $default = '' )
 * @method mixed set_session( string $key, mixed $value, int | null $duration = null )
 * @method bool user_can( null | string | false $capability = null )
 * @method void log( mixed $message, mixed $context = null, string $level = '' )
 * @method void add_message( string $message, string $group = '', bool $error = false, bool $escape = true, null | array $override_allowed_html = null )
 * @method string get_page_slug( string $file )
 * @method mixed get_shared_object( string $key, string | null $target = null )
 * @method void set_shared_object( string $key, mixed $object, string | null $target = null )
 * @method bool isset_shared_object( string $key, string | null $target = null )
 * @method void delete_shared_object( string $key, string | null $target = null )
 * @method bool send_mail( string $to, string $subject, string | array $body, string | false $text = false )
 * @method string get_view( Package $instance, string $name, array $args = [], bool $echo = false, bool $error = true, bool $remove_nl = false )
 * @method void add_script_view( Package $instance, string $name, array $args = [], int $priority = 10 )
 * @method void add_style_view( Package $instance, string $name, array $args = [], int $priority = 10 )
 * @method void enqueue_style( Package $instance, string $handle, string $file, array $depends = [], string | bool | null $ver = false, string $media = 'all', string $dir = 'css' )
 * @method void enqueue_script( Package $instance, string $handle, string $file, array $depends = [], string | bool | null $ver = false, bool $in_footer = true, string $dir = 'js' )
 * @method bool localize_script( Package $instance, string $handle, string $name, array $data )
 * @method bool lock_process( string $name, callable $func, int $timeout = 60 )
 * @method bool is_enough_version()
 * @method void load_all_packages()
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
// @codingStandardsIgnoreStart
class WP_Framework {
	// @codingStandardsIgnoreEnd

	/**
	 * @var WP_Framework[] $instances
	 */
	private static $instances = [];

	/**
	 * @var array $framework_package_versions (package => version)
	 */
	private static $framework_package_versions = [];

	/**
	 * @var array $framework_package_plugin_names (package => plugin_name)
	 */
	private static $framework_package_plugin_names = [];

	/**
	 * @var bool $packages_loaded
	 */
	private static $packages_loaded = false;

	/**
	 * @var Package_Base[]
	 */
	private static $packages = [];

	/**
	 * @var array $framework_cache
	 */
	private static $framework_cache;

	/**
	 * for debug
	 * @var float $_started
	 */
	private static $started_at;

	/**
	 * for debug
	 * @var float $elapsed
	 */
	private static $elapsed = 0.0;

	/**
	 * @var array $package_versions (package => version)
	 */
	private $package_versions;

	/**
	 * @var array $package_directories (package => directory)
	 */
	private $package_directories;

	/**
	 * @var Package_Base[]
	 */
	private $available_packages;

	/**
	 * @var string $framework_root_directory
	 */
	private $framework_root_directory;

	/**
	 * @var bool $plugins_loaded
	 */
	private $plugins_loaded = false;

	/**
	 * @var bool $framework_initialized
	 */
	private $framework_initialized = false;

	/**
	 * @var Main $main
	 */
	private $main;

	/**
	 * @var bool $is_uninstall
	 */
	private $is_uninstall = false;

	/**
	 * @var array $plugin_data
	 */
	private $plugin_data;

	/**
	 * @var array $readonly_properties
	 */
	private $readonly_properties = [
		'is_theme'             => false,
		'original_plugin_name' => '',
		'plugin_name'          => '',
		'slug_name'            => '',
		'plugin_file'          => '',
		'plugin_dir'           => '',
		'relative_path'        => '',
		'package_file'         => '',
	];

	/** @var bool $is_allowed_access */
	private $is_allowed_access = false;

	/**
	 * WP_Framework constructor.
	 *
	 * @param string $plugin_name
	 * @param string $plugin_file
	 * @param string|null $slug_name
	 * @param string|null $relative
	 * @param string|null $package
	 */
	private function __construct( $plugin_name, $plugin_file, $slug_name, $relative, $package ) {
		$this->is_allowed_access    = true;
		$theme_dir                  = str_replace( '/', DS, WP_CONTENT_DIR . DS . 'theme' );
		$relative                   = ! empty( $relative ) ? trim( $relative ) : null;
		$this->is_theme             = preg_match( "#\A{$theme_dir}#", str_replace( '/', DS, $plugin_file ) ) > 0;
		$this->original_plugin_name = $plugin_name;
		$this->plugin_file          = $plugin_file;
		$this->plugin_dir           = dirname( $plugin_file );
		$this->relative_path        = empty( $relative ) ? '' : ( trim( str_replace( '/', DS, $relative ), DS ) . DS );
		$this->package_file         = is_string( $package ) && ! empty( $package ) ? $package : null;
		$this->plugin_name          = strtolower( $this->original_plugin_name );
		$this->slug_name            = ! empty( $slug_name ) ? strtolower( $slug_name ) : $this->plugin_name;
		$this->is_allowed_access    = false;

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$this->plugin_data = $this->is_theme ? wp_get_theme() : get_plugin_data( $this->plugin_file, false, false );

		$this->setup_framework_version();
		$this->setup_actions();
		$this->load_pluggable();
	}

	/**
	 * @param string $name
	 *
	 * @return mixed
	 * @throws OutOfRangeException
	 */
	public function __get( $name ) {
		if ( array_key_exists( $name, $this->readonly_properties ) ) {
			return $this->readonly_properties[ $name ];
		}

		return $this->get_main()->__get( $name );
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 *
	 * @throws OutOfRangeException
	 * @SuppressWarnings(PHPMD.MissingImport)
	 */
	public function __set( $name, $value ) {
		if ( $this->is_allowed_access && array_key_exists( $name, $this->readonly_properties ) ) {
			$this->readonly_properties[ $name ] = $value;
		} else {
			throw new OutOfRangeException( sprintf( 'you cannot access %s->%s.', static::class, $name ) );
		}
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		if ( array_key_exists( $name, $this->readonly_properties ) ) {
			return ! is_null( $this->readonly_properties[ $name ] );
		}

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
	 *
	 * @throws Exception
	 */
	public static function __callStatic( $name, $arguments ) {
		$matches = null;
		if ( preg_match( '#register_uninstall_(.+)\z#', $name, $matches ) ) {
			self::uninstall( $matches[1] );
		}
	}

	/**
	 * @param string $plugin_name
	 * @param string|null $plugin_file
	 * @param string|null $slug_name
	 * @param string|null $relative
	 * @param string|null $package
	 *
	 * @return WP_Framework
	 */
	public static function get_instance( $plugin_name, $plugin_file = null, $slug_name = null, $relative = null, $package = null ) {
		if ( ! isset( self::$instances[ $plugin_name ] ) ) {
			if ( empty( $plugin_file ) ) {
				self::kill( '$plugin_file is required.', __FILE__, __LINE__ );
			}
			self::report_performance();
			self::run( function () use ( $plugin_name, $plugin_file, $slug_name, $relative, $package ) {
				$instances                       = new static( $plugin_name, $plugin_file, $slug_name, $relative, $package );
				self::$instances[ $plugin_name ] = $instances;
				self::update_framework_packages( $instances );
			} );
		}

		return self::$instances[ $plugin_name ];
	}

	/**
	 * for debug
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	private static function report_performance() {
		if ( ! isset( self::$started_at ) ) {
			self::$started_at = false;
			if ( defined( 'WP_FRAMEWORK_PERFORMANCE_REPORT' ) && ! defined( 'PHPUNIT_COMPOSER_INSTALL' ) ) {
				self::$started_at = microtime( true ) * 1000;

				add_action( 'shutdown', function () {
					if ( ! did_action( 'wp_loaded' ) ) {
						return;
					}
					if ( defined( 'WP_UNINSTALL_PLUGIN' ) && WP_UNINSTALL_PLUGIN ) {
						return;
					}
					if ( defined( 'WP_FRAMEWORK_PERFORMANCE_REPORT_EXCLUDE_AJAX' ) ) {
						if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
							return;
						}
						if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
							return;
						}
					}
					if ( defined( 'WP_FRAMEWORK_PERFORMANCE_REPORT_EXCLUDE_CRON' ) && defined( 'DOING_CRON' ) && DOING_CRON ) {
						return;
					}
					if ( defined( 'WP_FRAMEWORK_SUSPEND_PERFORMANCE_REPORT' ) ) {
						return;
					}

					error_log( '' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					error_log( '' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					$total = 0;
					foreach ( self::$instances as $instance ) {
						if ( ! $instance->framework_initialized() ) {
							continue;
						}
						$total += $instance->filter->get_elapsed();
					}
					$total  = $total + self::$elapsed;
					$global = microtime( true ) * 1000 - self::$started_at;
					error_log( sprintf( 'shutdown framework: %12.8fms (%12.8fms) / %12.8fms (%.2f%%)', $total, self::$elapsed, $global, ( $total / $global ) * 100 ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

					foreach ( self::$instances as $instance ) {
						if ( ! $instance->framework_initialized() ) {
							continue;
						}
						$elapsed = $instance->filter->get_elapsed();
						error_log( sprintf( '  %12.8fms (%5.2f%% / %5.2f%%) : %s', $elapsed, ( $elapsed / $global ) * 100, ( $elapsed / $total ) * 100, $instance->plugin_name ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
						if ( defined( 'WP_FRAMEWORK_DETAIL_REPORT' ) ) {
							foreach ( $instance->filter->get_elapsed_details() as $detail ) {
								error_log( '     - ' . $detail ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
							}
						}
						if ( $instance->is_valid_package( 'db' ) ) {
							$instance->db->performance_report();
						}
					}
				}, 1 );
			}
		}
	}

	/**
	 * @return Package_Base[]
	 */
	public function get_packages() {
		if ( ! isset( $this->available_packages ) ) {
			$packages                  = $this->get_package_names();
			$this->package_directories = [];
			foreach ( self::$packages as $package => $instance ) {
				if ( in_array( $package, $packages, true ) ) {
					$this->available_packages[ $package ] = $instance;
				}
			}
		}

		return $this->available_packages;
	}

	/**
	 * @return string[]
	 */
	public function get_package_names() {
		if ( ! $this->framework_initialized ) {
			self::kill( [
				'framework is not ready.',
				'<pre>' . wp_debug_backtrace_summary() . '</pre>', // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_wp_debug_backtrace_summary
			], __FILE__, __LINE__ );
		}

		return array_keys( $this->package_versions );
	}

	/**
	 * @return array
	 */
	public function get_package_versions() {
		if ( ! $this->framework_initialized ) {
			self::kill( [
				'framework is not ready.',
				'<pre>' . wp_debug_backtrace_summary() . '</pre>', // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_wp_debug_backtrace_summary
			], __FILE__, __LINE__ );
		}

		return $this->package_versions;
	}

	/**
	 * @return string[]
	 */
	public function get_package_directories() {
		if ( ! isset( $this->package_directories ) ) {
			$this->package_directories = [];
			foreach ( $this->get_packages() as $package => $instance ) {
				$this->package_directories[ $package ] = $instance->get_dir();
			}
		}

		return $this->package_directories;
	}

	/**
	 * @param string $package
	 *
	 * @return bool
	 */
	public function is_valid_package( $package ) {
		return isset( $this->available_packages[ $package ] );
	}

	/**
	 * @param string $package
	 *
	 * @return Package_Base
	 */
	public function get_package_instance( $package = 'core' ) {
		if ( ! isset( $this->available_packages[ $package ] ) ) {
			self::kill( [ 'package is not available.', 'package name: ' . $package ], __FILE__, __LINE__ );
		}

		return $this->available_packages[ $package ];
	}

	/**
	 * @param string $package
	 *
	 * @return string
	 */
	public function get_package_directory( $package = 'core' ) {
		$dirs = $this->get_package_directories();
		if ( ! isset( $dirs[ $package ] ) ) {
			self::kill( [ 'package is not available.', 'package name: ' . $package ], __FILE__, __LINE__ );
		}

		return $dirs[ $package ];
	}

	/**
	 * @param string $package
	 *
	 * @return string
	 */
	public function get_package_version( $package = 'core' ) {
		if ( ! $this->framework_initialized ) {
			self::kill( [
				'framework is not ready.',
				'<pre>' . wp_debug_backtrace_summary() . '</pre>', // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_wp_debug_backtrace_summary
			], __FILE__, __LINE__ );
		}
		if ( ! array_key_exists( $package, self::$framework_package_versions ) ) {
			self::kill( [ 'package is not available.', 'package name: ' . $package ], __FILE__, __LINE__ );
		}

		return self::$framework_package_versions[ $package ];
	}

	/**
	 * @return string
	 */
	public function get_framework_version() {
		return $this->get_package_version();
	}

	/**
	 * @param string|null $key
	 *
	 * @return array|string
	 */
	public function get_plugin_data( $key = null ) {
		return empty( $key ) ? $this->plugin_data : $this->plugin_data[ $key ];
	}

	/**
	 * @return string
	 */
	public function get_plugin_version() {
		return $this->get_plugin_data( 'Version' );
	}

	/**
	 * @return string
	 */
	public function get_plugin_uri() {
		return $this->get_plugin_data( $this->is_theme ? 'ThemeURI' : 'PluginURI' );
	}

	/**
	 * @return bool
	 */
	public function is_uninstall() {
		return $this->is_uninstall;
	}

	/**
	 * @param string|array $message
	 * @param string $file
	 * @param int $line
	 * @param string $title
	 * @param bool $output_file_info
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
	 */
	// @codingStandardsIgnoreStart
	public static function wp_die( $message, $file, $line, $title = '', $output_file_info = true ) {
		// @codingStandardsIgnoreEnd
		if ( ! is_array( $message ) ) {
			$message = [ '[wp content framework]', $message ];
		}
		if ( $output_file_info ) {
			$message[] = 'File: ' . $file;
			$message[] = 'Line: ' . $line;
		}

		if ( is_admin() || ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) ) {
			$message = '<ul><li>' . implode( '</li><li>', $message ) . '</li></ul>';
			wp_die( wp_kses_post( $message ), esc_html( $title ) );
		} else {
			if ( $title ) {
				error_log( $title ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
			error_log( print_r( $message, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
		exit;
	}

	/**
	 * @param string|array $message
	 * @param string $file
	 * @param int $line
	 * @param string $title
	 * @param bool $output_file_info
	 */
	public static function kill( $message, $file, $line, $title = '', $output_file_info = true ) {
		self::wp_die( $message, $file, $line, $title, $output_file_info ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @return WP_Framework[]
	 */
	public function get_instances() {
		if ( ! $this->framework_initialized ) {
			self::kill( [
				'framework is not ready.',
				'<pre>' . wp_debug_backtrace_summary() . '</pre>', // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_wp_debug_backtrace_summary
			], __FILE__, __LINE__ );
		}

		return array_filter( self::$instances, function ( $instance ) {
			/** @var WP_Framework $instance */
			return $instance->framework_initialized;
		} );
	}

	/**
	 * @return Main|\WP_Framework_Core\Interfaces\Singleton
	 */
	private function get_main() {
		if ( ! $this->framework_initialized ) {
			self::kill( [
				'framework is not ready.',
				'<pre>' . wp_debug_backtrace_summary() . '</pre>', // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_wp_debug_backtrace_summary
			], __FILE__, __LINE__ );
		}
		if ( ! isset( $this->main ) ) {
			if ( ! class_exists( '\WP_Framework_Core\Classes\Main' ) ) {
				$path = $this->get_package_directory() . DS . 'src' . DS . 'classes' . DS . 'main.php';
				/** @noinspection PhpIncludeInspection */
				require_once $path;
			}
			$this->main = Main::get_instance( $this );
		}

		return $this->main;
	}

	/**
	 * @return array
	 */
	private function get_plugin_cache() {
		if ( ! defined( 'WP_FRAMEWORK_FORCE_CACHE' ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return [ false, null, null ];
		}
		if ( ! isset( self::$framework_cache ) ) {
			self::$framework_cache = get_site_option( WP_FRAMEWORK_VENDOR_NAME );
		}
		if ( ! is_array( self::$framework_cache ) || ! isset( self::$framework_cache[ $this->plugin_name ] ) ) {
			return [ false, null, null ];
		}
		$cache = self::$framework_cache[ $this->plugin_name ];
		if ( ! is_array( $cache ) || count( $cache ) !== 2 || $cache[0] !== $this->get_plugin_version() || ! is_array( $cache[1] ) || count( $cache[1] ) !== 2 ) {
			return [ false, null, null ];
		}

		return [ true, $cache[1][0], $cache[1][1] ];
	}

	/**
	 * @return bool
	 */
	private function set_plugin_cache() {
		if ( ! is_array( self::$framework_cache ) ) {
			self::$framework_cache = [];
		}
		self::$framework_cache[ $this->plugin_name ] = [
			$this->get_plugin_version(),
			[
				$this->framework_root_directory,
				$this->package_versions,
			],
		];

		return update_site_option( WP_FRAMEWORK_VENDOR_NAME, self::$framework_cache );
	}

	/**
	 * @return bool
	 */
	private function delete_plugin_cache() {
		if ( is_multisite() ) {
			// 途中でマルチサイトにした場合のために削除
			global $wpdb;
			$current_blog_id = get_current_blog_id();
			$blog_ids        = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				delete_option( WP_FRAMEWORK_VENDOR_NAME );
			}
			switch_to_blog( $current_blog_id );
		}

		return delete_site_option( WP_FRAMEWORK_VENDOR_NAME );
	}

	/**
	 * setup framework version
	 */
	private function setup_framework_version() {
		list( $is_valid, $root_directory, $versions ) = $this->get_plugin_cache();
		if ( $is_valid ) {
			$this->framework_root_directory = $root_directory;
			$this->package_versions         = $versions;
		} else {
			$vendor_root = $this->plugin_dir . DS . $this->relative_path . 'vendor';
			$installed   = $vendor_root . DS . 'composer' . DS . 'installed.json';
			if ( ! @file_exists( $installed ) || ! @is_readable( $installed ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				self::kill( 'installed.json not found.', __FILE__, __LINE__ );
			}
			$json = @json_decode( @file_get_contents( $installed ), true ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( empty( $json ) ) {
				self::kill( 'installed.json is invalid.', __FILE__, __LINE__ );
			}

			$additional = $this->load_additional();
			$versions   = [];
			foreach ( $json as $package ) {
				$name     = $package['name'];
				$exploded = explode( '/', $name );

				if ( count( $exploded ) !== 2 ) {
					continue;
				}

				if ( WP_FRAMEWORK_VENDOR_NAME === $exploded[0] ) {
					$package_name = strtolower( $exploded[1] );
				} elseif ( is_array( $additional ) && in_array( $name, $additional, true ) ) {
					$package_name = strtolower( $name );
				} else {
					continue;
				}

				$version                   = $package['version_normalized'];
				$versions[ $package_name ] = $version;
			}
			if ( ! isset( $versions['core'] ) ) {
				self::kill( 'installed.json is invalid.', __FILE__, __LINE__ );
			}
			$this->framework_root_directory = $vendor_root . DS . WP_FRAMEWORK_VENDOR_NAME;
			$this->package_versions         = $versions;
			$this->set_plugin_cache();
		}
	}

	/**
	 * @return array|false
	 */
	private function load_additional() {
		$additional = false;
		if ( ! empty( $this->package_file ) ) {
			$additional_package = $this->plugin_dir . DS . $this->package_file;
			if ( @file_exists( $additional_package ) && @is_readable( $additional_package ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				$additional = @json_decode( @file_get_contents( $additional_package ), true ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				if ( ! is_array( $additional ) || empty( $additional ) ) {
					$additional = false;
				}
			}
		}

		return $additional;
	}

	/**
	 * @param WP_Framework $app
	 * @SuppressWarnings(PHPMD.UndefinedVariable)
	 */
	private static function update_framework_packages( WP_Framework $app ) {
		foreach ( $app->package_versions as $package => $version ) {
			if ( ! isset( self::$framework_package_versions[ $package ] ) || version_compare( self::$framework_package_versions[ $package ], $version, '<' ) ) {
				self::$framework_package_versions[ $package ]     = $version;
				self::$framework_package_plugin_names[ $package ] = $app->original_plugin_name;
			}
		}
	}

	/**
	 * initialize framework
	 * @SuppressWarnings(PHPMD.UndefinedVariable)
	 */
	private static function load_packages() {
		if ( ! class_exists( '\WP_Framework\Package_Base' ) ) {
			/** @noinspection PhpIncludeInspection */
			require_once self::$instances[ self::$framework_package_plugin_names['core'] ]->framework_root_directory . DS . 'core' . DS . 'package_base.php';
		}
		$priority = [];
		$packages = [];
		foreach ( self::$framework_package_plugin_names as $key => $plugin_name ) {
			$app = self::$instances[ $plugin_name ];
			if ( strpos( $key, '/' ) !== false ) {
				$directory = dirname( $app->framework_root_directory ) . DS . $key;
				$exploded  = explode( '/', $key );
				$namespace = ucwords( str_replace( '-', '_', $exploded[0] ), '_' );
				$package   = $exploded[1];
			} else {
				$package   = $key;
				$directory = $app->framework_root_directory . DS . $package;
				$namespace = 'WP_Framework';
			}

			$class = "\\{$namespace}\Package_" . ucwords( $package, '_' );
			if ( ! class_exists( $class ) ) {
				$path = $directory . DS . 'package_' . $package . '.php';
				if ( ! @is_readable( $path ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
					self::kill( [ 'invalid package', 'package name: ' . $key ], __FILE__, __LINE__ );
				}
				/** @noinspection PhpIncludeInspection */
				require_once $path;

				if ( ! class_exists( $class ) ) {
					self::kill( [ 'invalid package', 'package name: ' . $key, 'class name: ' . $class ], __FILE__, __LINE__ );
				}
			}

			$version = self::$framework_package_versions[ $key ];
			/** @var Package_Base $class */
			$packages[ $key ] = $class::get_instance( $app, $key, $directory, $version );
			$priority[ $key ] = $packages[ $key ]->get_priority();
		}
		array_multisort( $priority, $packages );
		self::$packages = [];
		foreach ( $packages as $package ) {
			/** @var Package_Base $package */
			self::$packages[ $package->get_package() ] = $package;
		}
	}

	/**
	 * for debug
	 *
	 * @param callable $callback
	 */
	private static function run( $callback ) {
		$start = microtime( true ) * 1000;
		$callback();
		$elapsed         = microtime( true ) * 1000 - $start;
		static::$elapsed = static::$elapsed + $elapsed;
	}

	/**
	 * setup actions
	 */
	private function setup_actions() {
		add_action( 'after_setup_theme', function () {
			self::run( function () {
				$this->initialize_framework();
			} );
		} );

		if ( $this->is_theme ) {
			add_action( 'switch_theme', function () {
				$this->filter->do_action( 'app_deactivated', $this );
				$this->delete_plugin_cache();
			} );
		} else {
			add_action( 'plugins_loaded', function () {
				$this->plugins_loaded();
			} );
			add_action( 'deactivated_plugin', function ( $plugin ) {
				if ( $this->define->plugin_base_name === $plugin ) {
					$this->filter->do_action( 'app_deactivated', $this );
					$this->delete_plugin_cache();
				}
			} );
		}

		add_action( 'init', function () {
			$this->main_init();
		}, 1 );
	}

	/**
	 * plugin loaded
	 */
	private function plugins_loaded() {
		if ( $this->plugins_loaded || $this->is_theme ) {
			return;
		}
		$this->plugins_loaded = true;
		$this->load_functions();
	}

	/**
	 * @param bool $load_packages
	 *
	 * @throws Exception
	 */
	private function initialize_framework( $load_packages = false ) {
		$this->plugins_loaded();
		if ( $this->framework_initialized ) {
			return;
		}
		$this->framework_initialized = true;

		if ( ! self::$packages_loaded || $load_packages ) {
			self::$packages_loaded = true;
			self::load_packages();
		}

		spl_autoload_register( function ( $class ) {
			return $this->get_main()->load_class( $class );
		} );

		$this->load_setup();
	}

	/**
	 * @return bool
	 */
	public function framework_initialized() {
		return $this->framework_initialized;
	}

	/**
	 * @param string $name
	 */
	private function load_plugin_file( $name ) {
		$path = $this->plugin_dir . DS . $name . '.php';
		if ( is_readable( $path ) ) {
			/** @noinspection PhpIncludeInspection */
			require_once $path;
		}
	}

	/**
	 * load pluggable file
	 */
	private function load_pluggable() {
		if ( $this->is_theme ) {
			return;
		}
		$this->load_plugin_file( 'pluggable' );
	}

	/**
	 * load functions file
	 */
	private function load_functions() {
		if ( $this->is_theme ) {
			return;
		}
		$this->load_plugin_file( 'functions' );
	}

	/**
	 * load setup file
	 */
	private function load_setup() {
		$this->load_plugin_file( 'setup' );
	}

	/**
	 * @param string $plugin_base_name
	 *
	 * @throws Exception
	 */
	private static function uninstall( $plugin_base_name ) {
		$app = self::find_plugin( $plugin_base_name );
		if ( ! isset( $app ) ) {
			return;
		}

		$app->is_uninstall = true;
		$app->main_init();
		$app->uninstall->uninstall();
	}

	/**
	 * @param string $plugin_base_name
	 *
	 * @return WP_Framework|null
	 * @throws Exception
	 */
	private static function find_plugin( $plugin_base_name ) {
		/** @var WP_Framework $instance */
		foreach ( self::$instances as $instance ) {
			if ( $instance->is_theme ) {
				continue;
			}
			$instance->initialize_framework( true );
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

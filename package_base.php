<?php
/**
 * WP_Framework Package Base
 *
 * @version 0.0.1
 * @author technote-space
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Package_Base
 * @package WP_Framework
 */
abstract class Package_Base {

	/** @var Package_Base[] $_instances */
	private static $_instances = [];

	/**
	 * @var \WP_Framework $_app
	 */
	private $_app;

	/**
	 * @var array $_configs
	 */
	private $_configs = [];

	/**
	 * @var string $_package
	 */
	private $_package;

	/**
	 * @var string $_dir
	 */
	private $_dir;

	/**
	 * @var string $_version
	 */
	private $_version;

	/**
	 * @var string $_url
	 */
	private $_url;

	/**
	 * @var string $_namespace
	 */
	private $_namespace;

	/**
	 * @param \WP_Framework $app
	 * @param string $package
	 * @param string $dir
	 * @param string $version
	 *
	 * @return Package_Base
	 */
	public static function get_instance( \WP_Framework $app, $package, $dir, $version ) {
		! isset( self::$_instances[ $package ] ) and self::$_instances[ $package ] = new static( $app, $package, $dir, $version );

		return self::$_instances[ $package ];
	}

	/**
	 * Main constructor.
	 *
	 * @param \WP_Framework $app
	 * @param string $package
	 * @param string $dir
	 * @param string $version
	 */
	private function __construct( $app, $package, $dir, $version ) {
		$this->_app     = $app;
		$this->_package = $package;
		$this->_dir     = $dir;
		$this->_version = $version;
		$this->initialize();
	}

	/**
	 * @return string
	 */
	public function get_package() {
		return $this->_package;
	}

	/**
	 * @return string
	 */
	public function get_namespace() {
		! isset( $this->_namespace ) and $this->_namespace = "WP_Framework_" . ucwords( $this->_package, '_' );

		return $this->_namespace;
	}

	/**
	 * initialize
	 */
	protected function initialize() {

	}

	/**
	 * @return int
	 */
	public abstract function get_priority();

	/**
	 * @return array
	 */
	public function get_configs() {
		return [];
	}

	/**
	 * @param $name
	 * @param \WP_Framework $app
	 *
	 * @return array
	 */
	public function get_config( $name, $app ) {
		if ( ! isset( $this->_configs[ $name ] ) ) {
			if ( ! in_array( $name, $this->get_configs() ) ) {
				$this->_configs[ $name ] = [];
			} else {
				$this->_configs[ $name ] = $this->load_package_config( $name );
			}
		}

		return array_replace_recursive( $this->_configs[ $name ], $this->load_plugin_config( $name, $app ) );
	}

	/**
	 * @param string $class
	 *
	 * @return bool
	 */
	public function load_class( $class ) {
		$class = $this->trim_namespace( $class );
		if ( $class ) {
			$class = strtolower( $class );
			$path  = $this->get_dir() . DS . 'src' . DS . str_replace( '\\', DS, $class ) . '.php';
			if ( is_readable( $path ) ) {
				/** @noinspection PhpIncludeInspection */
				require_once $path;

				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $namespace
	 *
	 * @return array
	 */
	public function namespace_to_dir( $namespace ) {
		$relative = $this->trim_namespace( $namespace );
		if ( $relative ) {
			return [ $this->get_dir() . DS . 'src', $relative ];
		}

		return [ null, null ];
	}

	/**
	 * @param string $string
	 *
	 * @return string|false
	 */
	private function trim_namespace( $string ) {
		$namespace = $this->get_namespace();
		$string    = ltrim( $string, '\\' );
		if ( preg_match( "#\A{$namespace}\\\\#", $string ) ) {
			return preg_replace( "#\A{$namespace}\\\\#", '', $string );
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function get_dir() {
		return $this->_dir;
	}

	/**
	 * @return string
	 */
	public function get_version() {
		return $this->_version;
	}

	/**
	 * @return string
	 */
	public function get_url() {
		if ( ! isset( $this->_url ) ) {
			$url        = $this->_app->is_theme ? get_template_directory_uri() : plugins_url( '', $this->_app->plugin_file );
			$relative   = str_replace( DS, '/', $this->_app->relative_path );
			$vendor     = WP_FRAMEWORK_VENDOR_NAME;
			$this->_url = "{$url}/{$relative}vendor/{$vendor}/{$this->_package}";
		}

		return $this->_url;
	}

	/**
	 * @return bool
	 */
	protected function is_valid_assets() {
		return false;
	}

	/**
	 * @return bool
	 */
	protected function is_valid_view() {
		return false;
	}

	/**
	 * @return bool
	 */
	protected function is_valid_translate() {
		return false;
	}

	/**
	 * @param string $name
	 *
	 * @return array
	 */
	private function load_package_config( $name ) {
		$package_config = $this->load_config_file( $this->get_dir() . DS . 'configs', $name );

		return apply_filters( 'wp_framework/load_config', $package_config, $name, $package_config );
	}

	/**
	 * @param string $name
	 * @param \WP_Framework $app
	 *
	 * @return array
	 */
	private function load_plugin_config( $name, $app ) {
		$plugin_config = $this->load_config_file( $app->plugin_dir . DS . 'configs' . DS . 'packages' . DS . $this->get_package(), $name );

		return apply_filters( 'wp_framework/load_config', $plugin_config, $name, $plugin_config, $app );
	}

	/**
	 * @param string $dir
	 * @param string $name
	 *
	 * @return array
	 */
	private function load_config_file( $dir, $name ) {
		$path = rtrim( $dir, DS ) . DS . $name . '.php';
		if ( ! file_exists( $path ) ) {
			return [];
		}
		/** @noinspection PhpIncludeInspection */
		$config = include $path;
		if ( ! is_array( $config ) ) {
			$config = [];
		}

		return $config;
	}

	/**
	 * @return string|false
	 */
	public function get_assets_dir() {
		if ( ! $this->is_valid_assets() ) {
			return false;
		}

		return $this->get_dir() . DS . 'assets';
	}

	/**
	 * @return string|false
	 */
	public function get_assets_url() {
		if ( ! $this->is_valid_assets() ) {
			return false;
		}

		return $this->get_url() . '/assets';
	}

	/**
	 * @param bool $allow_multiple
	 *
	 * @return array
	 */
	public function get_assets_settings( $allow_multiple = false ) {
		if ( 'presenter' === $this->_package ) {
			return [ $this->get_assets_dir() => $this->get_assets_url() ];
		}

		$presenter = $this->_app->get_package_instance( 'presenter' );
		if ( ! $this->is_valid_assets() ) {
			return $presenter->get_assets_settings();
		}

		if ( $allow_multiple ) {
			$settings                            = $presenter->get_assets_settings();
			$settings[ $this->get_assets_dir() ] = $this->get_assets_url();
		} else {
			$settings                            = [];
			$settings[ $this->get_assets_dir() ] = $this->get_assets_url();
			foreach ( $presenter->get_assets_settings() as $k => $v ) {
				$settings[ $k ] = $v;
			}
		}

		return $settings;
	}

	/**
	 * @return array
	 */
	public function get_views_dirs() {
		if ( 'presenter' === $this->_package ) {
			return [ $this->get_views_dir() ];
		}

		$presenter = $this->_app->get_package_instance( 'presenter' );
		if ( ! $this->is_valid_view() ) {
			return $presenter->get_views_dirs();
		}

		$dirs   = [];
		$dirs[] = $this->get_views_dir();
		foreach ( $presenter->get_views_dirs() as $dir ) {
			$dirs[] = $dir;
		}

		return $dirs;
	}

	/**
	 * @return array
	 */
	public function get_translate_settings() {
		if ( 'common' === $this->_package ) {
			return [ $this->get_textdomain() => $this->get_language_directory() ];
		}

		$common = $this->_app->get_package_instance( 'common' );
		if ( ! $this->is_valid_translate() ) {
			return $common->get_translate_settings();
		}

		$settings                            = [];
		$settings[ $this->get_textdomain() ] = $this->get_language_directory();
		foreach ( $common->get_translate_settings() as $k => $v ) {
			$settings[ $k ] = $v;
		}

		return $settings;
	}

	/**
	 * @return string
	 */
	protected function get_textdomain() {
		return 'wp_framework-' . $this->_package;
	}

	/**
	 * @return string
	 */
	protected function get_views_dir() {
		return $this->get_dir() . DS . 'src' . DS . 'views';
	}

	/**
	 * @return string
	 */
	protected function get_language_directory() {
		return $this->get_dir() . DS . 'languages';
	}
}

<?php
/**
 * WP_Framework_Core Traits Singleton
 *
 * @version 0.0.13
 * @author technote-space
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Core\Traits;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Trait Singleton
 * @package WP_Framework_Core\Traits
 * @property \WP_Framework $app
 */
trait Singleton {

	use Readonly, Translate, Package;

	/**
	 * @var Singleton[] $_instances
	 */
	private static $_instances = [];

	/**
	 * @var string[] $_slugs
	 */
	private static $_slugs = [];

	/**
	 * @var \WP_Framework $app
	 */
	protected $app;

	/**
	 * @var bool $_initialize_called
	 */
	private $_initialize_called = false;

	/**
	 * @var bool $_initialized_called
	 */
	private $_initialized_called = false;

	/**
	 * @var string $_class_name
	 */
	private $_class_name;

	/**
	 * @var \ReflectionClass $_reflection
	 */
	private $_reflection;

	/**
	 * @param \WP_Framework $app
	 *
	 * @return \WP_Framework_Core\Traits\Singleton
	 */
	public static function get_instance( \WP_Framework $app ) {
		$class = get_called_class();
		if ( false === $class ) {
			$class = get_class();
		}

		list( $mapped, $class ) = $app->get_mapped_class( $class );
		if ( $mapped ) {
			$key = $app->plugin_name;
		} else {
			$key = static::is_shared_class() ? '' : $app->plugin_name;
		}
		if ( empty( self::$_instances[ $key ] ) || ! array_key_exists( $class, self::$_instances[ $key ] ) ) {
			try {
				$reflection = new \ReflectionClass( $class );
			} catch ( \Exception $e ) {
				$app->wp_die( 'unexpected error has occurred.', __FILE__, __LINE__ );
				exit;
			}
			if ( $reflection->isAbstract() ) {
				self::$_instances[ $key ][ $class ] = null;
			} else {
				if ( $mapped ) {
					/** @var \WP_Framework_Core\Traits\Singleton $class */
					$instance                           = $class::get_instance( $app );
					self::$_instances[ $key ][ $class ] = $instance;
				} else {
					$instance = new static( $app, $reflection );
					if ( $app->is_uninstall() && $instance instanceof \WP_Framework_Common\Interfaces\Uninstall ) {
						$app->uninstall->add_uninstall( [ $instance, 'uninstall' ], $instance->get_uninstall_priority() );
					}
					self::$_instances[ $key ][ $class ] = $instance;
					$instance->call_initialize();
				}
			}
		}

		return self::$_instances[ $key ][ $class ];
	}

	/**
	 * @return bool
	 */
	protected static function is_shared_class() {
		return false;
	}

	/**
	 * Singleton constructor.
	 *
	 * @param \WP_Framework $app
	 * @param \ReflectionClass $reflection
	 */
	private function __construct( \WP_Framework $app, \ReflectionClass $reflection ) {
		$this->init( $app, $reflection );
	}

	/**
	 * @param \WP_Framework $app
	 * @param \ReflectionClass $reflection
	 */
	protected function init( \WP_Framework $app, \ReflectionClass $reflection ) {
		$this->app         = $app;
		$this->_reflection = $reflection;
		$this->_class_name = $reflection->getName();
		if ( $this instanceof \WP_Framework_Core\Interfaces\Hook ) {
			if ( $app->has_initialized() ) {
				$this->call_initialized();
			} else {
				add_action( $this->get_filter_prefix() . 'app_initialized', function () {
					$this->call_initialized();
				} );
			}
		}
	}

	/**
	 * initialize
	 */
	protected function initialize() {

	}

	/**
	 * initialized
	 */
	protected function initialized() {

	}

	/**
	 * call initialize
	 */
	private function call_initialize() {
		if ( $this->_initialize_called || ! $this->app->is_enough_version() ) {
			return;
		}
		$this->_initialize_called = true;
		$this->set_allowed_access( true );
		$this->initialize();
		$this->set_allowed_access( false );
	}

	/**
	 * call initialized
	 */
	private function call_initialized() {
		$this->call_initialize();
		if ( $this->_initialized_called || ! $this->app->is_enough_version() ) {
			return;
		}
		$this->_initialized_called = true;
		$this->initialized();
	}

	/**
	 * @param string $config_name
	 * @param string $suffix
	 *
	 * @return string
	 */
	public function get_slug( $config_name, $suffix = '-' ) {
		if ( ! isset( self::$_slugs[ $this->app->plugin_name ][ $config_name ] ) ) {
			$default = $this->app->slug_name . $suffix;
			$slug    = $this->app->get_config( 'slug', $config_name, $default );
			if ( empty( $slug ) ) {
				$slug = $default;
			}
			self::$_slugs[ $this->app->plugin_name ][ $config_name ] = $slug;
		}

		return self::$_slugs[ $this->app->plugin_name ][ $config_name ];
	}

	/**
	 * @param string $method
	 *
	 * @return bool
	 */
	public function is_filter_callable( $method ) {
		return method_exists( $this, $method ) && is_callable( [ $this, $method ] );
	}

	/**
	 * @param string $method
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function filter_callback( $method, array $args ) {
		return call_user_func( [ $this, $method ], ...$args );
	}

	/**
	 * @return string
	 */
	protected function get_file_slug() {
		$class    = get_class( $this );
		$exploded = explode( '\\', $class );
		$slug     = end( $exploded );

		return strtolower( $slug );
	}

	/**
	 * @return string
	 */
	public function get_class_name() {
		return $this->_class_name;
	}

	/**
	 * @return \ReflectionClass
	 */
	public function get_reflection() {
		return $this->_reflection;
	}
}

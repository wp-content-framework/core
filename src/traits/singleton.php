<?php
/**
 * WP_Framework_Core Traits Singleton
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
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
		$key = static::is_shared_class() ? '' : $app->plugin_name;
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
				$instance = new static( $app, $reflection );
				if ( $app->is_uninstall() && $instance instanceof \WP_Framework_Common\Interfaces\Uninstall ) {
					$app->uninstall->add_uninstall( [ $instance, 'uninstall' ], $instance->get_uninstall_priority() );
				}
				self::$_instances[ $key ][ $class ] = $instance;
				$instance->set_allowed_access( true );
				$instance->initialize();
				$instance->set_allowed_access( false );
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
				$this->initialized();
			} else {
				add_action( $this->get_filter_prefix() . 'app_initialized', function () {
					$this->initialized();
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
	 * @param string $name
	 * @param callable $func
	 *
	 * @return bool
	 */
	protected function lock_process( $name, callable $func ) {
		$name .= '__LOCK_PROCESS__';
		$this->app->option->reload_options();
		$check = $this->app->option->get( $name );
		if ( ! empty( $check ) ) {
			return false;
		}
		$rand = md5( uniqid() );
		$this->app->option->set( $name, $rand );
		$this->app->option->reload_options();
		if ( $this->app->option->get( $name ) != $rand ) {
			return false;
		}
		$func();
		$this->app->option->delete( $name );

		return true;
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

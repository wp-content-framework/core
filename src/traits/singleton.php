<?php
/**
 * WP_Framework_Core Traits Singleton
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Core\Traits;

use Exception;
use ReflectionClass;
use WP_Framework;
use WP_Framework_Common\Interfaces\Uninstall;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Trait Singleton
 * @package WP_Framework_Core\Traits
 * @property WP_Framework $app
 */
trait Singleton {

	use Readonly, Translate, Utility, Package;

	/**
	 * @var Singleton[][] $instances
	 */
	private static $instances = [];

	/**
	 * @var string[][] $slugs
	 */
	private static $slugs = [];

	/**
	 * @var WP_Framework $app
	 */
	protected $app;

	/**
	 * @var bool $initialize_called
	 */
	private $initialize_called = false;

	/**
	 * @var bool $initialized_called
	 */
	private $initialized_called = false;

	/**
	 * @var string $class_name
	 */
	private $class_name;

	/**
	 * @var string $class_name_slug
	 */
	private $class_name_slug;

	/**
	 * @var ReflectionClass $reflection
	 */
	private $reflection;

	/**
	 * @param WP_Framework $app
	 *
	 * @return Singleton
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	public static function get_instance( WP_Framework $app ) {
		$_class = get_called_class();
		if ( false === $_class ) {
			$_class = get_class();
		}

		list( $mapped, $class ) = $app->get_mapped_class( $_class );
		if ( $mapped ) {
			$key = $app->plugin_name;
		} else {
			$key = static::is_shared_class() ? '' : $app->plugin_name;
		}
		if ( empty( $class ) ) {
			$class = $_class;
		}

		if ( ! array_key_exists( $key, self::$instances ) || ! array_key_exists( $class, self::$instances[ $key ] ) ) {
			try {
				$reflection = new ReflectionClass( $class );
			} catch ( Exception $e ) {
				WP_Framework::kill( [ 'unexpected error has occurred.', $e->getMessage(), $class, $_class ], __FILE__, __LINE__ );
				exit;
			}
			if ( $reflection->isAbstract() ) {
				self::$instances[ $key ][ $class ] = null;
			} else {
				if ( $mapped ) {
					/** @var Singleton $class */
					$instance                          = $class::get_instance( $app );
					self::$instances[ $key ][ $class ] = $instance;
				} else {
					$instance = new static( $app, $reflection );
					if ( $app->is_uninstall() && $instance instanceof Uninstall ) {
						$app->uninstall->add_uninstall( function () use ( $instance ) {
							$instance->uninstall();
						}, $instance->get_uninstall_priority() );
					}
					self::$instances[ $key ][ $class ] = $instance;
					$instance->call_initialize();
				}
			}
		}

		return self::$instances[ $key ][ $class ];
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
	 * @param WP_Framework $app
	 * @param ReflectionClass $reflection
	 */
	private function __construct( WP_Framework $app, ReflectionClass $reflection ) {
		$this->init( $app, $reflection );
	}

	/**
	 * @param WP_Framework $app
	 * @param ReflectionClass $reflection
	 */
	protected function init( WP_Framework $app, ReflectionClass $reflection ) {
		$this->app        = $app;
		$this->reflection = $reflection;
		$this->class_name = $reflection->getName();
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
		if ( $this->initialize_called ) {
			return;
		}
		$this->initialize_called = true;
		$this->set_allowed_access( true );
		$this->initialize();
		$this->set_allowed_access( false );
	}

	/**
	 * call initialized
	 */
	private function call_initialized() {
		$this->call_initialize();
		if ( $this->initialized_called ) {
			return;
		}
		$this->initialized_called = true;
		$this->initialized();
	}

	/**
	 * @param string $config_name
	 * @param string $suffix
	 *
	 * @return string
	 */
	public function get_slug( $config_name, $suffix = '-' ) {
		if ( ! array_key_exists( $this->app->plugin_name, self::$slugs ) || ! array_key_exists( $config_name, self::$slugs[ $this->app->plugin_name ] ) ) {
			$default = $this->app->slug_name . $suffix;
			$slug    = $this->app->get_config( 'slug', $config_name, $default );
			if ( empty( $slug ) ) {
				$slug = $default;
			}
			self::$slugs[ $this->app->plugin_name ][ $config_name ] = $slug;
		}

		return self::$slugs[ $this->app->plugin_name ][ $config_name ];
	}

	/**
	 * @param string $method
	 *
	 * @return bool
	 */
	public function is_filter_callable( $method ) {
		return $this->is_method_callable( $method );
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
		return $this->class_name;
	}

	/**
	 * @return string
	 */
	public function get_class_name_slug() {
		if ( ! isset( $this->class_name_slug ) ) {
			$this->class_name_slug = strtolower( str_replace( [ '_', '\\' ], [ '-', '_' ], $this->get_class_name() ) );
		}

		return $this->class_name_slug;
	}

	/**
	 * @return ReflectionClass
	 */
	public function get_reflection() {
		return $this->reflection;
	}

	/**
	 * @param string $name
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function __call( $name, array $args ) {
		return $this->app->deprecated->call( static::class, $this, $name, $args );
	}
}

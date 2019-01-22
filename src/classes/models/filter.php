<?php
/**
 * WP_Framework Classes Models Filter
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
 * Class Filter
 * @package WP_Framework\Classes\Models
 */
class Filter implements \WP_Framework\Interfaces\Singleton, \WP_Framework\Interfaces\Hook {

	use \WP_Framework\Traits\Singleton, \WP_Framework\Traits\Hook;

	/**
	 * @var array $_target_app
	 */
	private $_target_app = [];

	/**
	 * initialize
	 */
	protected function initialize() {
		foreach ( $this->apply_filters( 'filter', $this->app->config->load( 'filter' ) ) as $class => $tags ) {
			$this->register_class_filter( $class, $tags );
		}
	}

	/**
	 * @param string $class
	 * @param array $tags
	 */
	public function register_class_filter( $class, array $tags ) {
		if ( empty( $class ) || ! is_array( $tags ) ) {
			return;
		}
		foreach ( $tags as $tag => $methods ) {
			$this->register_filter( $class, $tag, $methods );
		}
	}

	/**
	 * @param string $class
	 * @param string $tag
	 * @param array $methods
	 */
	public function register_filter( $class, $tag, array $methods ) {
		$tag = $this->app->utility->replace( $tag, [ 'prefix' => $this->get_filter_prefix() ] );
		if ( empty( $class ) || empty( $tag ) || ! is_array( $methods ) ) {
			return;
		}
		foreach ( $methods as $method => $params ) {
			if ( ! is_array( $params ) && is_string( $params ) ) {
				$method = $params;
				$params = [];
			}
			if ( empty( $method ) || ! is_string( $method ) ) {
				continue;
			}
			list( $priority, $accepted_args ) = $this->get_filter_params( $params );
			add_filter( $tag, function () use ( $class, $method ) {
				return $this->call_filter_callback( $class, $method, func_get_args() );
			}, $priority, $accepted_args );
		}
	}

	/**
	 * @param string $class
	 *
	 * @return false|\WP_Framework|\WP_Framework\Interfaces\Singleton
	 */
	private function get_target_app( $class ) {
		if ( ! isset( $this->_target_app[ $class ] ) ) {
			$app = false;
			if ( strpos( $class, '->' ) !== false ) {
				$app      = $this->app;
				$exploded = explode( '->', $class );
				foreach ( $exploded as $property ) {
					if ( isset( $app->$property ) ) {
						$app = $app->$property;
					} else {
						$app = false;
						break;
					}
				}
			} else {
				if ( isset( $this->app->$class ) ) {
					$app = $this->app->$class;
				}
			}
			if ( false === $app ) {
				if ( class_exists( $class ) && is_subclass_of( $class, '\WP_Framework\Interfaces\Singleton' ) ) {
					try {
						/** @var \WP_Framework\Interfaces\Singleton $class */
						$app = $class::get_instance( $this->app );
					} catch ( \Exception $e ) {
					}
				}
			}
			$this->_target_app[ $class ] = $app;
		}

		return $this->_target_app[ $class ];
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	private function get_filter_params( array $params ) {
		$priority      = 10;
		$accepted_args = 100;
		if ( is_array( $params ) ) {
			if ( count( $params ) >= 1 ) {
				$priority = $params[0];
			}
			if ( count( $params ) >= 2 ) {
				$accepted_args = $params[1];
			}
		}

		return [ $priority, $accepted_args ];
	}

	/**
	 * @param string $class
	 * @param string $method
	 * @param array $args
	 *
	 * @return mixed
	 */
	private function call_filter_callback( $class, $method, array $args ) {
		$result = empty( $args ) ? null : reset( $args );
		$app    = $this->get_target_app( $class );
		if ( empty( $app ) ) {
			return $result;
		}

		if ( $app->is_filter_callable( $method ) ) {
			return $app->filter_callback( $method, $args );
		}

		return $result;
	}
}

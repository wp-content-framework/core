<?php
/**
 * WP_Framework Classes Models Loader
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
 * Class Loader
 * @package WP_Framework\Classes\Models
 * @property-read \WP_Framework\Classes\Models\Loader\Controller\Admin $admin
 * @property-read \WP_Framework\Classes\Models\Loader\Controller\Api $api
 * @property-read \WP_Framework\Classes\Models\Loader\Cron $cron
 */
class Loader implements \WP_Framework\Interfaces\Singleton, \WP_Framework\Interfaces\Hook {

	use \WP_Framework\Traits\Singleton, \WP_Framework\Traits\Hook;

	/**
	 * @var array $readonly_properties
	 */
	protected $readonly_properties = [
		'admin',
		'api',
		'cron',
	];

	/**
	 * initialize
	 */
	protected function initialize() {
		$scan_dir  = $this->app->define->lib_src_dir . DS . 'classes' . DS . 'models' . DS . 'loader';
		$namespace = $this->app->define->lib_namespace . '\\Classes\\Models\\Loader\\';
		foreach ( $this->app->utility->scan_dir_namespace_class( $scan_dir, false, $namespace ) as $class ) {
			if ( class_exists( $class ) && is_subclass_of( $class, '\WP_Framework\Interfaces\Singleton' ) ) {
				try {
					/** @var \WP_Framework\Traits\Singleton $class */
					$loader = $class::get_instance( $this->app );
					if ( $loader instanceof \WP_Framework\Interfaces\Loader ) {
						/** @var \WP_Framework\Interfaces\Loader $loader */
						$name        = $loader->get_loader_name();
						$this->$name = $loader;
					}
				} catch ( \Exception $e ) {
				}
			}
		}
	}
}

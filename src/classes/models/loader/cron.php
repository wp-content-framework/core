<?php
/**
 * WP_Framework Classes Models Loader Cron
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework\Classes\Models\Loader;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Cron
 * @package WP_Framework\Classes\Models\Loader
 */
class Cron implements \WP_Framework\Interfaces\Loader {

	use \WP_Framework\Traits\Loader;

	/**
	 * initialized
	 */
	protected function initialized() {
		$this->get_class_list();
	}

	/**
	 * @return string
	 */
	protected function get_instanceof() {
		return '\WP_Framework\Classes\Crons\Base';
	}

	/**
	 * @return array
	 */
	public function get_cron_class_names() {
		$list = $this->get_class_list();

		return array_keys( $list );
	}

	/**
	 * @return array
	 */
	protected function get_namespaces() {
		return [
			$this->app->define->plugin_namespace . '\\Classes\\Crons',
			$this->app->define->lib_namespace . '\\Classes\\Crons',
		];
	}
}

<?php
/**
 * WP_Framework Classes Models Log
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
 * Class Log
 * @package WP_Framework\Classes\Models
 */
class Log implements \WP_Framework\Interfaces\Singleton, \WP_Framework\Interfaces\Hook, \WP_Framework\Interfaces\Presenter {

	use \WP_Framework\Traits\Singleton, \WP_Framework\Traits\Hook, \WP_Framework\Traits\Presenter;

	/**
	 * setup shutdown
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function setup_shutdown() {
		if ( $this->apply_filters( 'capture_shutdown_error' ) && $this->is_valid() ) {
			add_action( 'shutdown', function () {
				$this->shutdown();
			}, 0 );
		}
	}

	/**
	 * shutdown
	 */
	private function shutdown() {
		$error = error_get_last();
		if ( $error === null ) {
			return;
		}

		if ( $error['type'] & $this->app->get_config( 'config', 'target_shutdown_error' ) ) {
			$suppress = $this->app->get_config( 'config', 'suppress_log_messages' );
			$message  = str_replace( [ "\r\n", "\r", "\n" ], "\n", $error['message'] );
			$messages = explode( "\n", $message );
			$message  = reset( $messages );
			if ( empty( $suppress ) || ( is_array( $suppress ) && ! in_array( $message, $suppress ) ) ) {
				$this->app->log( $message, $error, 'error' );
			}
		}
	}

	/**
	 * @return bool
	 */
	public function is_valid() {
		if ( $this->app->get_config( 'config', 'prevent_use_log' ) ) {
			return false;
		}

		return $this->apply_filters( 'log_validity', $this->apply_filters( 'is_valid_log' ) );
	}

	/**
	 * @param string $message
	 * @param mixed $context
	 * @param string $level
	 *
	 * @return bool
	 */
	public function log( $message, $context = null, $level = '' ) {
		if ( ! $this->is_valid() ) {
			return false;
		}

		$log_level = $this->app->get_config( 'config', 'log_level' );
		$level     = $this->get_log_level( $level, $log_level );
		if ( empty( $log_level[ $level ] ) ) {
			return false;
		}

		global $wp_version;
		$data                      = $this->get_called_info();
		$data['message']           = is_string( $message ) ? $this->app->translate( $message ) : json_encode( $message );
		$data['lib_version']       = $this->app->get_library_version();
		$data['plugin_version']    = $this->app->get_plugin_version();
		$data['php_version']       = phpversion();
		$data['wordpress_version'] = $wp_version;
		$data['level']             = $level;
		if ( isset( $context ) ) {
			$data['context'] = json_encode( $context );
		}

		$this->send_mail( $level, $log_level, $message, $data );
		$this->insert_log( $level, $log_level, $data );

		return true;
	}

	/**
	 * @param string $level
	 * @param array $log_level
	 *
	 * @return string
	 */
	private function get_log_level( $level, array $log_level ) {
		if ( ! isset( $log_level[ $level ] ) && ! isset( $log_level[''] ) ) {
			return 'info';
		}
		'' === $level || ! isset( $log_level[ $level ] ) and $level = $log_level[''];
		if ( empty( $log_level[ $level ] ) ) {
			return 'info';
		}

		return $level;
	}

	/**
	 * @param string $level
	 * @param array $log_level
	 * @param array $data
	 */
	private function insert_log( $level, array $log_level, array $data ) {
		if ( empty( $log_level[ $level ]['is_valid_log'] ) ) {
			return;
		}
		if ( $this->apply_filters( 'save___log_term' ) <= 0 ) {
			return;
		}
		$this->app->db->insert( '__log', $data );
	}

	/**
	 * @param string $level
	 * @param array $log_level
	 * @param string $message
	 * @param array $data
	 */
	private function send_mail( $level, array $log_level, $message, array $data ) {
		if ( empty( $log_level[ $level ]['is_valid_mail'] ) ) {
			return;
		}

		$level  = $log_level[ $level ];
		$roles  = $this->app->utility->array_get( $level, 'roles' );
		$emails = $this->app->utility->array_get( $level, 'emails' );

		if ( empty( $roles ) && empty( $emails ) ) {
			return;
		}

		$emails = array_unique( $emails );
		$emails = array_combine( $emails, $emails );
		foreach ( $roles as $role ) {
			foreach ( get_users( [ 'role' => $role ] ) as $user ) {
				/** @var \WP_User $user */
				! empty( $user->user_email ) and $emails[ $user->user_email ] = $user->user_email;
			}
		}

		foreach ( $emails as $email ) {
			$this->app->mail->send( $email, $message, $this->dump( $data, false ) );
		}
	}

	/**
	 * @return array
	 */
	private function get_called_info() {
		$next = false;
		foreach ( $this->app->utility->get_debug_backtrace() as $item ) {
			if ( $next ) {
				$return = [];
				isset( $item['file'] ) and $return['file'] = preg_replace( '/' . preg_quote( ABSPATH, '/' ) . '/A', '', $item['file'] );
				isset( $item['line'] ) and $return['line'] = $item['line'];

				return $return;
			}
			if ( ! empty( $item['class'] ) && __CLASS__ === $item['class'] && $item['function'] === 'log' ) {
				$next = true;
			}
		}

		return [];
	}

	/**
	 * @return int
	 */
	public function delete_old_logs() {
		$count = 0;
		$term  = $this->apply_filters( 'save___log_term' );
		foreach (
			$this->app->db->select( '__log', [
				'created_at' => [ '<', 'NOW() - INTERVAL ' . (int) $term . ' SECOND', true ],
			] ) as $log
		) {
			$this->app->db->delete( '__log', [
				'id' => $log['id'],
			] );
			$count ++;
		}

		return $count;
	}
}

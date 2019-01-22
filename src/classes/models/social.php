<?php
/**
 * WP_Framework Classes Models Social
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
 * Class Social
 * @package WP_Framework\Classes\Models
 */
class Social implements \WP_Framework\Interfaces\Loader {

	use \WP_Framework\Traits\Loader;

	/**
	 * initialize
	 */
	protected function initialize() {
		$this->app->filter->register_class_filter( 'social', [
			'template_redirect' => [
				'check_callback' => [],
			],
		] );
	}

	/**
	 * @return array
	 */
	protected function get_namespaces() {
		return [
			$this->app->define->plugin_namespace . '\\Classes\\Models\\Social',
		];
	}

	/**
	 * @return string
	 */
	protected function get_instanceof() {
		return '\WP_Framework\Interfaces\Helper\Social';
	}

	/**
	 * check callback
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function check_callback() {
		$code  = $this->app->input->get( 'code' );
		$error = $this->app->input->get( 'error' );
		$state = $this->app->input->get( 'state' );
		if ( ( empty( $code ) && empty( $error ) ) || empty( $state ) ) {
			return;
		}

		$state_params = $this->decode_state( $state );
		$class        = $this->get_social_service( $state_params );
		if ( empty( $class ) ) {
			return;
		}

		if ( ! $class->check_state_params( $state_params ) ) {
			return;
		}

		if ( ! empty( $error ) ) {
			$this->app->log( 'social error', $this->app->input->get() );
			$this->safe_redirect( $state_params );

			return;
		}

		list( $client_id, $client_secret ) = $class->get_oauth_settings();
		if ( empty( $client_id ) || empty( $client_secret ) ) {
			$this->safe_redirect( $state_params );

			return;
		}

		$access_token = $class->get_access_token( $code, $client_id, $client_secret );
		if ( empty( $access_token ) ) {
			$this->safe_redirect( $state_params );

			return;
		}

		$user = $class->get_user_info( $access_token );
		if ( empty( $user ) || empty( $user['id'] ) ) {
			$this->app->log( 'get user info error', $user );
			$this->safe_redirect( $state_params );

			return;
		}

		$class->register_or_login_customer( $user );
		$this->safe_redirect( $state_params );
	}

	/**
	 * @param string $state
	 *
	 * @return array|null|bool
	 */
	private function decode_state( $state ) {
		return @json_decode( base64_decode( strtr( $state, '-_,', '+/=' ) ), true );
	}

	/**
	 * @param mixed $state
	 *
	 * @return string|null
	 */
	private function get_social_service_name( $state ) {
		return $this->app->utility->array_get( $state, 'service' );
	}

	/**
	 * @param mixed $state
	 *
	 * @return \WP_Framework\Interfaces\Helper\Social|null
	 */
	private function get_social_service( $state ) {
		$name = $this->get_social_service_name( $state );
		if ( empty( $name ) ) {
			return null;
		}
		foreach ( $this->get_class_list() as $class ) {
			/** @var \WP_Framework\Interfaces\Helper\Social $class */
			if ( $name === $class->get_service_name() ) {
				return $class;
			}
		}

		return null;
	}

	/**
	 * @param array $params
	 */
	private function safe_redirect( $params ) {
		if ( ! empty( $params['redirect'] ) && preg_match( '#\A/[^/]+#', $params['redirect'] ) ) {
			wp_safe_redirect( $params['redirect'] );
			exit;
		}
	}

	/**
	 * @return string
	 */
	private function get_pseudo_email_domain() {
		return $this->apply_filters( 'pseudo_email_domain', $this->app->slug_name . '-pseudo.example.com' );
	}

	/**
	 * @param string $id
	 *
	 * @return string
	 */
	public function get_pseudo_email( $id ) {
		return $id . '@' . $this->get_pseudo_email_domain();
	}

	/**
	 * @param string $email
	 *
	 * @return bool
	 */
	public function is_pseudo_email( $email ) {
		return preg_match( '#' . preg_quote( '@' . $this->get_pseudo_email_domain() ) . '\z#', trim( $email ) ) > 0;
	}

	/**
	 * @param string $email
	 *
	 * @return string
	 */
	public function filter_pseudo_email( $email ) {
		return $this->is_pseudo_email( $email ) ? '' : $email;
	}

	/**
	 * @return array
	 */
	public function get_social_settings() {
		$settings = [];
		foreach ( $this->get_class_list() as $class ) {
			/** @var \WP_Framework\Interfaces\Helper\Social $class */
			$link = $class->get_oauth_link();
			if ( $link ) {
				$name              = $class->get_service_name();
				$settings[ $name ] = [
					'url'      => $link,
					'args'     => $class->get_link_args(),
					'contents' => $class->get_link_contents(),
				];
			}
		}

		return $settings;
	}
}

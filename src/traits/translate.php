<?php
/**
 * WP_Framework_Core Traits Translate
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Core\Traits;

use WP_Framework;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Trait Translate
 * @package WP_Framework_Core\Traits
 * @property WP_Framework $app
 * @mixin Package
 */
trait Translate {

	/**
	 * @var array $loaded_languages
	 */
	private static $loaded_languages = [];

	/**
	 * @var array $textdomains
	 */
	private static $textdomains = [];

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	public function translate( $value ) {
		foreach ( array_keys( $this->get_textdomains() ) as $textdomain ) {
			$translated = __( $value, $textdomain ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText, WordPress.WP.I18n.NonSingularStringLiteralDomain
			if ( $value !== $translated ) {
				return $translated;
			}
		}

		return $value;
	}

	/**
	 * @return array
	 */
	protected function get_textdomains() {
		$package = $this->get_package();
		if ( ! array_key_exists( $this->app->plugin_name, self::$textdomains ) || ! array_key_exists( $package, self::$textdomains[ $this->app->plugin_name ] ) ) {
			self::$textdomains[ $this->app->plugin_name ][ $package ] = [];
			if ( ! empty( $this->app->define->plugin_textdomain ) ) {
				self::$textdomains[ $this->app->plugin_name ][ $package ][ $this->app->define->plugin_textdomain ] = $this->app->define->plugin_languages_dir;
			}
			$instance = $this->get_package_instance();
			foreach ( $instance->get_translate_settings() as $textdomain => $path ) {
				self::$textdomains[ $this->app->plugin_name ][ $package ][ $textdomain ] = $path;
			}

			foreach ( self::$textdomains[ $this->app->plugin_name ][ $package ] as $textdomain => $path ) {
				if ( ! $this->setup_textdomain( $textdomain, $path ) ) {
					unset( self::$textdomains[ $this->app->plugin_name ][ $package ][ $textdomain ] );
				}
			}
		}

		return self::$textdomains[ $this->app->plugin_name ][ $package ];
	}

	/**
	 * @param string $textdomain
	 * @param string $dir
	 *
	 * @return bool
	 */
	private function setup_textdomain( $textdomain, $dir ) {
		if ( ! array_key_exists( $textdomain, self::$loaded_languages ) ) {
			if ( function_exists( 'determine_locale' ) ) {
				$locale = apply_filters( 'plugin_locale', determine_locale(), $textdomain );
			} else {
				$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );
			}
			$mofile = $textdomain . '-' . $locale . '.mo';
			$path   = $dir . DS . $mofile;

			self::$loaded_languages[ $textdomain ] = load_textdomain( $textdomain, $path );
		}

		return self::$loaded_languages[ $textdomain ];
	}
}

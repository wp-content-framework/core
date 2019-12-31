<?php
/**
 * WP_Framework_Core Traits Helper Data Helper
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Core\Traits\Helper;

use WP_Framework;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Trait Data_Helper
 * @package WP_Framework_Core\Traits\Helper
 * @property WP_Framework $app
 */
trait Data_Helper {

	/**
	 * @param array $data
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function convert_to_bool( array $data, $key ) {
		return ! empty( $data[ $key ] ) && '0' !== $data[ $key ] && 'false' !== $data[ $key ];
	}

	/**
	 * @param string $str
	 *
	 * @return string
	 */
	protected function the_content( $str ) {
		return apply_filters( 'the_content', $str );
	}

	/**
	 * @param mixed $param
	 * @param string $type
	 * @param bool $check_null
	 * @param bool $nullable
	 * @param bool $update
	 *
	 * @return mixed
	 */
	protected function sanitize_input( $param, $type, $check_null = false, $nullable = false, $update = false ) {
		if ( $check_null && is_null( $param ) ) {
			return null;
		}

		switch ( $type ) {
			case 'int':
				return $this->sanitize_int( $param );
			case 'float':
			case 'number':
				return $this->sanitize_number( $param );
			case 'bool':
				return $this->sanitize_bool( $param, $nullable, $update );
			default:
				return $this->sanitize_misc( $param );
		}
	}

	/**
	 * @param mixed $param
	 *
	 * @return int|null
	 */
	private function sanitize_int( $param ) {
		if ( ! is_int( $param ) && ! ctype_digit( ltrim( $param, '-' ) ) ) {
			return null;
		}
		$param = (int) $param;

		return $param;
	}

	/**
	 * @param mixed $param
	 *
	 * @return float|null
	 */
	private function sanitize_number( $param ) {
		if ( ! is_numeric( $param ) && ! ctype_alpha( $param ) ) {
			return null;
		}
		$param -= 0.0;

		return $param;
	}

	/**
	 * @param mixed $param
	 * @param bool $nullable
	 * @param bool $update
	 *
	 * @return int|string|null
	 */
	private function sanitize_bool( $param, $nullable, $update ) {
		if ( $nullable && ( is_null( $param ) || '' === $param ) ) {
			return null;
		}
		if ( $update && '' === $param ) {
			return null;
		}
		if ( is_string( $param ) ) {
			$param = strtolower( trim( $param ) );
			if ( 'true' === $param ) {
				$param = 1;
			} elseif ( 'false' === $param ) {
				$param = 0;
			} elseif ( '0' === $param ) {
				$param = 0;
			} else {
				$param = ! empty( $param ) ? 1 : 0;
			}
		} else {
			$param = ! empty( $param ) ? 1 : 0;
		}

		return $param;
	}

	/**
	 * @param mixed $param
	 *
	 * @return mixed
	 */
	private function sanitize_misc( $param ) {
		if ( is_null( $param ) || '' === (string) $param ) {
			return null;
		}

		return $param;
	}

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	protected function is_default( $value ) {
		return ! is_array( $value ) && ! is_bool( $value ) && '' === (string) ( $value );
	}
}

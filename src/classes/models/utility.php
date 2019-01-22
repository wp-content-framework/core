<?php
/**
 * WP_Framework Classes Models Utility
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
 * Class Utility
 * @package WP_Framework\Classes\Models
 */
class Utility implements \WP_Framework\Interfaces\Singleton {

	use \WP_Framework\Traits\Singleton;

	/**
	 * @var string[] $_replace_time
	 */
	private $_replace_time;

	/**
	 * @return bool
	 */
	protected static function is_shared_class() {
		return true;
	}

	/**
	 * @param array $array
	 * @param bool $preserve_keys
	 *
	 * @return array
	 */
	public function flatten( array $array, $preserve_keys = false ) {
		$return = [];
		array_walk_recursive( $array, function ( $v, $k ) use ( &$return, $preserve_keys ) {
			if ( $preserve_keys ) {
				$return[ $k ] = $v;
			} else {
				$return[] = $v;
			}
		} );

		return $return;
	}

	/**
	 * @return string
	 */
	public function uuid() {
		$pid  = getmypid();
		$node = isset( $_SERVER['SERVER_ADDR'] ) ? $_SERVER['SERVER_ADDR'] : '0.0.0.0';
		list( $timeMid, $timeLow ) = explode( ' ', microtime() );

		return sprintf( "%08x%04x%04x%02x%02x%04x%08x", (int) $timeLow, (int) substr( $timeMid, 2 ) & 0xffff,
			mt_rand( 0, 0xfff ) | 0x4000, mt_rand( 0, 0x3f ) | 0x80, mt_rand( 0, 0xff ), $pid & 0xffff, $node );
	}

	/**
	 * @param string $c
	 *
	 * @return bool
	 */
	public function defined( $c ) {
		if ( defined( $c ) ) {
			$const = @constant( $c );
			if ( $const ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $c
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	public function definedv( $c, $default = null ) {
		if ( defined( $c ) ) {
			$const = @constant( $c );

			return $const;
		}

		return $default;
	}

	/**
	 * @param array|object $obj
	 *
	 * @return array
	 */
	private function get_array_value( $obj ) {
		if ( $obj instanceof \stdClass ) {
			$obj = get_object_vars( $obj );
		} elseif ( ! is_array( $obj ) ) {
			if ( method_exists( $obj, 'to_array' ) ) {
				$obj = $obj->to_array();
			}
		}
		if ( ! is_array( $obj ) || empty( $obj ) ) {
			return [];
		}

		return $obj;
	}

	/**
	 * @param array|object $array
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function array_get( $array, $key, $default = null ) {
		$array = $this->get_array_value( $array );
		if ( array_key_exists( $key, $array ) ) {
			return $array[ $key ];
		}

		return $default;
	}

	/**
	 * @param array $array
	 * @param string $key
	 * @param mixed $value
	 */
	public function array_set( array &$array, $key, $value ) {
		$array[ $key ] = $value;
	}

	/**
	 * @param array|object $array
	 * @param string $key
	 * @param mixed $default
	 * @param bool $filter
	 *
	 * @return array
	 */
	public function array_pluck( $array, $key, $default = null, $filter = false ) {
		$array = $this->get_array_value( $array );

		return array_map( function ( $d ) use ( $key, $default ) {
			is_object( $d ) and $d = (array) $d;

			return is_array( $d ) && array_key_exists( $key, $d ) ? $d[ $key ] : $default;
		}, $filter ? array_filter( $array, function ( $d ) use ( $key ) {
			is_object( $d ) and $d = (array) $d;

			return is_array( $d ) && array_key_exists( $key, $d );
		} ) : $array );
	}

	/**
	 * @param array|object $array
	 * @param string|callable $callback
	 *
	 * @return array
	 */
	public function array_map( $array, $callback ) {
		$array = $this->get_array_value( $array );

		return array_map( function ( $d ) use ( $callback ) {
			return is_callable( $callback ) ? $callback( $d ) : ( is_string( $callback ) && method_exists( $d, $callback ) ? $d->$callback() : null );
		}, $array );
	}

	/**
	 * @param array|object $array
	 * @param string $key
	 *
	 * @return array
	 */
	public function array_pluck_unique( $array, $key ) {
		return array_unique( $this->array_pluck( $array, $key, null, true ) );
	}

	/**
	 * @param array $array
	 * @param string $key
	 * @param string $value
	 *
	 * @return array
	 */
	public function array_combine( array $array, $key, $value = null ) {
		$keys   = $this->array_pluck( $array, $key );
		$values = empty( $value ) ? $array : $this->array_pluck( $array, $value );

		return array_combine( $keys, $values );
	}

	/**
	 * @param string $string
	 * @param array $data
	 *
	 * @return string
	 */
	public function replace( $string, array $data ) {
		foreach ( $data as $k => $v ) {
			$string = str_replace( '${' . $k . '}', $v, $string );
		}

		return $string;
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function replace_time( $string ) {
		if ( ! isset( $this->_replace_time ) ) {
			$this->_replace_time = [];
			foreach (
				[
					'Y',
					'y',
					'M',
					'm',
					'n',
					'D',
					'd',
					'H',
					'h',
					'i',
					'j',
					's',
				] as $t
			) {
				$this->_replace_time[ $t ] = date_i18n( $t );
			}
		}

		return $this->replace( $string, $this->_replace_time );
	}

	/**
	 * @param string $data
	 * @param string $key
	 *
	 * @return false|string
	 */
	public function create_hash( $data, $key ) {
		return hash_hmac( function_exists( 'hash' ) ? 'sha256' : 'sha1', $data, $key );
	}

	/**
	 * @param string $command
	 *
	 * @return array
	 */
	public function exec( $command ) {
		$command .= ' 2>&1';
		$command = escapeshellcmd( $command );
		exec( $command, $output, $return_var );

		return [ $output, $return_var ];
	}

	/**
	 * @param string $command
	 */
	public function exec_async( $command ) {
		$command = escapeshellcmd( $command );
		if ( PHP_OS !== 'WIN32' && PHP_OS !== 'WINNT' ) {
			exec( $command . ' >/dev/null 2>&1 &' );
		} else {
			$fp = popen( 'start "" ' . $command, 'r' );
			pclose( $fp );
		}
	}

	/**
	 * @param string $haystack
	 * @param string $needle
	 *
	 * @return bool
	 */
	public function starts_with( $haystack, $needle ) {
		if ( '' === $haystack || '' === $needle ) {
			return false;
		}
		if ( $haystack === $needle ) {
			return true;
		}

		return strncmp( $haystack, $needle, strlen( $needle ) ) === 0;
	}

	/**
	 * @param string $haystack
	 * @param string $needle
	 *
	 * @return bool
	 */
	public function ends_with( $haystack, $needle ) {
		if ( '' === $haystack || '' === $needle ) {
			return false;
		}
		if ( $haystack === $needle ) {
			return true;
		}

		return substr_compare( $haystack, $needle, - strlen( $needle ) ) === 0;
	}

	/**
	 * @return bool
	 */
	public function doing_ajax() {
		if ( $this->definedv( 'REST_REQUEST' ) ) {
			return true;
		}

		if ( function_exists( 'wp_doing_ajax' ) ) {
			return wp_doing_ajax();
		}

		return $this->definedv( 'DOING_AJAX' );
	}

	/**
	 * @param array $unset
	 *
	 * @return array
	 */
	public function get_debug_backtrace( array $unset = [] ) {
		$backtrace = debug_backtrace();
		foreach ( $backtrace as $k => $v ) {
			// 大量のデータになりがちな object と args を削除や編集
			unset( $backtrace[ $k ]['object'] );
			if ( ! empty( $backtrace[ $k ]['args'] ) ) {
				$backtrace[ $k ]['args'] = $this->parse_backtrace_args( $backtrace[ $k ]['args'] );
			} else {
				unset( $backtrace[ $k ]['args'] );
			}
			if ( ! empty( $unset ) ) {
				foreach ( $v as $key => $value ) {
					if ( in_array( $key, $unset ) ) {
						unset( $backtrace[ $k ][ $key ] );
					}
				}
			}
		}

		return $backtrace;
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	private function parse_backtrace_args( array $args ) {
		return $this->array_map( $args, function ( $d ) {
			$type = gettype( $d );
			if ( 'array' === $type ) {
				return $this->parse_backtrace_args( $d );
			} elseif ( 'object' === $type ) {
				$type = get_class( $d );
			} elseif ( 'resource' !== $type && 'resource (closed)' !== $type && 'NULL' !== $type && 'unknown type' !== $type ) {
				if ( 'boolean' === $type ) {
					$d = var_export( $d, true );
				}
				$type .= ': ' . $d;
			}

			return $type;
		} );
	}

	/**
	 * @param string $dir
	 * @param bool $split
	 * @param string $relative
	 * @param array $ignore
	 *
	 * @return array
	 */
	public function scan_dir_namespace_class( $dir, $split = false, $relative = '', array $ignore = [ 'base.php' ] ) {
		$dir  = rtrim( $dir, DS );
		$list = [];
		if ( is_dir( $dir ) ) {
			foreach ( scandir( $dir ) as $file ) {
				if ( $file === '.' || $file === '..' || in_array( $file, $ignore ) ) {
					continue;
				}

				$path = rtrim( $dir, DS ) . DS . $file;
				if ( is_file( $path ) ) {
					if ( $this->ends_with( $file, '.php' ) ) {
						if ( $split ) {
							$list[] = [ $relative, ucfirst( $this->app->get_page_slug( $file ) ) ];
						} else {
							$list[] = $relative . ucfirst( $this->app->get_page_slug( $file ) );
						}
					}
				} elseif ( is_dir( $path ) ) {
					$list = array_merge( $list, $this->scan_dir_namespace_class( $path, $split, $relative . ucfirst( $file ) . '\\', $ignore ) );
				}
			}
		}

		return $list;
	}

	/**
	 * @param string $type
	 * @param bool $detect_text
	 *
	 * @return string
	 */
	public function parse_db_type( $type, $detect_text = false ) {
		switch ( true ) {
			case stristr( $type, 'TINYINT(1)' ) !== false:
				return 'bool';
			case stristr( $type, 'INT' ) !== false:
				return 'int';
			case stristr( $type, 'BIT' ) !== false:
				return 'bool';
			case stristr( $type, 'BOOLEAN' ) !== false:
				return 'bool';
			case stristr( $type, 'DECIMAL' ) !== false:
				return 'number';
			case stristr( $type, 'FLOAT' ) !== false:
				return 'float';
			case stristr( $type, 'DOUBLE' ) !== false:
				return 'number';
			case stristr( $type, 'REAL' ) !== false:
				return 'number';
			case $detect_text && stristr( $type, 'TEXT' ) !== false:
				return 'text';
		}

		return 'string';
	}
}

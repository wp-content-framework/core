<?php
/**
 * WP_Framework Traits Presenter
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework\Traits;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Trait Presenter
 * @package WP_Framework\Traits
 * @property \WP_Framework $app
 */
trait Presenter {

	/**
	 * @var array $_prev_post
	 */
	private $_prev_post = null;

	/**
	 * @var bool $_set_script_translations
	 */
	private $_set_script_translations = false;

	/**
	 * @return array
	 */
	private function get_check_view_dirs() {
		$dirs = [];
		! empty( $this->app->define->child_theme_views_dir ) and $dirs[] = $this->app->define->child_theme_views_dir;
		$dirs[] = $this->app->define->plugin_views_dir;
		$dirs[] = $this->app->define->lib_views_dir;

		return $this->apply_filters( 'check_view_dirs', $dirs );
	}

	/**
	 * @param string $name
	 * @param array $args
	 * @param bool $echo
	 * @param bool $error
	 * @param bool $remove_nl
	 *
	 * @return string
	 */
	public function get_view( $name, array $args = [], $echo = false, $error = true, $remove_nl = false ) {
		$name = trim( $name, '/' . DS );
		$name = str_replace( '/', DS, $name );
		$name .= '.php';
		$path = null;
		foreach ( $this->get_check_view_dirs() as $dir ) {
			$dir = rtrim( $dir, DS . '/' );
			if ( is_readable( $dir . DS . $name ) ) {
				$path = $dir . DS . $name;
				break;
			}
		}

		$view = '';
		if ( isset( $path ) ) {
			unset( $name );
			$args = $this->get_presenter_args( $args );
			extract( $args, EXTR_SKIP );

			ob_start();
			/** @noinspection PhpIncludeInspection */
			@include $path;
			$view = ob_get_contents();
			ob_end_clean();
		} elseif ( $error ) {
			$this->app->log( sprintf( 'View file [ %s ] not found.', $name ) );
		}

		if ( $remove_nl ) {
			$view = str_replace( [ "\r\n", "\r", "\n" ], ' ', $view );
		}

		if ( $echo ) {
			echo $view;
		}

		return $view;
	}

	/**
	 * @return string
	 */
	private function get_api_class() {
		return $this->get_slug( 'api_class', '_rest_api' );
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	private function get_presenter_args( array $args ) {
		$args['field'] = array_merge( $this->app->utility->array_get( $args, 'field', [] ), $this->app->input->all() );
		if ( $this instanceof \WP_Framework\Interfaces\Nonce ) {
			$args['nonce_key']   = $this->get_nonce_key();
			$args['nonce_value'] = $this->create_nonce();
		}
		$args['instance']  = $this;
		$args['action']    = $this->app->input->server( "REQUEST_URI" );
		$args['is_admin']  = is_admin();
		$args['user_can']  = $this->app->user_can();
		$args['api_class'] = $this->get_api_class();

		return $this->filter_presenter_args( $args );
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	protected function filter_presenter_args( array $args ) {
		return $args;
	}

	/**
	 * @param string $name
	 * @param array $args
	 * @param array $overwrite
	 * @param bool $echo
	 * @param bool $error
	 *
	 * @return string
	 */
	public function form( $name, array $args = [], array $overwrite = [], $echo = true, $error = true ) {
		return $this->get_view( 'include/form/' . trim( $name, '/' . DS ), array_replace_recursive( $args, $overwrite ), $echo, $error );
	}

	/**
	 * @param string $name
	 * @param mixed $data
	 * @param string|null $key
	 * @param string $default
	 * @param bool $checkbox
	 *
	 * @return mixed
	 */
	public function old( $name, $data, $key = null, $default = '', $checkbox = false ) {
		if ( is_array( $data ) ) {
			$default = $this->app->utility->array_get( $data, $key, $default );
		} elseif ( $data instanceof \stdClass ) {
			$default = property_exists( $data, $key ) ? $data->$key : $default;
		} elseif ( ! isset( $key ) ) {
			$default = $data;
		}
		if ( ! isset( $this->_prev_post ) ) {
			$this->_prev_post = $this->app->session->get( $this->get_old_post_session_key(), null );
			if ( empty( $this->_prev_post ) ) {
				$this->_prev_post = [];
			}
			$this->app->session->delete( $this->get_old_post_session_key() );
		}
		if ( $checkbox && ! empty( $this->_prev_post ) ) {
			$default = false;
		}

		return $this->app->utility->array_get( $this->_prev_post, $name, $default );
	}

	/**
	 * @return string
	 */
	protected function get_old_post_session_key() {
		return '__prev_post';
	}

	/**
	 * @param mixed $data
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function dump( $data, $echo = true ) {
		return $this->get_view( 'include/dump', [ 'data' => $data ], $echo );
	}

	/**
	 * @param string $script
	 * @param int $priority
	 */
	public function add_script( $script, $priority = 10 ) {
		$this->app->minify->register_script( $script, $priority );
	}

	/**
	 * @param string $style
	 * @param int $priority
	 */
	public function add_style( $style, $priority = 10 ) {
		$this->app->minify->register_style( $style, $priority );
	}

	/**
	 * @param string $name
	 * @param array $args
	 * @param int $priority
	 */
	public function add_script_view( $name, array $args = [], $priority = 10 ) {
		$this->add_script( $this->get_view( $name, $args, false, false ), $priority );
	}

	/**
	 * @param string $name
	 * @param array $args
	 * @param int $priority
	 */
	public function add_style_view( $name, array $args = [], $priority = 10 ) {
		$this->add_style( $this->get_view( $name, $args, false, false ), $priority );
	}

	/**
	 * @param string $value
	 * @param bool $translate
	 * @param bool $echo
	 * @param bool $escape
	 * @param array $args
	 *
	 * @return string
	 */
	public function h( $value, $translate = false, $echo = true, $escape = true, ...$args ) {
		if ( $translate ) {
			$value = $this->app->translate( $value );
		}
		if ( ! empty( $args ) ) {
			$value = sprintf( $value, ...$args );
		}
		if ( $escape ) {
			$value = esc_html( $value );
			$value = nl2br( $value );
		}
		if ( $echo ) {
			echo $value;
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function json( $value, $echo = true ) {
		return $this->h( json_encode( $value ), false, $echo, false );
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function translate( $value ) {
		return $this->app->translate( $value );
	}

	/**
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function id( $echo = true ) {
		return $this->h( $this->app->slug_name, false, $echo );
	}

	/**
	 * @param array $data
	 * @param bool $echo
	 *
	 * @return int
	 */
	public function n( array $data, $echo = true ) {
		$count = count( $data );
		if ( $echo ) {
			echo $count;
		}

		return $count;
	}

	/**
	 * @param string $url
	 * @param string $contents
	 * @param bool $translate
	 * @param bool $new_tab
	 * @param array $args
	 * @param bool $echo
	 * @param bool $escape
	 *
	 * @return string
	 */
	public function url( $url, $contents, $translate = false, $new_tab = false, array $args = [], $echo = true, $escape = true ) {
		$overwrite = [
			'href'     => $url,
			'contents' => $this->h( $contents, $translate, false, $escape ),
		];
		if ( $new_tab ) {
			$overwrite['target'] = '_blank';
		}

		return $this->get_view( 'include/url', array_replace_recursive( $args, $overwrite ), $echo, true, true );
	}

	/**
	 * @param bool $append_version
	 * @param string $q
	 *
	 * @return string
	 */
	private function get_assets_version( $append_version, $q = 'v' ) {
		if ( ! $append_version ) {
			return '';
		}
		$append = trim( $this->apply_filters( 'assets_version' ) );
		if ( $append !== '' ) {
			if ( $q ) {
				return '?' . $q . '=' . $append;
			}

			return '?' . $append;
		}

		return '';
	}

	/**
	 * @param bool $allow_multiple
	 *
	 * @return array
	 */
	private function get_check_assets_dirs( $allow_multiple = false ) {
		$dirs = [];
		if ( $this->app->is_theme ) {
			if ( $allow_multiple ) {
				$dirs[ $this->app->define->lib_assets_dir ]    = $this->app->define->lib_assets_url;
				$dirs[ $this->app->define->plugin_assets_dir ] = $this->app->define->plugin_assets_url;
				! empty( $this->app->define->child_theme_assets_dir ) and $dirs[ $this->app->define->child_theme_assets_dir ] = $this->app->define->child_theme_assets_url;
			} else {
				! empty( $this->app->define->child_theme_assets_dir ) and $dirs[ $this->app->define->child_theme_assets_dir ] = $this->app->define->child_theme_assets_url;
				$dirs[ $this->app->define->plugin_assets_dir ] = $this->app->define->plugin_assets_url;
				$dirs[ $this->app->define->lib_assets_dir ]    = $this->app->define->lib_assets_url;
			}
		} else {
			$dirs[ $this->app->define->plugin_assets_dir ] = $this->app->define->plugin_assets_url;
			$dirs[ $this->app->define->lib_assets_dir ]    = $this->app->define->lib_assets_url;
		}

		return $this->apply_filters( 'check_assets_dirs', $dirs );
	}

	/**
	 * @param string $path
	 * @param string $default
	 * @param bool $url
	 * @param bool $append_version
	 *
	 * @return string
	 */
	private function get_assets( $path, $default = '', $url = false, $append_version = true ) {
		if ( empty( $path ) ) {
			return '';
		}

		$path = trim( $path );
		$path = trim( $path, '/' . DS );
		$path = str_replace( '/', DS, $path );

		foreach ( $this->get_check_assets_dirs() as $_dir => $_url ) {
			$_dir = rtrim( $_dir, DS . '/' );
			if ( file_exists( $_dir . DS . $path ) && is_file( $_dir . DS . $path ) ) {
				if ( $url ) {
					return rtrim( $_url, '/' ) . '/' . str_replace( DS, '/', $path ) . $this->apply_filters( 'get_assets_version', $this->get_assets_version( $append_version ), $append_version );
				}

				return $_dir . DS . $path;
			}
		}
		if ( empty( $default ) ) {
			return '';
		}

		return $this->get_assets( $default, '', $url, false );
	}

	/**
	 * @param string $path
	 * @param string $default
	 * @param bool $append_version
	 *
	 * @return string
	 */
	public function get_assets_url( $path, $default = '', $append_version = true ) {
		return $this->get_assets( $path, $default, true, $append_version );
	}

	/**
	 * @param string $path
	 * @param string $default
	 *
	 * @return string
	 */
	protected function get_assets_path( $path, $default = '' ) {
		return $this->get_assets( $path, $default );
	}

	/**
	 * @param string $path
	 * @param string $default
	 * @param bool $append_version
	 *
	 * @return string
	 */
	public function get_img_url( $path, $default = 'img/no_img.png', $append_version = true ) {
		return empty( $path ) ? '' : $this->get_assets_url( 'img/' . $path, $default, $append_version );
	}

	/**
	 * @param string $path
	 * @param string $default
	 *
	 * @return string
	 */
	protected function get_css_path( $path, $default = '' ) {
		return empty( $path ) ? '' : $this->get_assets_path( 'css/' . $path, $default );
	}

	/**
	 * @param string $path
	 * @param string $default
	 *
	 * @return string
	 */
	protected function get_js_path( $path, $default = '' ) {
		return empty ( $path ) ? '' : $this->get_assets_path( 'js/' . $path, $default );
	}

	/**
	 * @param string $url
	 * @param string $view
	 * @param array $args
	 * @param string $field
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function assets( $url, $view, array $args, $field, $echo = true ) {
		return $this->get_view( $view, array_merge( $args, [
			$field => $url,
		] ), $echo, true, true );
	}

	/**
	 * @param string $path
	 * @param array $args
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function img( $path, array $args = [], $echo = true ) {
		return $this->assets( $this->get_img_url( $path ), 'include/img', $args, 'src', $echo );
	}

	/**
	 * @param array $args
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function loading( array $args = [], $echo = true ) {
		return $this->img( 'loading.gif', $args, $echo );
	}

	/**
	 * @param array $args
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function no_img( array $args = [], $echo = true ) {
		return $this->img( 'no_img.png', $args, $echo );
	}

	/**
	 * @param string $path
	 * @param int $priority
	 *
	 * @return bool
	 */
	public function css( $path, $priority = 10 ) {
		$css = $this->get_css_path( $path );
		if ( ! empty( $css ) ) {
			$this->app->minify->register_css_file( $css, $priority );

			return true;
		}

		return false;
	}

	/**
	 * @param string $path
	 * @param int $priority
	 *
	 * @return bool
	 */
	public function js( $path, $priority = 10 ) {
		$js = $this->get_js_path( $path );
		if ( ! empty( $js ) ) {
			$this->app->minify->register_js_file( $js, $priority );

			return true;
		}

		return false;
	}

	/**
	 * @param string $handle
	 * @param string $file
	 * @param array $depends
	 * @param string|bool|null $ver
	 * @param string $media
	 * @param string $dir
	 *
	 * @return bool
	 */
	public function enqueue_style( $handle, $file, array $depends = [], $ver = false, $media = 'all', $dir = 'css' ) {
		$path    = $dir . DS . $file;
		$result  = false;
		$_handle = $handle;
		$index   = 0;
		foreach ( $this->get_check_assets_dirs( true ) as $_dir => $_url ) {
			$_dir = rtrim( $_dir, DS . '/' );
			if ( file_exists( $_dir . DS . $path ) && is_file( $_dir . DS . $path ) ) {
				wp_enqueue_style( $handle, $_url . '/' . $dir . '/' . $file, $depends, $ver, $media );

				if ( ! $this->app->is_theme ) {
					return true;
				}
				$result = true;
				$handle = "{$_handle}-{$index}";
				$index ++;
			}
		}

		return $result;
	}

	/**
	 * @param string $handle
	 * @param string $file
	 * @param array $depends
	 * @param string|bool|null $ver
	 * @param bool $in_footer
	 * @param string $dir
	 *
	 * @return bool
	 */
	public function enqueue_script( $handle, $file, array $depends = [], $ver = false, $in_footer = true, $dir = 'js' ) {
		$path    = $dir . DS . $file;
		$result  = false;
		$_handle = $handle;
		$index   = 0;
		foreach ( $this->get_check_assets_dirs( true ) as $_dir => $_url ) {
			$_dir = rtrim( $_dir, DS . '/' );
			if ( file_exists( $_dir . DS . $path ) && is_file( $_dir . DS . $path ) ) {
				wp_enqueue_script( $handle, $_url . '/' . $dir . '/' . $file, $depends, $ver, $in_footer );

				if ( ! $this->app->is_theme ) {
					return true;
				}
				$result = true;
				$handle = "{$_handle}-{$index}";
				$index ++;
			}
		}

		return $result;
	}

	/**
	 * @param string $handle
	 * @param string $name
	 * @param array $data
	 *
	 * @return bool
	 */
	public function localize_script( $handle, $name, array $data ) {
		return wp_localize_script( $handle, $name, $data );
	}

	/**
	 * setup modal
	 */
	public function setup_modal() {
		$this->add_script_view( 'include/script/modal', [], 1 );
		$this->add_style_view( 'include/style/modal', [], 1 );
	}

	/**
	 * setup color picker
	 */
	public function setup_color_picker() {
		wp_enqueue_script( 'wp-color-picker' );
		$this->add_script_view( 'include/script/color', [], 1 );
	}

	/**
	 * @return string
	 */
	public function get_color_picker_class() {
		return $this->get_slug( 'color_picker_class', '-color_picker' );
	}

	/**
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function modal_class( $echo = true ) {
		return $this->h( $this->get_slug( 'modal_class', '_modal' ), false, $echo );
	}

	/**
	 * @param string $handle
	 */
	public function set_script_translations( $handle ) {
		if ( $this->_set_script_translations ) {
			return;
		}
		$this->_set_script_translations = true;
		$text_domain                    = $this->app->get_text_domain();
		if ( empty( $text_domain ) ) {
			return;
		}

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( $handle, $text_domain );
		} elseif ( function_exists( 'wp_get_jed_locale_data' ) || function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$json = function_exists( 'wp_get_jed_locale_data' ) ? wp_get_jed_locale_data( $text_domain ) : gutenberg_get_jed_locale_data( $text_domain );
			wp_add_inline_script(
				'wp-i18n',
				sprintf( 'wp.i18n.setLocaleData(  %s, "%s" );', wp_json_encode( $json ), $text_domain ),
				'after'
			);
		}
	}

	/**
	 * @param string $type
	 *
	 * @return string
	 */
	public function get_form_by_type( $type ) {
		switch ( $this->app->utility->parse_db_type( $type, true ) ) {
			case 'int':
				return 'number';
			case 'bool':
				return 'checkbox';
			case 'number';
			case 'float';
				return 'text';
			case 'text';
				return 'textarea';
		}

		return 'text';
	}
}

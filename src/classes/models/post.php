<?php
/**
 * WP_Framework Classes Models Post
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
 * Class Post
 * @package WP_Framework\Classes\Models
 */
class Post implements \WP_Framework\Interfaces\Singleton, \WP_Framework\Interfaces\Hook, \WP_Framework\Interfaces\Uninstall {

	use \WP_Framework\Traits\Singleton, \WP_Framework\Traits\Hook, \WP_Framework\Traits\Uninstall;

	/**
	 * @return string
	 */
	private function get_post_prefix() {
		return $this->get_slug( 'post_prefix', '_post' ) . '-';
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	public function get_meta_key( $key ) {
		return $this->get_post_prefix() . $key;
	}

	/**
	 * @param bool $check_query
	 *
	 * @return int
	 */
	public function get_post_id( $check_query = false ) {
		global $post, $wp_query;
		if ( ! isset( $post ) ) {
			if ( $check_query && isset( $wp_query, $wp_query->query_vars['p'] ) ) {
				$post_id = $wp_query->query_vars['p'];
			} else {
				$post_id = 0;
			}
		} else {
			$post_id = $post->ID;
		}

		return $post_id;
	}

	/**
	 * @param string $key
	 * @param int|null $post_id
	 * @param bool $single
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $key, $post_id = null, $single = true, $default = '' ) {
		if ( ! isset( $post_id ) ) {
			$post_id = $this->get_post_id( true );
		}
		if ( $post_id <= 0 ) {
			return $this->apply_filters( 'get_post_meta', $default, $key, $post_id, $single, $default, $this->get_post_prefix() );
		}

		return $this->apply_filters( 'get_post_meta', get_post_meta( $post_id, $this->get_meta_key( $key ), $single ), $key, $post_id, $single, $default, $this->get_post_prefix() );
	}

	/**
	 * @param int $post_id
	 * @param string $key
	 * @param mixed $value
	 * @param bool $add
	 * @param bool $unique
	 *
	 * @return bool|int
	 */
	public function set( $post_id, $key, $value, $add = false, $unique = false ) {
		if ( $post_id <= 0 ) {
			return false;
		}

		if ( ! $add ) {
			return update_post_meta( $post_id, $this->get_meta_key( $key ), $value );
		}

		if ( $unique ) {
			$values = $this->get( $key, $post_id, false, [] );
			if ( in_array( $value, $values ) ) {
				return false;
			}
		}

		return add_post_meta( $post_id, $this->get_meta_key( $key ), $value );
	}

	/**
	 * @param int $post_id
	 * @param string $key
	 * @param mixed $meta_value
	 *
	 * @return bool
	 */
	public function delete( $post_id, $key, $meta_value = '' ) {
		if ( $post_id <= 0 ) {
			return false;
		}

		return delete_post_meta( $post_id, $this->get_meta_key( $key ), $meta_value );
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function set_all( $key, $value ) {
		global $wpdb;
		$query = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE meta_key LIKE %s", $value, $this->get_meta_key( $key ) );
		$wpdb->query( $query );
	}

	/**
	 * @param string $key
	 */
	public function delete_all( $key ) {
		global $wpdb;
		$query = $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE %s", $this->get_meta_key( $key ) );
		$wpdb->query( $query );
	}

	/**
	 * @param string $key
	 * @param string $value
	 *
	 * @return bool
	 */
	public function delete_matched( $key, $value ) {
		$post_ids = $this->find( $key, $value );
		if ( empty( $post_ids ) ) {
			return true;
		}
		foreach ( $post_ids as $post_id ) {
			$this->delete( $key, $post_id );
		}

		return true;
	}

	/**
	 * @param string $key
	 * @param string $value
	 *
	 * @return array
	 */
	public function find( $key, $value ) {
		global $wpdb;
		$query   = <<< SQL
			SELECT * FROM {$wpdb->postmeta}
			WHERE meta_key LIKE %s
			AND   meta_value LIKE %s
SQL;
		$results = $wpdb->get_results( $wpdb->prepare( $query, $this->get_meta_key( $key ), $value ) );

		return $this->apply_filters( 'find_post_meta', $this->app->utility->array_pluck( $results, 'post_id' ), $key, $value );
	}

	/**
	 * @param string $key
	 * @param string $value
	 *
	 * @return false|int
	 */
	public function first( $key, $value ) {
		$post_ids = $this->find( $key, $value );
		if ( empty( $post_ids ) ) {
			return false;
		}

		return reset( $post_ids );
	}

	/**
	 * @param string $key
	 *
	 * @return array
	 */
	public function get_meta_post_ids( $key ) {
		global $wpdb;
		$query   = <<< SQL
		SELECT post_id FROM {$wpdb->postmeta}
		WHERE meta_key LIKE %s
SQL;
		$results = $wpdb->get_results( $wpdb->prepare( $query, $this->get_meta_key( $key ) ) );

		return $this->apply_filters( 'get_meta_post_ids', $this->app->utility->array_pluck( $results, 'post_id' ), $key );
	}

	/**
	 * @param array|string $tags
	 *
	 * @return bool
	 */
	public function has_shortcode( $tags ) {
		if ( empty( $tags ) ) {
			return false;
		}

		$tagnames = $this->get_tagnames();
		if ( empty( $tagnames ) ) {
			return false;
		}
		! is_array( $tags ) and $tags = [ $tags ];

		return ! empty( array_intersect( $tags, $tagnames ) );
	}

	/**
	 * @return array|false
	 */
	private function get_tagnames() {
		if ( ! is_singular() ) {
			return false;
		}

		global $shortcode_tags;
		if ( empty( $shortcode_tags ) || ! is_array( $shortcode_tags ) ) {
			return false;
		}

		$post    = get_post();
		$content = $post->post_content;
		if ( false === strpos( $content, '[' ) ) {
			return false;
		}

		// Find all registered tag names in $content.
		preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );

		return array_intersect( array_keys( $shortcode_tags ), $matches[1] );
	}

	/**
	 * @return bool
	 */
	public function is_valid_tinymce_color_picker() {
		global $wp_version;

		return version_compare( $wp_version, '4.0', '>=' );
	}

	/**
	 * @return bool
	 */
	public function is_block_editor() {
		if ( ! is_admin() ) {
			return false;
		}
		$current_screen = get_current_screen();

		return ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) || ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() );
	}

	/**
	 * uninstall
	 */
	public function uninstall() {
		global $wpdb;
		$query = $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE %s", $this->get_post_prefix() . '%' );
		$wpdb->query( $query );
	}

	/**
	 * @return int
	 */
	public function get_uninstall_priority() {
		return 100;
	}
}

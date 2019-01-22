<?php
/**
 * WP_Framework Views Include Attributes
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	return;
}
/** @var \WP_Framework\Traits\Presenter $instance */
/** @var array $attributes */
?>
<?php if ( ! empty( $attributes ) && is_array( $attributes ) ): ?>
	<?php foreach ( $attributes as $k => $v ): ?>
		<?php $instance->h( $k ); ?>="<?php $instance->h( $v, ! empty( $translate ) && is_array( $translate ) && in_array( $k, $translate ) ); ?>"
	<?php endforeach; ?>
<?php endif; ?>

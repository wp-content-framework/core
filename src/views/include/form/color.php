<?php
/**
 * WP_Framework Views Include Form Color picker
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
/** @var array $args */
$args['class'] .= ' ' . $instance->get_color_picker_class();
?>
<?php if ( isset( $label ) ): ?>
    <label>
		<?php $instance->h( $label, true ); ?>
		<?php $instance->form( 'input/text', $args ); ?>
    </label>
<?php else: ?>
	<?php $instance->form( 'input/text', $args ); ?>
<?php endif; ?>
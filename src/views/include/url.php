<?php
/**
 * WP_Framework_Core Views Include Img
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
/** @var \WP_Framework_Presenter\Interfaces\Presenter $instance */
/** @var array $args */
/** @var string $id */
/** @var string $class */
/** @var string $href */
/** @var string $target */
/** @var string $contents */
/** @var array $attributes */
empty( $attributes ) and $attributes = [];
isset( $id ) and $attributes['id'] = $id;
isset( $class ) and $attributes['class'] = $class;
$attributes['href'] = $href;
isset( $target ) and $attributes['target'] = $target;
! isset( $contents ) and $contents = '';
?>
<a <?php $instance->get_view( 'include/attributes', array_merge( $args, [ 'attributes' => $attributes ] ), true ); ?> >
	<?php echo $contents ?>
</a>
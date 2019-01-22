<?php
/**
 * WP_Framework Views Admin Include Custom Post Text
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
/** @var array $data */
/** @var array $column */
/** @var string $name */
/** @var string $prefix */
$attr = $instance->app->utility->array_get( $column, 'attributes', [] );
if ( isset( $column['length'] ) ) {
	$attr['maxlength'] = $column['length'];
}
$attr['placeholder'] = $instance->app->utility->array_get( $column, 'default', '' );
?>
<?php $instance->form( 'input/text', [
	'name'       => $prefix . $name,
	'id'         => $prefix . $name,
	'value'      => $instance->old( $prefix . $name, $data, $name ),
	'attributes' => $attr,
], $instance->app->utility->array_get( $column, 'args', [] ) ); ?>

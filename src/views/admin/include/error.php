<?php
/**
 * WP_Framework Views Admin Include Error
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
/** @var string $message */
?>
<div class="wrap cf-wrap">
    <div class="icon32 icon32-error"><br/></div>
    <h2>Error</h2>
    <div class="error">
        <p>
			<?php $instance->h( $message, true ); ?>
        </p>
    </div>
</div>
<?php
/**
 * WP_Framework_Core Views Admin Include Exception
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
/** @var \Exception $e */
?>
<div class="wrap cf-wrap">
    <div class="icon32 icon32-error"><br/></div>
    <h2>Error</h2>
    <div class="error">
        <h3>
			<?php $instance->h( $e->getMessage() ); ?>
        </h3>
        <p>
			<?php echo nl2br( $e->getTraceAsString() ); ?>
        </p>
    </div>
</div>
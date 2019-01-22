<?php
/**
 * WP_Framework Crons Base
 *
 * @version 0.0.1
 * @author technote-space
 * @since 0.0.1
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework\Classes\Crons;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Base
 * @package WP_Framework\Classes\Crons
 */
abstract class Base implements \WP_Framework\Interfaces\Cron {

	use \WP_Framework\Traits\Cron;

}

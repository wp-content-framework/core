<?php
/**
 * WP_Framework Classes Models Device
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
 * Class Device
 * @package WP_Framework\Classes\Models
 */
class Device implements \WP_Framework\Interfaces\Singleton, \WP_Framework\Interfaces\Hook {

	use \WP_Framework\Traits\Singleton, \WP_Framework\Traits\Hook;

	/**
	 * @var bool $_is_robot
	 */
	private $_is_robot = null;

	/**
	 * @var \Mobile_Detect $_mobile_detect
	 */
	private $_mobile_detect;

	/**
	 * @return bool
	 */
	protected static function is_shared_class() {
		return true;
	}

	/**
	 * initialize
	 */
	protected function initialize() {
		$this->_mobile_detect = new \Mobile_Detect();
	}

	/**
	 * @param bool $cache
	 *
	 * @return bool
	 */
	public function is_robot( $cache = true ) {
		if ( $cache && isset( $this->_is_robot ) ) {
			return $this->_is_robot;
		}

		$this->_is_robot = $this->apply_filters( 'pre_check_bot', null );
		if ( is_bool( $this->_is_robot ) ) {
			return $this->_is_robot;
		}

		$bot_list = explode( ',', $this->apply_filters( 'bot_list', implode( ',', [
			'facebookexternalhit',
			'Googlebot',
			'Baiduspider',
			'bingbot',
			'Yeti',
			'NaverBot',
			'Yahoo! Slurp',
			'Y!J-BRI',
			'Y!J-BRJ/YATS crawler',
			'Tumblr',
			//		'livedoor',
			//		'Hatena',
			'Twitterbot',
			'Page Speed',
			'Google Web Preview',
			'msnbot/',
			'proodleBot',
			'psbot/',
			'ScSpider/',
			'TutorGigBot/',
			'YottaShopping_Bot/',
			'Faxobot/',
			'Gigabot/',
			'MJ12bot/',
			'Ask Jeeves/Teoma; ',
		] ) ) );

		$this->_is_robot = false;
		$ua              = $this->app->input->user_agent();
		foreach ( $bot_list as $value ) {
			$value = trim( $value );
			if ( preg_match( '/' . str_replace( '/', '\\/', $value ) . '/i', $ua ) ) {
				$this->_is_robot = true;
				break;
			}
		}

		return $this->_is_robot;
	}

	/**
	 * @param null|string $ua
	 *
	 * @return bool
	 */
	public function is_tablet( $ua = null ) {
		return $this->_mobile_detect->isTablet( $ua );
	}

	/**
	 * @param null|string $ua
	 *
	 * @return bool
	 */
	public function is_mobile( $ua = null ) {
		return $this->_mobile_detect->isMobile( $ua );
	}

	/**
	 * @return \Mobile_Detect
	 */
	public function get_mobile_detect() {
		return $this->_mobile_detect;
	}
}

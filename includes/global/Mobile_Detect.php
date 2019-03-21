<?php
/**
 * Mobile Detect Library
 *
 * @deprecated 1.6.4
 *
 * @package Envira Gallery
 */

/**
 * Mobile Detect Class.
 */
class Mobile_Detect {

	/**
	 * Replacement function for Legecy Class
	 *
	 * @return boolean
	 */
	public function isMobile(){ // @codingStandardsIgnoreLine

		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {

			return preg_match( '/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i', sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) );

		}

		return false;
	}
}

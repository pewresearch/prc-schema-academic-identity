<?php
/**
 * Plugin activator.
 *
 * @package PRC\Platform\Academic_Identity
 */

namespace PRC\Platform\Academic_Identity;

use DEFAULT_TECHNICAL_CONTACT;

/**
 * Plugin activator.
 */
class Plugin_Activator {

	/**
	 * Activate the plugin.
	 */
	public static function activate() {
		flush_rewrite_rules();

		wp_mail(
			DEFAULT_TECHNICAL_CONTACT,
			'PRC Academic Identity Activated',
			'The PRC Academic Identity plugin has been activated on ' . get_site_url()
		);
	}
}

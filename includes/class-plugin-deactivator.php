<?php
/**
 * Plugin deactivator.
 *
 * @package PRC\Platform\Academic_Identity
 */

namespace PRC\Platform\Academic_Identity;

use DEFAULT_TECHNICAL_CONTACT;

/**
 * Plugin deactivator.
 */
class Plugin_Deactivator {

	/**
	 * Deactivate the plugin.
	 */
	public static function deactivate() {
		flush_rewrite_rules();

		wp_mail(
			DEFAULT_TECHNICAL_CONTACT,
			'PRC Academic Identity Deactivated',
			'The PRC Academic Identity plugin has been deactivated on ' . get_site_url()
		);
	}
}

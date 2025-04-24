<?php
/**
 * PRC Academic Identity
 *
 * @package           PRC_ACADEMIC_IDENTITY
 * @author            Seth Rubenstein
 * @copyright         2024 Pew Research Center
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       PRC Academic Identity
 * Plugin URI:        https://github.com/pewresearch/prc-academic-identity
 * Description:       A plugin for PRC Platform that manages academic identity metadata like DOI, ORCID, etc. This plugin integrates with multiple PRC Platform plugins, including Staff Bylines, Post Like Types, and more.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      8.2
 * Author:            Seth Rubenstein
 * Author URI:        https://pewresearch.org
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       prc-academic-identity
 * Requires Plugins:  prc-platform-core, prc-staff-bylines
 */

namespace PRC\Platform\Academic_Identity;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PRC_ACADEMIC_IDENTITY_FILE', __FILE__ );
define( 'PRC_ACADEMIC_IDENTITY_DIR', __DIR__ );
define( 'PRC_ACADEMIC_IDENTITY_BLOCKS_DIR', PRC_ACADEMIC_IDENTITY_DIR . '/blocks' );
define( 'PRC_ACADEMIC_IDENTITY_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-activator.php
 */
function activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-activator.php';
	Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-deactivator.php
 */
function deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-deactivator.php';
	Plugin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, '\PRC\Platform\Academic_Identity\activate' );
register_deactivation_hook( __FILE__, '\PRC\Platform\Academic_Identity\deactivate' );

/**
 * Helper utilities
 */
require plugin_dir_path( __FILE__ ) . 'includes/utils.php';

/**
 * The core plugin class that is used to define the hooks that initialize the various components.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_prc_academic_identity() {
	$plugin = new Plugin();
	$plugin->run();
}
run_prc_academic_identity();

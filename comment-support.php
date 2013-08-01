<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that also follow
 * WordPress coding standards and PHP best practices.
 *
 * @package   Comment_Support
 * @author    Brandon Lavigne <brandon.lavigne@gmail.com>
 * @license   GPL-2.0+
 * @link      http://caavadesign.com
 * @copyright 2013 Caava Design
 *
 * @wordpress-plugin
 * Plugin Name: Comment Support
 * Plugin URI:  http://caavadesign.com
 * Description: Adds Support Post type where users can post file attachments
 * Version:     1.0.0
 * Author:      Brandon Lavigne
 * Author URI:  http://caavadesign.com
 * Text Domain: comment_support
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( plugin_dir_path( __FILE__ ) . 'class-comment-support.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'Comment_Support', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Comment_Support', 'deactivate' ) );

Comment_Support::get_instance();
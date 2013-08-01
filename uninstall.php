<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Comment_Support
 * @author    Brandon Lavigne <brandon.lavigne@gmail.com>
 * @license   GPL-2.0+
 * @link      http://caavadesign.com
 * @copyright 2013 Caava Design
 */

// If uninstall, not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// TODO: Define uninstall functionality here
<?php

/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that also follow
 * WordPress coding standards and PHP best practices.
 *
 * @package   Update Notifier Demo
 * @author    Ivan Batić <ivan.batic@live.com>
 * @link      http://github.com/ivanbatic/wp-update-notifier-demo
 *
 * @wordpress-plugin
 * Plugin Name: Update Notifier Demo
 * Plugin URI:  http://github.com/ivanbatic/wp-update-notifier-demo
 * Description: Sends an email periodically with information about whether updates are available
 * Version:     1.0.0
 * Author:      Ivan Batić <ivan.batic@live.com>
 * Domain Path: /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

require_once( plugin_dir_path(__FILE__) . 'class-update-notifier-demo.php' );
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once(ABSPATH . 'wp-admin/includes/screen.php');

// Register hooks that are fired when the plugin is activated or deactivated.
// When the plugin is deleted, the uninstall.php file is loaded.
add_filter('cron_schedules', function($schedules) {
        return $schedules += array(
            'weekly'  => array(
                'interval' => WEEK_IN_SECONDS,
                'display'  => __('Once Weekly')
            ),
            'monthly' => array(
                'interval' => DAY_IN_SECONDS * 30,
                'display'  => __('Once Monthly')
            )
        );
    });

register_activation_hook(__FILE__, array('update_notifier_demo', 'activate'));
register_deactivation_hook(__FILE__, array('update_notifier_demo', 'deactivate'));

add_action('plugins_loaded', array('update_notifier_demo', 'get_instance'));

add_action('current_screen', function() {
        $screen = get_current_screen();
        if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'upgrade-core') && strpos($screen->base, 'update-core') === 0) {
            // Set it because of check in update-core.php:398, 
            // which won't allow themes to be installed without a post request
            $_POST['checked'] = true;
            // Needed for core upgrade
            $_POST['upgrade'] = true;
            $_POST['version'] = $_GET['version'];
            $_POST['locale'] = $_GET['locale'];
        }
    });
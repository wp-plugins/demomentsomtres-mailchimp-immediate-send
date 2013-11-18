<?php
/*
Plugin Name: DeMomentSomTres MailChimp Immediate Send
Plugin URI: http://demomentsomtres.com/english/wordpress-plugin-mailchimp-immediate-send/
Description: Immediate notifications via Mailchimp
Version: 1.0.1
Author: Marc Queralt
Author URI: http://demomentsomtres.com
*/

define('DMST_MC_IMMEDIATE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DMST_MC_IMMEDIATE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DMST_MC_IMMEDIATE_TEXT_DOMAIN', 'DeMomentSomTres-MailChimp-Immediate');
define('DMST_MC_IMMEDIATE_OPTIONS', 'dmst_mc_immediate_options');
define('DMST_MC_IMMEDIATE_META_LOG','dms3_mc_imm_log'); /** the meta field containing the log */
define('DMST_MC_IMMEDIATE_STDTXT','std_content00'); /** the locator to be updated on the template */

require_once DMST_MC_IMMEDIATE_PLUGIN_PATH . 'functions.php';
require_once DMST_MC_IMMEDIATE_PLUGIN_PATH . 'admin-helper.php';
require_once DMST_MC_IMMEDIATE_PLUGIN_PATH . 'admin.php';

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
    exit;
}

load_plugin_textdomain(DMST_MC_IMMEDIATE_TEXT_DOMAIN, false, DMST_MC_IMMEDIATE_PLUGIN_URL . '/languages');

// Get our MailChimp API class in scope
if (!class_exists('DeMomentSomTresMailChimp')) {
	require_once(DMST_MC_IMMEDIATE_PLUGIN_PATH.'mailchimp-api/MailChimp.class.php');
}

if (dmst_mc_immediate_check_requirements(false)):
    //add_action('transition_post_status', 'dmst_mc_immediate_content_published',99);
    dmst_mc_immediate_init();
    add_action('publish_post', 'dmst_mc_immediate_content_published');
    add_action('publish_page', 'dmst_mc_immediate_content_published');
endif;
?>

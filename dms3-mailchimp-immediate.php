<?php

/*
  Plugin Name: DeMomentSomTres MailChimp Immediate
  Plugin URI: http://demomentsomtres.com/english/wordpress-plugins/mailchimp-immediate-send/
  Description: Immediate notifications via Mailchimp
  Version: 2.3
  Author: Marc Queralt
  Author URI: http://demomentsomtres.com
 */

define('DMST_MC_IMMEDIATE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DMST_MC_IMMEDIATE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DMST_MC_IMMEDIATE_LANG_DIR', dirname(plugin_basename(__FILE__)) . '/languages');
define('DMST_MC_IMMEDIATE_TEXT_DOMAIN', 'DeMomentSomTres-MailChimp-Immediate');
define('DMST_MC_IMMEDIATE_OPTIONS', 'dmst_mc_immediate_options');
define('DMST_MC_IMMEDIATE_META_LOG', 'dms3_mc_imm_log');/** the meta field containing the log */
define('DMST_MC_IMMEDIATE_STDTXT', 'std_content00');/** the locator to be updated on the template */
require_once DMST_MC_IMMEDIATE_PLUGIN_PATH . 'functions.php';
require_once DMST_MC_IMMEDIATE_PLUGIN_PATH . 'admin.php';

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
    exit;
}

if (!in_array('demomentsomtres-tools/demomentsomtres-tools.php', apply_filters('active_plugins', get_option('active_plugins')))):
    add_action('admin_notices', 'DMST_MC_messageNoTools');
else:
    require_once(ABSPATH . 'wp-content/plugins/demomentsomtres-tools/demomentsomtres-tools.php');
    require_once(ABSPATH . 'wp-content/plugins/demomentsomtres-tools/mailchimp/demomentsomtres-mailchimp.php');
    add_action('plugins_loaded', 'dmst_mc_immediate_plugin_init');

    if (dmst_mc_immediate_check_requirements(false)):
        dmst_mc_immediate_init();
        //add_action('publish_post', 'dmst_mc_immediate_content_published');
        //add_action('publish_page', 'dmst_mc_immediate_content_published');
        add_action('save_post', 'dmst_mc_immediate_sendIfRequired');
        add_action('add_meta_boxes', 'dmst_mc_immediate_add_metaboxes');
    endif;
endif;

function DMST_MC_messageNoTools() {
    ?>
    <div class="error">
        <p><?php _e('The DeMomentSomTres Mailchimp Immediate Send plugin requires the free DeMomentSomTres Tools plugin.', DMS3_WCDD_TEXT_DOMAIN); ?>
            <br/>
            <a href="http://demomentsomtres.com/english/wordpress-plugins/demomentsomtres-tools/?utm_source=web&utm_medium=wordpress&utm_campaign=adminnotice&utm_term=dms3MCImmediateSend" target="_blank"><?php _e('Download it here', DMS3_WCDD_TEXT_DOMAIN); ?></a>
        </p>
    </div>
    <?php
}
?>

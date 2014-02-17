<?php

/**
 * @since 1.0.3
 */
function dmst_mc_immediate_plugin_init() {
    load_plugin_textdomain(DMST_MC_IMMEDIATE_TEXT_DOMAIN, false, DMST_MC_IMMEDIATE_LANG_DIR);
}

/**
 * 
 * @return array
 * @since 1.0
 */
function dmst_mc_immediate_get_posttypes() {
    $result = get_post_types(array('public' => true), 'names');
    return $result;
}

/**
 * Init mailchim session
 * @since 1.0
 * @return DeMomentSomTresMailChimp|null
 */
function dmst_mc_immediate_init() {
    global $DeMomentSomTres_MC_IM_Session;

    if (!isset($DeMomentSomTres_MC_IM_Session)):
        $api = dmst_admin_helper_get_option(DMST_MC_IMMEDIATE_OPTIONS, 'API', '');
        if ($api == ''):
            return null;
        endif;
        $DeMomentSomTres_MC_IM_Session = new DeMomentSomTresMailChimp($api);
    else:
        return $DeMomentSomTres_MC_IM_Session;
    endif;
}

/**
 * 
 * @global array $dmst_mc_immediate_list_global
 * @return array the lists of MailChimp lists
 * @since 1.0
 */
function dmst_mc_immediate_lists() {
    global $dmst_mc_immediate_list_global;
    if (!isset($dmst_mc_immediate_list_global)):
        $result = array();
        $session = dmst_mc_immediate_init();
        if ($session):
            $temp = $session->call('lists/list');
            if (isset($temp['data'])):
                foreach ($temp['data'] as $t):
                    $result[] = array(
                        'id' => $t['id'],
                        'name' => $t['name']
                    );
                endforeach;
            endif;
        endif;
        $dmst_mc_immediate_list_global = $result;
    else:
        $result = $dmst_mc_immediate_list_global;
    endif;
    return $result;
}

/**
 * 
 * @global array $dmst_mc_immediate_template_global
 * @return array the list of MailChimp templates
 * @since 1.0
 */
function dmst_mc_immediate_templates() {
    global $dmst_mc_immediate_template_global;
    if (!isset($dmst_mc_immediate_template_global)):
        $result = array();
        $session = dmst_mc_immediate_init();
        if ($session):
            $temp = $session->call('templates/list');
            if (isset($temp['user'])):
                foreach ($temp['user'] as $t):
                    $result[] = array(
                        'id' => $t['id'],
                        'name' => $t['name']
                    );
                endforeach;
            endif;
        endif;
        $dmst_mc_immediate_template_global = $result;
    else:
        $result = $dmst_mc_immediate_template_global;
    endif;
    return $result;
}

/**
 * @since 1.0
 * @param boolean $showMessages
 * @return boolean The requirements are meet or not
 */
function dmst_mc_immediate_check_requirements($showMessages = true) {
    if (!function_exists('curl_init')):
        if ($showMessages):
            add_settings_error('', '', __('CURL not installed and required', DMST_MC_IMMEDIATE_TEXT_DOMAIN));
        endif;
        return false;
    endif;
    return true;
}

/**
 * @since 1.0
 * @return array
 */
function dmst_mc_immediate_getoption_posttypes() {
    return array_keys(dmst_admin_helper_get_option(DMST_MC_IMMEDIATE_OPTIONS, 'posttypes', array()));
}

/**
 * 
 * @param type $postID
 * @since 1.1
 */
function dmst_mc_immediate_sendIfRequired($postID) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $postID;
    $oldStatus = $_POST['hidden_post_status'];
    $resend = isset($_POST['dms3-mcimmediate-send']);
//    echo '<pre>';
//    print_r($resend);
//    echo '</pre>';
//    exit;
    $post = get_post($postID);
    if (($post->post_status == 'publish' && $oldStatus != $post->post_status) ||
            ($post->post_status == 'publish' && $resend)):
        $posttype = $post->post_type;
        $optionPostTypes = dmst_mc_immediate_getoption_posttypes();
        if (in_array($posttype, $optionPostTypes)):
            $taxonomies = dmst_mc_immediate_get_posttype_taxonomies($posttype);
            $optionTaxonomies = dmst_mc_immediate_getoption_posttype_taxonomies($posttype);
            $taxonomiesToSend = array_intersect($taxonomies, $optionTaxonomies);
            $oldlog = get_metadata($posttype, $postID, DMST_MC_IMMEDIATE_META_LOG, true);
            $log = '';
            $log .= date('Y/m/d H:i:s ') . __('Start to send', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n";
//        $log .= date('Y/m/d H:i:s ') . __('Available taxonomies', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n" . print_r($taxonomies, true) . "\n";
//        $log .= date('Y/m/d H:i:s ') . __('Option taxonomies', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n" . print_r($optionTaxonomies, true) . "\n";
//        $log .= date('Y/m/d H:i:s ') . __('Selected taxonomies are:', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n" . print_r($taxonomiesToSend, true) . "\n";
            foreach ($taxonomiesToSend as $taxonomy):
                $terms = wp_get_post_terms($postID, $taxonomy);
                $optionTerms = dmst_mc_immediate_getoption_posttype_taxonomy_terms($posttype, $taxonomy);
//            $log .= date('Y/m/d H:i:s ') . __('Available terms:', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n" . print_r($terms, true) . "\n";
//            $log .= date('Y/m/d H:i:s ') . __('Option terms:', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n" . print_r($optionTerms, true) . "\n";
                foreach ($terms as $term):
                    if (in_array($term->slug, $optionTerms)):
//                    $log .= date('Y/m/d H:i:s ') . __('Processing term:', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . $term->slug . "\n";
                        $listID = dmst_mc_immediate_getoption_listId($posttype, $taxonomy, $term->slug);
                        if ($listID):
                            $templateID = dmst_mc_immediate_getoption_templateId($posttype, $taxonomy, $term->slug);
                            $campaign = dmst_mc_immediate_campaign_create($listID, $post->post_content, $templateID);
                            if ($campaign):
                                $log .= date('Y/m/d H:i:s ') . __('Campaign dump', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n" . print_r($campaign, true) . "\n";
                                $cid = $campaign['id'];
                                $success = dmst_mc_immediate_campaign_send($cid);
                                if ($success):
                                    $log .= date('Y/m/d H:i:s ') . __('Campaign sent', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n";
                                endif;
                            else:
                                $log .= date('Y/m/d H:i:s ') . __('Error: Campaign not created.', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n";
                            endif;
                        else:
                            $log .= date('Y/m/d H:i:s ') . __('Error: List not found.', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n" . print_r($listID, true) . "\n";
                        endif;
                    endif;
                endforeach;
            endforeach;
            $log .= date('Y/m/d H:i:s ') . __('End to send', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n";
            update_metadata($posttype, $postID, DMST_MC_IMMEDIATE_META_LOG, $log . $oldlog);
        endif;
    endif;
}

/**
 * @since 1.0
 */
function dmst_mc_immediate_content_published($postID) {
    $post = get_post($postID);
    $posttype = $post->post_type;
    $optionPostTypes = dmst_mc_immediate_getoption_posttypes();
    if (in_array($posttype, $optionPostTypes)):
        $taxonomies = dmst_mc_immediate_get_posttype_taxonomies($posttype);
        $optionTaxonomies = dmst_mc_immediate_getoption_posttype_taxonomies($posttype);
        $taxonomiesToSend = array_intersect($taxonomies, $optionTaxonomies);
        $oldlog = get_metadata($posttype, $postID, DMST_MC_IMMEDIATE_META_LOG, true);
        $log = '';
        $log .= date('Y/m/d H:i:s ') . __('Start to send', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n";
//        $log .= date('Y/m/d H:i:s ') . __('Available taxonomies', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n" . print_r($taxonomies, true) . "\n";
//        $log .= date('Y/m/d H:i:s ') . __('Option taxonomies', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n" . print_r($optionTaxonomies, true) . "\n";
//        $log .= date('Y/m/d H:i:s ') . __('Selected taxonomies are:', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n" . print_r($taxonomiesToSend, true) . "\n";
        foreach ($taxonomiesToSend as $taxonomy):
            $terms = wp_get_post_terms($postID, $taxonomy);
            $optionTerms = dmst_mc_immediate_getoption_posttype_taxonomy_terms($posttype, $taxonomy);
//            $log .= date('Y/m/d H:i:s ') . __('Available terms:', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n" . print_r($terms, true) . "\n";
//            $log .= date('Y/m/d H:i:s ') . __('Option terms:', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n" . print_r($optionTerms, true) . "\n";
            foreach ($terms as $term):
                if (in_array($term->slug, $optionTerms)):
//                    $log .= date('Y/m/d H:i:s ') . __('Processing term:', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . $term->slug . "\n";
                    $listID = dmst_mc_immediate_getoption_listId($posttype, $taxonomy, $term->slug);
                    if ($listID):
                        $templateID = dmst_mc_immediate_getoption_templateId($posttype, $taxonomy, $term->slug);
                        $campaign = dmst_mc_immediate_campaign_create($listID, $post->post_content, $templateID);
                        if ($campaign):
                            $log .= date('Y/m/d H:i:s ') . __('Campaign dump', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n" . print_r($campaign, true) . "\n";
                            $cid = $campaign['id'];
                            $success = dmst_mc_immediate_campaign_send($cid);
                            if ($success):
                                $log .= date('Y/m/d H:i:s ') . __('Campaign sent', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n";
                            endif;
                        else:
                            $log .= date('Y/m/d H:i:s ') . __('Error: Campaign not created.', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n";
                        endif;
                    else:
                        $log .= date('Y/m/d H:i:s ') . __('Error: List not found.', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n" . print_r($listID, true) . "\n";
                    endif;
                endif;
            endforeach;
        endforeach;
        $log .= date('Y/m/d H:i:s ') . __('End to send', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "\n";
        update_metadata($posttype, $postID, DMST_MC_IMMEDIATE_META_LOG, $log . $oldlog);
    endif;
}

/**
 * @since 1.0
 * @param string $listID list ID in mailchimp
 * @param string $content to be post
 * @param string $templateID template ID in mailchimp
 * @return boolean/mixed false if error, see mailchimp api for structure
 */
function dmst_mc_immediate_campaign_create($listID, $content, $templateID = null) {
    $session = dmst_mc_immediate_init();
    $list = $session->call(
            'lists/list', array(
        'filters' => array(
            'list_id' => $listID
        )
            )
    );
    if ($list):
        if ($list['total'] > 0):
            $cname = $list['data'][0]['name'];
            $cfrom = $list['data'][0]['default_from_name'];
            $cemail = $list['data'][0]['default_from_email'];
            $csubject = $list['data'][0]['default_subject'];
            if ($templateID == ''):
                $content = array(
                    'html' => $content,
                    'generate_text' => true,
                );
            else:
                $content = array(
                    'generate_text' => true,
                    'sections' => array(
                        DMST_MC_IMMEDIATE_STDTXT => $content
                    )
                );
            endif;
            $cid = $session->call('campaigns/create', array(
                'type' => 'regular',
                'options' => array(
                    'list_id' => $listID,
                    'subject' => $csubject,
                    'from_email' => $cemail,
                    'from_name' => $cfrom,
                    'to_name' => $cname,
                    'template_id' => $templateID,
                    'title' => $cname . date(' Y/m/d H:i:s')
                ),
                'content' => $content
                    )
            );
            return $cid;
        else:
            return false;
        endif;
    else:
        return false;
    endif;
}

/**
 * @since 1.0
 * @param string $cid
 * @return boolean success or failure
 */
function dmst_mc_immediate_campaign_send($cid) {
    $session = dmst_mc_immediate_init();
    return $session->call('campaigns/send', array(
                'cid' => $cid
                    )
    );
}

/**
 * Get the taxonomies linked to a posttype
 * @param string $p the post type
 * @return array all taxonomies
 * @since 1.0
 */
function dmst_mc_immediate_get_posttype_taxonomies($p) {
    $taxs = get_taxonomies(
            array(
        'show_ui' => true
            ), 'objects'
    );
    $result = array();
    foreach ($taxs as $name => $tax):
        if (in_array($p, $tax->object_type)):
            $result[] = $name;
        endif;
    endforeach;
    return $result;
}

/**
 * Get the taxonomies of a post type that have to be sent
 * @param string $p the post type
 * @return array the taxonomies
 * @since 1.0
 */
function dmst_mc_immediate_getoption_posttype_taxonomies($p) {
    $t1 = dmst_admin_helper_get_option(DMST_MC_IMMEDIATE_OPTIONS, 'lists', array());
    $t = (isset($t1[$p])) ? $t1[$p] : array();
    $taxonomies = array_keys($t);
    return $taxonomies;
}

/**
 * Get the terms of a taxonomy and posttype that have to be sent
 * @param string $p posttype
 * @param string $t taxonomy
 * @return array the terms
 */
function dmst_mc_immediate_getoption_posttype_taxonomy_terms($p, $t) {
    $t1 = dmst_admin_helper_get_option(DMST_MC_IMMEDIATE_OPTIONS, 'lists', array());
    $t2 = (isset($t1[$p])) ? $t1[$p] : array();
    $terms = (isset($t2[$t])) ? $t2[$t] : array();
    return array_keys($terms);
}

/**
 * Get the listid linked to a term
 * @param string $p posttype
 * @param string $tx taxonomy
 * @param string $t term
 * @return string listId
 */
function dmst_mc_immediate_getoption_listId($p, $tx, $t) {
    $t1 = dmst_admin_helper_get_option(DMST_MC_IMMEDIATE_OPTIONS, 'lists', array());
    $t2 = (isset($t1[$p])) ? $t1[$p] : array();
    $terms = (isset($t2[$tx])) ? $t2[$tx] : array();
    $listid = (isset($terms[$t])) ? $terms[$t] : null;
    return $listid;
}

/**
 * Get the templateid linked to a term
 * @param string $p posttype
 * @param string $tx taxonomy
 * @param string $t term
 * @return string templateId
 */
function dmst_mc_immediate_getoption_templateId($p, $tx, $t) {
    $t1 = dmst_admin_helper_get_option(DMST_MC_IMMEDIATE_OPTIONS, 'templates', array());
    $t2 = (isset($t1[$p])) ? $t1[$p] : array();
    $terms = (isset($t2[$tx])) ? $t2[$tx] : array();
    $id = (isset($terms[$t])) ? $terms[$t] : null;
    return $id;
}

/**
 * @since 1.1
 */
function dmst_mc_immediate_add_metaboxes() {
    $posttypes = dmst_mc_immediate_getoption_posttypes();
    //echo '<pre>'.print_r($posttypes,true).'</pre>';exit;
    foreach ($posttypes as $posttype):
        add_meta_box('dms3-mcimmediate-resend', __('Send', DMST_MC_IMMEDIATE_TEXT_DOMAIN), 'dmst_mc_immediate_resend_metabox', $posttype, 'side', 'high');
    endforeach;
}

/*
 * @since 1.1
 */
function dmst_mc_immediate_resend_metabox($post) {
    if ($post->post_status == 'publish'):
        echo '<p>' . __('Check the field to force resend to mailchimp as a published content is updated.', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . '</p>';
        echo '<p><input name="dms3-mcimmediate-send" type="checkbox" ' . checked(false, true, false) . '/>' . __('Force send', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . '</p>';
    else:
        echo '<p>' . __('The content will be sent to mailchimp when published if linked to any of the active taxonomy terms', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . '</p>';
    endif;
//    echo '<pre>' . print_r($post, true) . '</pre>';
}

?>
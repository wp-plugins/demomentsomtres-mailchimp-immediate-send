<?php
/*
 * Settings and administration
 * @since 1.0
 */

add_action('admin_menu', 'dmst_mc_immediate_add_page');
add_action('admin_init', 'dmst_mc_immediate_admin_init');

/**
 * Add admin page
 * @since 1.0 
 */
function dmst_mc_immediate_add_page() {
    add_options_page(__('Mailchimp Immediate', DMST_MC_IMMEDIATE_TEXT_DOMAIN), __('Mailchimp Immediate Send', DMST_MC_IMMEDIATE_TEXT_DOMAIN), 'manage_options', 'dmst_mc_immediate', 'dmst_mc_immediate_option_page');
}

/**
 * Admin page contents
 * @since 1.0
 */
function dmst_mc_immediate_option_page() {
    ?>
    <div class="wrap" style="float:left;width:50%;">
        <?php screen_icon(); ?>
        <h2><?php _e('DeMomentSomTres - MailChimp Immediate Send', DMST_MC_IMMEDIATE_TEXT_DOMAIN); ?></h2>
        <form action="options.php" method="post">
            <?php settings_fields('dmst_mc_immediate_options'); ?>
            <?php do_settings_sections('dmst_mc_immediate'); ?>
            <br/>
            <input name="Submit" class="button button-primary" type="submit" value="<?php _e('Save Changes', DMST_MC_IMMEDIATE_TEXT_DOMAIN); ?>"/>
        </form>
    </div>
    <?php
    echo '<div style="background-color:#eee; width:45%;float:right;padding:10px;">';
    echo '<h3>' . __('Options', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . '</h3>' . '<pre style="font-size:0.8em;">';
    print_r(get_option(DMST_MC_IMMEDIATE_OPTIONS));
    echo '</pre>';
    echo '</div>';
}

/**
 * Admin page options
 * @since 1.0
 */
function dmst_mc_immediate_admin_init() {
    if (!dmst_mc_immediate_check_requirements())
        return null;

    register_setting('dmst_mc_immediate_options', 'dmst_mc_immediate_options', 'dmst_mc_immediate_validate_options');

    add_settings_section('dmst_mc_immediate_general', __('General Options', DMST_MC_IMMEDIATE_TEXT_DOMAIN), 'dmst_mc_immediate_section_general', 'dmst_mc_immediate');
    add_settings_section('dmst_mc_immediate_posttype_options', __('Post Types', DMST_MC_IMMEDIATE_TEXT_DOMAIN), 'dmst_mc_immediate_section_posttypes', 'dmst_mc_immediate');

    add_settings_field('dmst_mc_immediate_apikey', __('Mailchimp API Key', DMST_MC_IMMEDIATE_TEXT_DOMAIN), 'dmst_mc_immediate_field_apikey', 'dmst_mc_immediate', 'dmst_mc_immediate_general');

    dmst_mc_immediate_option_post_types();
}

/**
 * Validate admin options
 * @param array $input the options values as are entered into the forms
 * @return array the validate options
 * @since 1.0
 */
function dmst_mc_immediate_validate_options($input) {
    $lists = $input['lists'];
    $templates = $input['templates'];
    $newLists = array();
    foreach ($lists as $posttype => $taxonomies):
        $newPostTypes = array();
        foreach ($taxonomies as $taxonomy => $terms):
            $newTerms = array();
            foreach ($terms as $term => $content):
                if ('' != $content):
                    $newTerms[$term] = $content;
                endif;
            endforeach;
            if (count($newTerms) > 0):
                $newPostTypes[$taxonomy] = $newTerms;
            endif;
        endforeach;
        if (count($newPostTypes) > 0):
            $newLists[$posttype] = $newPostTypes;
        endif;
    endforeach;
        $newTemplates = array();
    foreach ($templates as $posttype => $taxonomies):
        $newPostTypes = array();
        foreach ($taxonomies as $taxonomy => $terms):
            $newTerms = array();
            foreach ($terms as $term => $content):
                if ('' != $content):
                    $newTerms[$term] = $content;
                endif;
            endforeach;
            if (count($newTerms) > 0):
                $newPostTypes[$taxonomy] = $newTerms;
            endif;
        endforeach;
        if (count($newPostTypes) > 0):
            $newTemplates[$posttype] = $newPostTypes;
        endif;
    endforeach;
    $input['lists'] = $newLists;
    $input['templates'] = $newTemplates;
    $input = array_map('dmst_admin_helper_esc_attr', $input);
    return $input;
}

/**
 * @since 1.0
 */
function dmst_mc_immediate_section_general() {
    
}

/**
 * @since 1.0
 */
function dmst_mc_immediate_section_posttypes() {
    echo '<p>'.__('This section shows only post types having any taxonomy with informed values.',DMST_MC_IMMEDIATE_TEXT_DOMAIN).'</p>';
    echo '<p>'.__('If the post type is not selected, NOTHING will be sent after publishing content of that post type.',DMST_MC_IMMEDIATE_TEXT_DOMAIN).'</p>';
}

/**
 * @since 1.0
 */
function dmst_mc_immediate_section_content() {
    
}

/**
 * @since 1.0
 */
function dmst_mc_immediate_field_apikey() {
    $name = 'API';
    $value = dmst_admin_helper_get_option(DMST_MC_IMMEDIATE_OPTIONS, $name);
    dmst_admin_helper_input(DMST_MC_IMMEDIATE_OPTIONS, $name, $value);
}

/**
 * @since 1.0
 */
function dmst_mc_immediate_option_post_types() {
    $lists = dmst_mc_immediate_lists();
    $post_types = dmst_mc_immediate_get_posttypes();
    foreach ($post_types as $p):
        $name = 'post-type-' . $p;
        $taxonomies = dmst_mc_immediate_get_posttype_taxonomies($p);
        if (count($taxonomies) > 0):
            add_settings_field('dmst_mc_immediate_' . $p, $p, 'dmst_mc_immediate_field_posttype', 'dmst_mc_immediate', 'dmst_mc_immediate_posttype_options', array('posttype' => $p));
            foreach ($taxonomies as $t):
                $tname = $t;
                $terms = get_terms(array($tname), array('hide_empty'=>false));
                if (count($terms) > 0):
                    add_settings_section('dmst_mc_immediate_' . $p . '_' . $tname, $p . ' ' . __('taxonomy', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . ': ' . $tname, 'dmst_mc_immediate_section_content', 'dmst_mc_immediate');
                    foreach ($terms as $term):
                        add_settings_field('dmst_mc_immediate_' . $p . '_' . $tname . '_' . $term->slug, __('List for', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . ' ' . $term->slug, 'dmst_mc_immediate_field_mclist', 'dmst_mc_immediate', 'dmst_mc_immediate_' . $p . '_' . $tname, array(
                            'posttype' => $p,
                            'taxonomy' => $tname,
                            'term' => $term->slug
                                )
                        );
                        add_settings_field('dmst_mc_immediate_' . $p . '_' . $tname . '_' . $term->slug . '_template', __('Template for', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . ' ' . $term->slug, 'dmst_mc_immediate_field_mctemplate', 'dmst_mc_immediate', 'dmst_mc_immediate_' . $p . '_' . $tname, array(
                            'posttype' => $p,
                            'taxonomy' => $tname,
                            'term' => $term->slug
                                )
                        );
                    endforeach;
                endif;
            endforeach;
        endif;
    endforeach;
}

/**
 * @since 1.0
 * @param array $args arguments 'posttype' sets the posttype to show
 */
function dmst_mc_immediate_field_posttype($args) {
    $name = $args['posttype'];
    $array = dmst_admin_helper_get_option(DMST_MC_IMMEDIATE_OPTIONS, 'posttypes', array());
    $value = (isset($array[$name])) ? $array[$name] : 'off';
    dmst_admin_helper_input(DMST_MC_IMMEDIATE_OPTIONS . '[posttypes]', $name, $value, 'checkbox', 'on');
}

/**
 * @since 1.0
 * @param array $args posttype, taxonomy and term must be set
 */
function dmst_mc_immediate_field_mclist($args) {
    $posttype = $args['posttype'];
    $taxonomy = $args['taxonomy'];
    $term = $args['term'];
    $lists = dmst_mc_immediate_lists();
    $options = dmst_admin_helper_get_option(DMST_MC_IMMEDIATE_OPTIONS, 'lists', array());
    $options = (isset($options[$posttype])) ? $options[$posttype] : array();
    $options = (isset($options[$taxonomy])) ? $options[$taxonomy] : array();
    $value = (isset($options[$term])) ? $options[$term] : null;
    $prefix = DMST_MC_IMMEDIATE_OPTIONS . "[lists][$posttype][$taxonomy]";
    dmst_admin_helper_input($prefix, $term, $value, 'list', null, null, '', '', $lists, __('--- Do not send ---', DMST_MC_IMMEDIATE_TEXT_DOMAIN));
}

/**
 * @since 1.0
 * @param array $args posttype, taxonomy and term must be set
 */
function dmst_mc_immediate_field_mctemplate($args) {
    $posttype = $args['posttype'];
    $taxonomy = $args['taxonomy'];
    $term = $args['term'];
    $templates = dmst_mc_immediate_templates();
    $options = dmst_admin_helper_get_option(DMST_MC_IMMEDIATE_OPTIONS, 'templates', array());
    $options = (isset($options[$posttype])) ? $options[$posttype] : array();
    $options = (isset($options[$taxonomy])) ? $options[$taxonomy] : array();
    $value = (isset($options[$term])) ? $options[$term] : null;
    $prefix = DMST_MC_IMMEDIATE_OPTIONS . "[templates][$posttype][$taxonomy]";
    dmst_admin_helper_input($prefix, $term, $value, 'list', null, null, '', '', $templates, __('--- Default ---', DMST_MC_IMMEDIATE_TEXT_DOMAIN));
}
?>
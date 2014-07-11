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
    <div class="wrap">
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
    echo '<div style="background-color:#eee;display:none;">';
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
    add_settings_section('dmst_mc_immediate_content_options', __('List, Groups and Conditions', DMST_MC_IMMEDIATE_TEXT_DOMAIN), 'dmst_mc_immediate_section_content', 'dmst_mc_immediate');

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
    $newListGroups = array();
    foreach ($input['list-groups'] as $config):
//        if (isset($config['delete']))
//            break;
        if ('' == $config['list-group'])
            break;
        list($list, $grouping, $group) = explode("-", $config['list-group']);
        $conditions = array();
        foreach ($config['conditions'] as $condition):
            if ('' == $condition['taxonomy-term'])
                break;
            list($post, $tax, $term) = explode("-", $condition['taxonomy-term']);
            if ($post != $config['posttype'])
                break;
            $conditions[] = array(
                'taxonomy' => $tax,
                'term' => $term
            );
        endforeach;
        $newListGroups[] = array(
            'posttype' => $config['posttype'],
            'list' => $list,
            'grouping' => $grouping,
            'group' => $group,
            'conditions' => $conditions,
            'template' => $config['template'],
            'locator' => $config['locator'],
        );
    endforeach;
    $input['list-groups'] = $newListGroups;
    $input = DeMomentSomTresTools::adminHelper_esc_attr($input);
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
    echo '<p>' . __('This section shows only post types with AT LEAST ONE taxonomy containing informed terms.', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . '</p>';
    echo '<p>' . __('If the post type is not selected, NOTHING will be sent after publishing content of that post type.', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . '</p>';
}

/**
 * @since 2.0
 */
function dmst_mc_immediate_section_content() {
    $listGroups = DeMomentSomTresTools::get_option(DMST_MC_IMMEDIATE_OPTIONS, 'list-groups');
    ?>
    <p><?php _e('You can add conditions to specify which contents are sent immediately to MailChimp as they are published', DMST_MC_IMMEDIATE_TEXT_DOMAIN); ?></p>
    <p><?php _e('Lines set to none will be ignored.', DMST_MC_IMMEDIATE_TEXT_DOMAIN); ?></p>
    <?php
    $rownum = 0;
    foreach ($listGroups as $rowid => $config):
        $listid = $config['list'];
        $groupingid = $config['grouping'];
        $groupid = $config['group'];
        $posttype = $config['posttype'];
        $template = $config['template'];
        $conditions = $config['conditions'];
        $locator = $config['locator'];
        echo dmst_mc_immediate_admin_row($rownum, $listid, $groupingid, $groupid, $posttype, $template, $locator, $conditions);
        $rownum++;
    endforeach;
    for ($i = 0; $i < 2; $i++):
        echo dmst_mc_immediate_admin_row($rownum);
        $rownum++;
    endfor;
}

/**
 * @since 2.0
 */
function dmst_mc_immediate_admin_row($rownum, $listid = 0, $groupingid = 0, $groupid = 0, $posttype = '', $template = '', $locator = '', $conditions = array()) {
    $prefix = DMST_MC_IMMEDIATE_OPTIONS . "[list-groups][$rownum]";
    if ($locator == ""):
        $locator = DMST_MC_IMMEDIATE_STDTXT;
    endif;
    $result = "<table class='form-table' style='border:1px solid #ccc;'>";
    $result .="<tbody>";
    $result .= "<tr><th scope='row'>" . __('List & Group', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "</th>";
    $result .= "<td>";
    $result .= dmst_mc_immediate_select_list_groups($prefix, 'list-group', $listid, $groupingid, $groupid);
    $result .= "</td></tr>";
    $result .= "<tr><th scope='row'>" . __('Post type', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "</th>";
    $result .= "<td>";
    $result .= dmst_mc_immediate_select_posttypes($prefix, 'posttype', $posttype);
    $result .= "</td></tr>";
    $result .= "<tr><th scope='row'>" . __('Conditions', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "</th>";
    $result .= "<td style='border:1px solid #ccc;'>";
    $result .= dmst_mc_immediate_conditions($prefix, $conditions, $posttype);
    $result .= "</td></tr>";
    $result .= "<tr><th scope='row'>" . __('Template', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "</th>";
    $result .= "<td>";
    $result .= dmst_mc_immediate_select_template($prefix, 'template', $template);
    $result .= "</td></tr>";
    $result .= "<tr><th scope='row'>" . __('Template Locator', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "</th>";
    $result .= "<td>";
    $result .= DeMomentSomTresTools::adminHelper_inputArray($prefix, 'locator', $locator, array('echo' => false));
    $result .= "</td></tr>";
//    $result .= "<tr><th scope='row'>" . __('Delete this entry when saving', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "</th>";
//    $result .= "<td>";
//    $result .= DeMomentSomTresTools::adminHelper_inputArray($prefix, 'delete', false, array('type' => 'checkbox', 'echo' => false));
//    $result .= "</td></tr>";
    $result.="</table>";
    return $result;
}

/**
 * @since 1.0
 */
function dmst_mc_immediate_field_apikey() {
    $name = 'API';
    $value = DeMomentSomTresTools::get_option(DMST_MC_IMMEDIATE_OPTIONS, $name);
    DeMomentSomTresTools::adminHelper_inputArray(DMST_MC_IMMEDIATE_OPTIONS, $name, $value, array(
        'class' => 'regular-text'
    ));
}

/**
 * @since 1.0
 */
function dmst_mc_immediate_option_post_types() {
//    $lists = dmst_mc_immediate_lists();
    $post_types = dmst_mc_immediate_get_posttypes();
    foreach ($post_types as $p):
        //$name = 'post-type-' . $p;
        $taxonomies = dmst_mc_immediate_get_posttype_taxonomies($p);
        if (count($taxonomies) > 0):
            add_settings_field('dmst_mc_immediate_' . $p, $p, 'dmst_mc_immediate_field_posttype', 'dmst_mc_immediate', 'dmst_mc_immediate_posttype_options', array('posttype' => $p));
//            foreach ($taxonomies as $t):
//                $tname = $t;
//                $terms = get_terms(array($tname), array('hide_empty' => false));
//                if (count($terms) > 0):
//                    add_settings_section('dmst_mc_immediate_' . $p . '_' . $tname, $p . ' ' . __('taxonomy', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . ': ' . $tname, 'dmst_mc_immediate_section_content', 'dmst_mc_immediate');
//                    foreach ($terms as $term):
//                        add_settings_field('dmst_mc_immediate_' . $p . '_' . $tname . '_' . $term->slug, __('List for', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . ' ' . $term->slug, 'dmst_mc_immediate_field_mclist', 'dmst_mc_immediate', 'dmst_mc_immediate_' . $p . '_' . $tname, array(
//                            'posttype' => $p,
//                            'taxonomy' => $tname,
//                            'term' => $term->slug
//                                )
//                        );
//                        add_settings_field('dmst_mc_immediate_' . $p . '_' . $tname . '_' . $term->slug . '_template', __('Template for', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . ' ' . $term->slug, 'dmst_mc_immediate_field_mctemplate', 'dmst_mc_immediate', 'dmst_mc_immediate_' . $p . '_' . $tname, array(
//                            'posttype' => $p,
//                            'taxonomy' => $tname,
//                            'term' => $term->slug
//                                )
//                        );
//                    endforeach;
//                endif;
//            endforeach;
        endif;
    endforeach;
}

/**
 * @since 1.0
 * @param array $args arguments 'posttype' sets the posttype to show
 */
function dmst_mc_immediate_field_posttype($args) {
    $name = $args['posttype'];
    $array = DeMomentSomTresTools::get_option(DMST_MC_IMMEDIATE_OPTIONS, 'posttypes', array());
    $value = (isset($array[$name])) ? $array[$name] : 'off';
    DeMomentSomTresTools::adminHelper_input(DMST_MC_IMMEDIATE_OPTIONS . '[posttypes]', $name, $value, 'checkbox', 'on');
}

/**
 * @since 1.0
 * @param array $args posttype, taxonomy and term must be set
 * @deprecated 2.0
 */
function dmst_mc_immediate_field_mclist($args) {
    $posttype = $args['posttype'];
    $taxonomy = $args['taxonomy'];
    $term = $args['term'];
    $lists = dmst_mc_immediate_lists();
    $options = DeMomentSomTresTools::get_option(DMST_MC_IMMEDIATE_OPTIONS, 'lists', array());
    $options = (isset($options[$posttype])) ? $options[$posttype] : array();
    $options = (isset($options[$taxonomy])) ? $options[$taxonomy] : array();
    $value = (isset($options[$term])) ? $options[$term] : null;
    $prefix = DMST_MC_IMMEDIATE_OPTIONS . "[lists][$posttype][$taxonomy]";
    DeMomentSomTresTools::adminHelper_input($prefix, $term, $value, 'list', null, null, '', '', $lists, __('--- Do not send ---', DMST_MC_IMMEDIATE_TEXT_DOMAIN));
}

/**
 * @since 1.0
 * @param array $args posttype, taxonomy and term must be set
 * @deprecated 2.0
 */
function dmst_mc_immediate_field_mctemplate($args) {
    $posttype = $args['posttype'];
    $taxonomy = $args['taxonomy'];
    $term = $args['term'];
    $templates = dmst_mc_immediate_templates();
    $options = DeMomentSomTresTools::get_option(DMST_MC_IMMEDIATE_OPTIONS, 'templates', array());
    $options = (isset($options[$posttype])) ? $options[$posttype] : array();
    $options = (isset($options[$taxonomy])) ? $options[$taxonomy] : array();
    $value = (isset($options[$term])) ? $options[$term] : null;
    $prefix = DMST_MC_IMMEDIATE_OPTIONS . "[templates][$posttype][$taxonomy]";
    DeMomentSomTresTools::adminHelper_input($prefix, $term, $value, 'list', null, null, '', '', $templates, __('--- Default ---', DMST_MC_IMMEDIATE_TEXT_DOMAIN));
}

/**
 * 
 * @param type $prefix
 * @param type $field
 * @param type $value
 * @return type
 * @since 2.0
 */
function dmst_mc_immediate_select_template($prefix, $field, $value) {
    $templates = dmst_mc_immediate_templates();
    return DeMomentSomTresTools::adminHelper_inputArray($prefix, $field, $value, array(
                'type' => 'list',
                'list' => $templates,
                'listNone' => __('--- None ---', DMST_MC_IMMEDIATE_TEXT_DOMAIN),
                'echo' => false,
    ));
}

/**
 * @since 2.0
 */
function dmst_mc_immediate_select_posttypes($prefix, $field, $value) {
    $posttypesList = dmst_mc_immediate_get_posttypes();
    $posttypes = array();
    foreach ($posttypesList as $id => $name):
        $posttypes[] = array(
            'id' => $id,
            'name' => $name
        );
    endforeach;
//    echo '<pre>' . print_r($posttypes, true) . '</pre>';
//    exit;
    return DeMomentSomTresTools::adminHelper_inputArray($prefix, $field, $value, array(
                'type' => 'list',
                'list' => $posttypes,
                'listNone' => __('--- None ---', DMST_MC_IMMEDIATE_TEXT_DOMAIN),
                'echo' => false,
    ));
}

/**
 * @since 2.0
 */
function dmst_mc_immediate_select_list_groups($prefix, $field, $listid = '', $groupingid = '', $groupid = '') {
    $lists = dmst_mc_immediate_lists();
    $select = array();
    foreach ($lists as $list):
        $select[] = array(
            'id' => $list['id'],
            'name' => $list['name']
        );
        if (isset($list['groupings'])):
            foreach ($list['groupings'] as $grouping):
                foreach ($grouping['groups'] as $group):
                    $select[] = array(
                        'id' => $list['id'] . '-' . $grouping['id'] . '-' . $group['id'],
                        'name' => $list['name'] . ' - ' . $group['name']
                    );
                endforeach;
            endforeach;
        endif;
    endforeach;
    $value = '';
    if ('' != $listid):
        $value = $listid;
        if ('' != $groupingid)
            if ('' != $groupid)
                $value.='-' . $groupingid . '-' . $groupid;
    endif;
    return DeMomentSomTresTools::adminHelper_inputArray($prefix, $field, $value, array(
                'type' => 'list',
                'list' => $select,
                'listNone' => __('--- None ---', DMST_MC_IMMEDIATE_TEXT_DOMAIN),
                'echo' => false,
    ));
}

/**
 * @since 2.0
 */
function dmst_mc_immediate_select_tax_term($prefix, $field, $taxid = '', $termid = '', $posttype = '') {
    $select = dmst_mc_immediate_get_tax_terms();
    $value = '';
    if ('' != $posttype)
        if ('' != $taxid)
            if ('' != termid)
                $value = $posttype . '-' . $taxid . '-' . $termid;
    return DeMomentSomTresTools::adminHelper_inputArray($prefix, $field, $value, array(
                'type' => 'list',
                'list' => $select,
                'listNone' => __('--- None ---', DMST_MC_IMMEDIATE_TEXT_DOMAIN),
                'echo' => false,
    ));
}

/**
 * @since 2.0
 */
function dmst_mc_immediate_conditions($prefix, $conditions, $posttype) {
//    echo '<pre>' . print_r($conditions, true) . '</pre>';
    $result = "<table class = 'table-form'>";
    $result .= "<thead>";
    $result .= "<th scope = 'column'>" . __('Taxonomy & term', DMST_MC_IMMEDIATE_TEXT_DOMAIN) . "</th>";
    $result .= "</thead>";
    $result .= "<tbody>";
    $rownum = 0;
    if (count($conditions > 0))
        foreach ($conditions as $condition):
            $newPrefix = $prefix . "[conditions][$rownum]";
            $rownum++;
            $result .= "<tr>";
            $result .= "<td>" . dmst_mc_immediate_select_tax_term($newPrefix, 'taxonomy-term', $condition['taxonomy'], $condition['term'], $posttype) . "</td>";
            $result .= "</tr>";
        endforeach;
    for ($i = 0; $i < 2; $i++):
        $newPrefix = $prefix . "[conditions][$rownum]";
        $rownum++;
        $result .= "<tr>";
        $result .= "<td>" . dmst_mc_immediate_select_tax_term($newPrefix, 'taxonomy-term') . "</td>";
        $result .= "</tr>";
    endfor;
    $result .= "</tbody>";
    $result .= "</table>";
    return $result;
}

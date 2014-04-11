<?php

/*
 * Library including admin and settings helping functions 
 * version 1.0
 */

if (!function_exists('dmst_admin_helper_esc_attr')):

    /**
     * Recursive applies esc_attr to a variable content array
     * @param array $x
     * @return array
     * @since 1.0
     */
    function dmst_admin_helper_esc_attr($x) {
        if (is_array($x)):
            return array_map('dmst_admin_helper_esc_attr', $x);
        else:
            return esc_attr($x);
        endif;
    }

endif;

if (!function_exists('dmst_admin_helper_input')):

    /**
     * Writes the options field with value in the admin page
     * @param string $prefix the option prefix name (content before the [ symbol)
     * @param string $field the option name
     * @param string $value the option value 
     * @param string $type specifies the type of input. If not set, it will be treated as a text box. Allowed values: 'page-dropdown' to show a drop down of pages, 'radio' to show a radio button, 'checkbox' to show a checkbox, 'list' as select
     * @param mixed $checked
     * @param string $html_before html to print before the begining of field
     * @param string $html_after html to print after the end of the field
     * @param string $label if set is showed as the radio button element label
     * @param array $list an array containing pairs of 'id','name'
     * @since 1.0
     */
    function dmst_admin_helper_input($prefix, $field, $value, $type = null, $checked = false, $label = null, $html_before = '', $html_after = '', $list = array(), $listNone = '-------') {
        echo $html_before;
        switch ($type):
            case 'page-dropdown':
                wp_dropdown_pages(array('name' => $prefix . '[' . $field . ']', 'show_option_none' => __('&mdash; Select &mdash;'), 'option_none_value' => '0', 'selected' => $value));
                break;
            case 'radio':
                echo "<input id='$field' name='" . $prefix . "[$field]' type='radio' value='$value'" . checked($value, $checked, false) . " />";
                if (isset($label)):
                    echo " <label>$label</label>";
                endif;
                break;
            case 'checkbox':
                echo "<input id='$field' name='" . $prefix . "[$field]' type='checkbox'" . checked($value, 'on', false) . " />";
                break;
            case 'list':
                echo "<select id='$field' name='".$prefix."[".$field."]'>";
                echo "<option value=''>".$listNone."</option>";
                foreach($list as $l):
                    $id=$l['id'];
                    $n=$l['name'];
                    echo "<option value='$id' ".selected($value,$id).">$n</option>";
                endforeach;
                echo "</select>";
                break;
            default:
                echo "<input id='" . $field . "' name='" . $prefix . "[" . $field . "]' type='text' value='$value'/>";
        endswitch;
        echo $html_after;
    }

endif;

if (!function_exists('dmst_admin_helper_get_option')):

    /**
     * Gets a the prefix['name'] option and, if not set, returns the default value provided
     * @param string $prefix the option prefix name (content before the [ symbol)
     * @param string $name the option name
     * @param mixed $default the default value
     * @return mixed the option new value
     * @since 1.0
     */
    function dmst_admin_helper_get_option($prefix, $name, $default = null) {
        $options = get_option($prefix);
        if (!isset($options[$name])):
            return $default;
        else:
            return $options[$name];
        endif;
    }


endif;
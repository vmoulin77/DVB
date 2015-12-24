<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('set_value'))
{
    /**
     * Form Value
     *
     * Grabs a value from the POST array for the specified field so you can
     * re-populate an input field or textarea. If Form Validation
     * is active it retrieves the info from the validation class
     *
     * @param   string  $field      Field name
     * @param   string  $default    Default value
     * @param   bool    $html_escape    Whether to escape HTML special characters or not
     * @return  string
     */
    function set_value($field, $default = '', $html_escape = TRUE)
    {
        $CI =& get_instance();

        if (is_bool($default)) {
            $default = $default ? '1' : '0';
        }

        $value = (isset($CI->form_validation) && is_object($CI->form_validation) && $CI->form_validation->has_rule($field))
            ? $CI->form_validation->set_value($field, $default)
            : $CI->input->post($field, FALSE);

        isset($value) OR $value = $default;
        return ($html_escape) ? html_escape($value) : $value;
    }
}

if ( ! function_exists('set_value_datetime'))
{
    function set_value_datetime($field, $default, $format = 'd/m/Y') {
        if ( ! $default instanceof DateTime) {
            log_message('error', 'The $default parameter in the set_value_datetime() function is not an instance of DateTime.');
            return false;
        }

        return set_value($field, $default->format($format));
    }
}

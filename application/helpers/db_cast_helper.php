<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('cast_row'))
{
    function cast_row(&$row) {
        $CI =& get_instance();

        $db_cast_data = $CI->config->item('db_cast_data');

        $vars = array_keys(get_object_vars($row));

        foreach ($vars as $var) {
            if ((strpos($var, ':') !== false)
                && ($row->{$var} !== null)
            ) {
                list($table, $field) = explode(':', $var);
                if (isset($db_cast_data[$table][$field])) {
                    switch ($db_cast_data[$table][$field]) {
                        case 'int':
                            $row->{$var} = (int) $row->{$var};
                            break;
                        case 'string':
                            $row->{$var} = (string) $row->{$var};
                            break;
                        case 'bool':
                            $row->{$var} = $row->{$var} === STR_DB_BOOL_TRUE;
                            break;
                        case 'datetime':
                            $row->{$var} = new DateTime($row->{$var});
                            break;
                        default:
                            break;
                    }
                }
            }
        }
    }
}

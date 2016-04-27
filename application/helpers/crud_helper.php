<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('model_to_table'))
{
    function model_to_table($model) {
        return isset($model::$table) ? $model::$table : strtolower($model);
    }
}

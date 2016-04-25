<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('model_table'))
{
    function model_table($model) {
        return isset($model::$table) ? $model::$table : strtolower($model);
    }
}

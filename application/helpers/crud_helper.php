<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('model_to_table'))
{
    function model_to_table($model) {
        return isset($model::$table) ? $model::$table : strtolower($model);
    }
}

if ( ! function_exists('init_finder_manager'))
{
    function init_finder_manager($model, $method, $filter = null) {
        if ($filter instanceof utils\crud\Finder_manager) {
            return $filter;
        }

        $finder_manager = new utils\crud\Finder_manager($model, $method);
        if ($filter !== null) {
            $finder_manager->set_type(FIND_ONE);
            $finder_manager->where(model_to_table($model) . '.id', $filter);
        }

        return $finder_manager;
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use utils\crud\Finder_manager;

if ( ! function_exists('model_to_table'))
{
    function model_to_table($model) {
        return isset($model::$table) ? $model::$table : strtolower($model);
    }
}

if ( ! function_exists('init_finder_manager'))
{
    function init_finder_manager($model, $method, $filter = null) {
        if ($filter instanceof Finder_manager) {
            return $filter;
        }

        $finder_manager = new Finder_manager($model, $method);
        if (is_numeric($filter)) {
            $finder_manager->set_type(FIND_ONE);
            $finder_manager->where(model_to_table($model) . '.id', $filter);
        } else {
            $finder_manager->order_by(model_to_table($model) . '.id', 'ASC');
        }

        return $finder_manager;
    }
}

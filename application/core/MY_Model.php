<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model
{
    public static function find($filter) {
        if ($filter instanceof utils\finder\Finder_manager) {
            $finder_manager = $filter;
        } else {
            $finder_manager = new utils\finder\Finder_manager();
            $finder_manager->where(model_to_table(get_called_class()) . '.id', (int) $filter);
        }

        $all = static::find_all($finder_manager);

        switch (count($all)) {
            case 0:
                return null;
            case 1:
                return $all[0];
            default:
                return false;
        }
    }
}

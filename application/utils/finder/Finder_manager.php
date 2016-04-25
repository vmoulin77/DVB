<?php
namespace utils\finder;

class Finder_manager
{
    private $CI;
    private $model;
    private $id = null;
    private $stack = array();
    private $with = array();

    public function __construct() {
        $this->CI =& get_instance();
    }

    public function model($model) {
        $this->CI->load->model($model);
        $this->model = $model;
        return $this;
    }

    public function id($id) {
        $this->id = $id;
        return $this;
    }

    public function __call($method, $args) {
        $this->stack[] = array(
            'method'  => $method,
            'args'    => $args
        );
        return $this;
    }

    public function with($with) {
        if (is_array($with)) {
            $this->with = array_merge($this->with, $with);
        } else {
            $this->with[] = $with;
        }
        
        return $this;
    }

    public function find() {
        return call_user_func($this->model . '::find', $this);
    }

    public function find_all() {
        return call_user_func($this->model . '::find_all', $this);
    }

    public function complete_query() {
        if ($this->id !== null) {
            $this->CI->db->where(model_table($this->model) . '.id', $this->id);
        }

        foreach ($this->stack as $item) {
            call_user_func_array(array($this->CI->db, $item['method']), $item['args']);
        }
    }

    private function exec_withers_recursive($data, $with) {
        if ($data === null) {
            return;
        }

        if ( ! is_array($data)) {
            $data = array($data);
        }

        if ( ! is_array($with)) {
            $with = array($with);
        }

        foreach ($data as $data_item) {
            foreach ($with as $with_key => $with_value) {
                if (is_string($with_key)) {
                    $this->exec_withers_recursive(call_user_func_array(array($data_item, 'with_' . $with_key), array()), $with_value);
                } else {
                    if ( ! is_array($with_value)) {
                        $with_value = array($with_value);
                    }

                    foreach ($with_value as $with_item) {
                        call_user_func_array(array($data_item, 'with_' . $with_item), array());
                    }
                }
            }
        }
    }

    public function exec_withers($data) {
        $this->exec_withers_recursive($data, $this->with);
    }
}

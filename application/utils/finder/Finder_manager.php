<?php
namespace utils\finder;

class Finder_manager
{
    private $CI;
    private $model;
    private $stack = array();
    private $with = array();

    public function __construct() {
        $this->CI =& get_instance();
    }

    public function model($model) {
        $this->CI->load->model($model);
        $this->model = $model;
    }

    public function __call($method, $args) {
        $this->stack[] = array(
            'method'  => $method,
            'args'    => $args
        );
    }

    public function with($with) {
        $this->with[] = $with;
    }

    public function find() {
        return call_user_func($this->model . '::find', $this);
    }

    public function find_all() {
        return call_user_func($this->model . '::find_all', $this);
    }

    public function complete_query() {
        foreach ($this->stack as $item) {
            call_user_func_array(array($this->CI->db, $item['method']), $item['args']);
        }
    }

    public function exec_withers(&$data) {
        if (is_object($data)) {
            $this->exec_withers(array($data));
        }

        foreach ($data as &$item) {
            foreach ($this->with as $with) {
                call_user_func_array(array($item, 'with_' . $with), array());
            }
        }
    }
}

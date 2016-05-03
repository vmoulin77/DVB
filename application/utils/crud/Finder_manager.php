<?php
namespace utils\crud;

class Finder_manager
{
    private $CI;
    private $model;
    private $method;
    private $type;
    private $parameters;
    private $stack = [];

    public function __construct($model, $method, $type = FIND_MANY, $parameters = []) {
        $this->CI =& get_instance();
        $this->CI->load->model($model);

        $this->model       = $model;
        $this->method      = $method;
        $this->type        = $type;
        $this->parameters  = $parameters;
    }

    public function get_type() {
        return $this->type;
    }

    public function set_type($type) {
        $this->type = $type;
    }

    public function get_parameters() {
        return $this->parameters;
    }

    public function set_parameters($parameters) {
        $this->parameters = $parameters;
    }

    public function get_parameter($parameter) {
        return $this->parameters[$parameter];
    }

    public function add_parameter($parameter, $value) {
        $this->parameters[$parameter] = $value;
    }

    public function add_parameters($parameters) {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    public function remove_parameter($parameter) {
        unset($this->parameters[$parameter]);
    }

    public function check_parameters($parameters) {
        if (empty(array_diff($parameters, array_keys($this->parameters)))) {
            return true;
        } else {
            return false;
        }
    }

    public function __call($method, $args) {
        $this->stack[] = array(
            'method'  => $method,
            'args'    => $args
        );
        return $this;
    }

    public function get() {
        return call_user_func($this->model . '::' . $this->method, $this);
    }

    public function complete_query() {
        foreach ($this->stack as $item) {
            call_user_func_array(array($this->CI->db, $item['method']), $item['args']);
        }
    }

    public function format_return($retour) {
        if ($this->type == FIND_ONE) {
            switch (count($retour)) {
                case 0:
                    return null;
                case 1:
                    return $retour[0];
                default:
                    return false;
            }
        }

        return $retour;
    }
}

<?php
namespace utils\crud;

class Finder_manager
{
    const TYPE_ONE   = 'one';
    const TYPE_MANY  = 'many';

    private $CI;
    private $model;
    private $method;
    private $type;
    private $stack = array();

    public function __construct($model, $method, $type = self::TYPE_MANY) {
        $this->CI =& get_instance();
        $this->CI->load->model($model);

        $this->model   = $model;
        $this->method  = $method;
        $this->type    = $type;
    }

    public function one() {
        $this->type = self::TYPE_ONE;
    }

    public function many() {
        $this->type = self::TYPE_MANY;
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
        if ($this->type == self::TYPE_ONE) {
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

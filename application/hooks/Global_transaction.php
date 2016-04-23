<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Global_transaction
{
    private $CI;
    private $is_activated = true;

    public function __construct() {
        $this->CI =& get_instance();

        $actions_without_global_trans = $this->CI->config->item('actions_without_global_trans');
        if (isset($actions_without_global_trans[$this->CI->router->class])
            && in_array($this->CI->router->method, $actions_without_global_trans[$this->CI->router->class])
        ) {
            $this->is_activated = false;
        }
    }

    public function open() {
        if ($this->is_activated) {
            $this->CI->transaction->begin();
        }
    }

    public function close() {
        if ($this->is_activated) {
            $this->CI->transaction->commit();
        }
    }
}

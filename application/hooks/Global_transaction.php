<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Global_transaction
{
    private $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    public function open() {
        $this->CI->transaction->begin();
    }

    public function close() {
        $this->CI->transaction->commit();
    }
}

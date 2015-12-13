<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Transaction
{
    private $CI;
    private $depth;
    private $is_rollback;

    public function __construct() {
        $this->CI =& get_instance();
        $this->reinitialize();
    }

    private function reinitialize() {
        $this->depth = 0;
        $this->is_rollback = false;
    }

    public function begin() {
        if ($this->depth == 0) {
            $this->CI->db->trans_begin();
        }
        $this->depth += 1;
    }

    public function set_as_rollback() {
        $this->is_rollback = true;
    }

    public function rollback() {
        $this->is_rollback = true;
        $this->depth -= 1;
        if ($this->depth == 0) {
            $this->CI->db->trans_rollback();
            $this->reinitialize();
        }
    }

    public function commit() {
        $this->depth -= 1;
        if ($this->depth == 0) {
            if ($this->is_rollback) {
                $this->CI->db->trans_rollback();
            } else {
                $this->CI->db->trans_commit();
            }
            $this->reinitialize();
        }
    }
}

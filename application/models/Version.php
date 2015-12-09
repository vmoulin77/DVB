<?php

class Version extends CI_Model
{
    private $id;
    private $database_version;
    private $app_version_code;
    private $app_version_name;
    private $created_at;

    private $created_decks = array();
    private $created_card_contents = array();
    private $deleted_cards = array();
    private $card_moves = array();

    public static function make($id, $database_version, $app_version_code, $app_version_name, $created_at) {
        $retour = new self();

        $retour->id = $id;
        $retour->database_version = $database_version;
        $retour->app_version_code = $app_version_code;
        $retour->app_version_name = $app_version_name;
        $retour->created_at = $created_at;

        return $retour;
    }

    public function get_id() {
        return $this->id;
    }
    public function set_id($id) {
        $this->id = $id;
    }

    public function get_database_version() {
        return $this->database_version;
    }
    public function set_database_version($database_version) {
        $this->database_version = $database_version;
    }

    public function get_app_version_code() {
        return $this->app_version_code;
    }
    public function set_app_version_code($app_version_code) {
        $this->app_version_code = $app_version_code;
    }

    public function get_app_version_name() {
        return $this->app_version_name;
    }
    public function set_app_version_name($app_version_name) {
        $this->app_version_name = $app_version_name;
    }

    public function get_created_at() {
        return $this->created_at;
    }
    public function set_created_at($created_at) {
        $this->created_at = $created_at;
    }

    public function get_created_decks() {
        return $this->created_decks;
    }
    public function set_created_decks($created_decks) {
        $this->created_decks = $created_decks;
    }

    public function get_created_card_contents() {
        return $this->created_card_contents;
    }
    public function set_created_card_contents($created_card_contents) {
        $this->created_card_contents = $created_card_contents;
    }

    public function get_deleted_cards() {
        return $this->deleted_cards;
    }
    public function set_deleted_cards($deleted_cards) {
        $this->deleted_cards = $deleted_cards;
    }

    public function get_card_moves() {
        return $this->card_moves;
    }
    public function set_card_moves($card_moves) {
        $this->card_moves = $card_moves;
    }
    
    /********************************************************/
    
    public static function get_current_version() {
        $CI = get_instance();

        $str_query = "SELECT id, database_version, app_version_code, app_version_name "
                   . "FROM version "
                   . "WHERE id = (SELECT MAX(id) FROM version)";
        $query = $CI->db->query($str_query);

        $row = $query->row();

        $database_version = (empty($row->database_version)) ? null : (int) $row->database_version;
        $app_version_code = (empty($row->app_version_code)) ? null : (int) $row->app_version_code;
        $app_version_name = (empty($row->app_version_name)) ? null : $row->app_version_name;

        return self::make(
            (int) $row->id,
            $database_version,
            $app_version_code,
            $app_version_name,
            null
        );
    }

    public static function get_by_id($id, $return_format = 'standard') {
        $CI = get_instance();

        $CI->db->select('id, database_version, app_version_code, app_version_name, created_at')
               ->from('version')
               ->where('id', $id);

        $query = $CI->db->get();

        if ($query->num_rows() == 1) {
            $row = $query->row();
            
            if ($return_format == 'standard') {
                $version_id                = (int) $row->id;
                $version_database_version  = (empty($row->database_version))  ? null : (int) $row->database_version;
                $version_app_version_code  = (empty($row->app_version_code))  ? null : (int) $row->app_version_code;
                $version_app_version_name  = (empty($row->app_version_name))  ? null : $row->app_version_name;
                $version_created_at        = (empty($row->created_at))        ? null : new DateTime($row->created_at);
            } elseif ($return_format == 'string') {
                $version_id                = $row->id;
                $version_database_version  = (empty($row->database_version))  ? null : $row->database_version;
                $version_app_version_code  = (empty($row->app_version_code))  ? null : $row->app_version_code;
                $version_app_version_name  = (empty($row->app_version_name))  ? null : $row->app_version_name;
                $version_created_at        = (empty($row->created_at))        ? null : $row->created_at;
            }

            return self::make(
                $version_id,
                $version_database_version,
                $version_app_version_code,
                $version_app_version_name,
                $version_created_at
            );
        } else {
            return false;
        }
    }

    public static function freeze($database_version, $app_version_code , $app_version_name) {
        $CI = get_instance();

        $now = new DateTime();

        $CI->db->trans_begin();

        $current_version = self::get_current_version();

        $data = array(
            'database_version'  => $database_version,
            'app_version_code'  => $app_version_code,
            'app_version_name'  => $app_version_name,
            'created_at'        => $now->format('Y-m-d H:i:s'),
        );

        $CI->db->set($data);
        $CI->db->where('id', $current_version->id);
        if ( ! $CI->db->update('version')) {
            $CI->db->trans_rollback();
            return false;
        }

        if ($CI->db->insert('version', array('id' => null))) {
            $CI->db->trans_commit();
            return true;
        } else {
            $CI->db->trans_rollback();
            return false;
        }
    }
}

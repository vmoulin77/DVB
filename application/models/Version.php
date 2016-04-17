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

    /********************************************************/
    /*                 The getters/setters                  */
    /********************************************************/
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

    /********************************************************/
    /*                    The finders                       */
    /********************************************************/
    public static function find($id) {
        $CI = get_instance();

        $id = (int) $id;

        $CI->db->select('database_version, app_version_code, app_version_name, created_at')
               ->from('version')
               ->where('id', $id);

        $query = $CI->db->get();

        if ($query->num_rows() == 1) {
            $row = $query->row();
            
            $version_database_version  = (empty($row->database_version))  ? null : (int) $row->database_version;
            $version_app_version_code  = (empty($row->app_version_code))  ? null : (int) $row->app_version_code;
            $version_app_version_name  = (empty($row->app_version_name))  ? null : $row->app_version_name;
            $version_created_at        = (empty($row->created_at))        ? null : new DateTime($row->created_at);

            return self::make(
                $id,
                $version_database_version,
                $version_app_version_code,
                $version_app_version_name,
                $version_created_at
            );
        } else {
            return false;
        }
    }

    public static function find_current_version() {
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
    /********************************************************/

    /********************************************************/
    /*                    The withers                       */
    /********************************************************/
    /********************************************************/

    /********************************************************/
    /*                   The modifiers                      */
    /********************************************************/
    /********************************************************/

    public static function freeze($database_version, $app_version_code , $app_version_name) {
        $CI = get_instance();

        $now = new DateTime();

        $current_version = self::find_current_version();

        $data = array(
            'database_version'  => $database_version,
            'app_version_code'  => $app_version_code,
            'app_version_name'  => $app_version_name,
            'created_at'        => $now->format('Y-m-d H:i:s'),
        );

        $CI->db->set($data);
        $CI->db->where('id', $current_version->id);
        if ( ! $CI->db->update('version')) {
            $CI->transaction->set_as_rollback();
            return false;
        }

        if ($CI->db->insert('version', array('id' => null))) {
            return true;
        } else {
            $CI->transaction->set_as_rollback();
            return false;
        }
    }

    public function compare_to(Version $version_before) {
        $this->load->model('Card_content');

        if ($this->get_id() <= $version_before->get_id()) {
            return array();
        }

        $this->db->select('id, id_card, word_english, word_french, is_active_english, is_active_french, id_version, is_last')
                 ->from('card_content')
                 ->where('id_version <= ', $this->id)
                 ->order_by('id_card ASC, id_version ASC');

        $query = $this->db->get();

        $card_contents = array();

        foreach ($query->result() as $row) {
            $id_card = (int) $row->id_card;
            $id_version = (int) $row->id_version;

            if ($id_version <= $version_before->id) {
                $card_contents[$id_card] = array();
            }

            $id                 = (int) $row->id;
            $word_english       = $row->word_english;
            $word_french        = $row->word_french;
            $is_active_english  = (bool) $row->is_active_english;
            $is_active_french   = (bool) $row->is_active_french;
            $is_last            = (bool) $row->is_last;

            $card_content = Card_content::make(
                $id,
                $word_english,
                $word_french,
                $is_active_english,
                $is_active_french,
                $is_last
            );

            $card_content->id_version = $id_version;

            $card_contents[$id_card][] = $card_content;
        }

        $retour = array();

        foreach ($card_contents as $value) {
            if ((count($value) > 1)
                || ($value[0]->id_version > $version_before->id)
            ) {
                $retour[] = $value;
            }
        }

        return $retour;
    }
}

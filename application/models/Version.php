<?php

class Version extends MY_Model
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
    public static function find($filter = null) {
        $CI = get_instance();

        $finder_manager = init_finder_manager(__CLASS__, __METHOD__, $filter);

        $CI->db->select('id AS version:id, database_version AS version:database_version, app_version_code AS version:app_version_code, app_version_name AS version:app_version_name, created_at AS version:created_at')
               ->from('version');

        $finder_manager->complete_query();

        $query = $CI->db->get();

        $retour = array();

        foreach ($query->result() as $row) {
            cast_row($row);
            
            $version = self::make(
                $row->{'version:id'},
                $row->{'version:database_version'},
                $row->{'version:app_version_code'},
                $row->{'version:app_version_name'},
                $row->{'version:created_at'}
            );

            $retour[] = $version;
        }

        return $finder_manager->format_return($retour);
    }
    /********************************************************/

    /********************************************************/
    /*                   The retrievers                     */
    /********************************************************/
    public static function retrieve_current_version() {
        $CI = get_instance();

        $str_query = 'SELECT id AS "version:id", database_version AS "version:database_version", app_version_code AS "version:app_version_code", app_version_name AS "version:app_version_name", created_at AS "version:created_at" '
                   . "FROM version "
                   . "WHERE id = (SELECT MAX(id) FROM version)";
        $query = $CI->db->query($str_query);

        $row = $query->row();
        cast_row($row);

        return self::make(
            $row->{'version:id'},
            $row->{'version:database_version'},
            $row->{'version:app_version_code'},
            $row->{'version:app_version_name'},
            $row->{'version:created_at'}
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

        $current_version = self::retrieve_current_version();

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

        if ($CI->db->insert('version', array('database_version' => null))) {
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

        $this->db
            ->select(
                'id AS "card_content:id",'
                . 'id_card AS "card_content:id_card",'
                . 'word_english AS "card_content:word_english",'
                . 'word_french AS "card_content:word_french",'
                . 'is_active_english AS "card_content:is_active_english",'
                . 'is_active_french AS "card_content:is_active_french",'
                . 'id_version AS "card_content:id_version",'
                . 'is_last AS "card_content:is_last"'
            )
            ->from('card_content')
            ->where('id_version <= ', $this->id)
            ->order_by('id_card ASC, id_version ASC');

        $query = $this->db->get();

        $card_contents = array();

        foreach ($query->result() as $row) {
            cast_row($row);

            if ($row->{'card_content:id_version'} <= $version_before->id) {
                $card_contents[$row->{'card_content:id_card'}] = array();
            }

            $card_content = Card_content::make(
                $row->{'card_content:id'},
                $row->{'card_content:word_english'},
                $row->{'card_content:word_french'},
                $row->{'card_content:is_active_english'},
                $row->{'card_content:is_active_french'},
                $row->{'card_content:is_last'}
            );

            $card_content->id_version = $row->{'card_content:id_version'};

            $card_contents[$row->{'card_content:id_card'}][] = $card_content;
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

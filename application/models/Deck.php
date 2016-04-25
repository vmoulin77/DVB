<?php

class Deck extends MY_Model
{
    private $id;
    private $num;
    private $name;
    
    private $version_when_created;
    private $cards = array();
    private $card_moves = array();
    
    public static function make($id, $num, $name) {
        $retour = new self();

        $retour->id = $id;
        $retour->num = $num;
        $retour->name = $name;

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

    public function get_num() {
        return $this->num;
    }
    public function set_num($num) {
        $this->num = $num;
    }

    public function get_name() {
        return $this->name;
    }
    public function set_name($name) {
        $this->name = $name;
    }

    public function get_version_when_created() {
        return $this->version_when_created;
    }
    public function set_version_when_created($version_when_created) {
        $this->version_when_created = $version_when_created;
    }

    public function get_cards() {
        return $this->cards;
    }
    public function set_cards($cards) {
        $this->cards = $cards;
    }
    public function add_card($card) {
        foreach ($this->cards as $item) {
            if ($item->id == $card->id) {
                return false;
            }
        }

        $this->cards[] = $card;
        return true;
    }
    public function modify_card($card) {
        foreach ($this->cards as &$item) {
            if ($item->id == $card->id) {
                $item = $card;
                return true;
            }
        }

        return false;
    }
    public function remove_card($id_card) {
        foreach ($this->cards as $key => $item) {
            if ($item->id == $id_card) {
                unset($this->cards[$key]);
                return true;
            }
        }

        return false;
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
    public static function find_all(utils\finder\Finder_manager $finder_manager = null) {
        $CI = get_instance();

        if ($finder_manager === null) {
            $finder_manager = new utils\finder\Finder_manager();
        }

        $CI->db->select('id, num, name')
               ->from('deck');

        $finder_manager->complete_query();

        $query = $CI->db->get();

        $retour = array();

        foreach ($query->result() as $row) {
            $deck = self::make(
                (int) $row->id,
                (int) $row->num,
                $row->name
            );

            $retour[] = $deck;
        }

        $finder_manager->exec_withers($retour);

        return $retour;
    }

    public static function find_all_with_contains_current_card($id_card) {
        $CI = get_instance();

        $decks = self::find_all();

        foreach ($decks as &$deck) {
            $CI->db->from('card_deck_version')
                   ->where('card_deck_version.id_card', $id_card)
                   ->where('card_deck_version.id_deck', $deck->id)
                   ->where('card_deck_version.is_last', true)
                   ->where('card_deck_version.type', 'add');

            if ($CI->db->count_all_results() == 0) {
                $deck->contains_current_card = false;
            } else {
                $deck->contains_current_card = true;
            }
        }

        return $decks;
    }
    /********************************************************/

    /********************************************************/
    /*                    The withers                       */
    /********************************************************/
    public function with_version_when_created() {
        $this->load->model('Version');

        $current_version = Version::find_current_version();

        $this->db->select('version.id, version.database_version, version.app_version_code, version.app_version_name, version.created_at')
                 ->from('deck')
                 ->join('version', 'version.id = deck.id_version_when_created')
                 ->where('deck.id', $this->id);

        $query = $this->db->get();
        $row = $query->row();

        if ($query->num_rows() == 1) {
            if ($row->id == $current_version->get_id()) {
                $version_when_created = $current_version;
            } else {
                $version_when_created = Version::make(
                    (int) $row->id,
                    (int) $row->database_version,
                    (int) $row->app_version_code,
                    $row->app_version_name,
                    new DateTime($row->created_at)
                );
            }
        } else {
            return new utils\errors\DVB_Error();
        }

        $this->set_version_when_created($version_when_created);
        
        return $this->version_when_created;
    }
    /********************************************************/

    /********************************************************/
    /*                   The modifiers                      */
    /********************************************************/
    public static function insert($num, $name) {
        $CI = get_instance();

        $CI->load->model('Version');

        if ( ! self::num_is_free($num)) {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error('INSERT_ERROR', "The deck number is not free.");
        }

        $current_version = Version::find_current_version();

        $data = array(
            'num'                      => $num,
            'name'                     => $name,
            'id_version_when_created'  => $current_version->get_id(),
        );

        if ($CI->db->insert('deck', $data)) {
            return true;
        } else {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error();
        }
    }

    public static function update($id, $data) {
        $CI = get_instance();

        if (self::deck_is_deleted($id)) {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error('UPDATE_ERROR', "The deck doesn't exist anymore.");
        }

        $deck = self::find($id);

        if (isset($data['num'])
            && ($data['num'] != $deck->num)
        ) {
            if ( ! self::num_is_free($data['num'])) {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error('UPDATE_ERROR', 'The deck number is not free.');
            }
        }

        $CI->db->set($data)
               ->where('id', $id);

        if ($CI->db->update('deck')) {
            return true;
        } else {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error();
        }
    }

    public static function delete($id) {
        $CI = get_instance();

        $CI->db->where('id', $id);
        if ($CI->db->delete('deck')) {
            if ($CI->db->affected_rows() == 1) {
                return true;
            } else {
                return new utils\errors\DVB_Error('DELETE_ERROR', "The deck doesn't exist anymore.");
            }
        } else {
            return new utils\errors\DVB_Error();
        }
    }
    /********************************************************/

    /********************************************************/

    public static function get_max_num() {
        $CI = get_instance();

        $CI->db->select_max('num', 'max_num');
        $query = $CI->db->get('deck');
        $row = $query->row();

        $retour = (int) $row->max_num;
        return $retour;
    }

    public static function deck_is_empty($id) {
        $CI = get_instance();

        if (self::deck_is_deleted($id)) {
            return new utils\errors\DVB_Error();
        } else {
            $CI->db->from('card_deck_version')
                   ->where('id_deck', $id)
                   ->where('is_last', true)
                   ->where('type', 'add');
            if ($CI->db->count_all_results() == 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function num_is_free($num) {
        $CI = get_instance();

        $CI->db->from('deck')
               ->where('num', $num);
        if ($CI->db->count_all_results() == 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function deck_is_deleted($id) {
        $CI = get_instance();

        $CI->db->from('deck')
               ->where('id', $id);
        if ($CI->db->count_all_results() == 0) {
            return true;
        } else {
            return false;
        }
    }
}

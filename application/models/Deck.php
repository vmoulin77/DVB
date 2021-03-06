<?php

use utils\errors\Standard_error;
use utils\crud\Finder_manager;

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
    public static function find($filter = null) {
        $CI = get_instance();

        $finder_manager = init_finder_manager(__CLASS__, __METHOD__, $filter);

        $CI->db
            ->select(
                'id AS deck:id,'
                . 'num AS deck:num,'
                . 'name AS deck:name'
            )
            ->from('deck');

        $finder_manager->complete_query();

        $query = $CI->db->get();

        $retour = array();

        foreach ($query->result() as $row) {
            cast_row($row);

            $retour[] = self::make(
                $row->{'deck:id'},
                $row->{'deck:num'},
                $row->{'deck:name'}
            );
        }

        return $finder_manager->format_return($retour);
    }

    public static function find_with_version_when_created($filter = null) {
        $CI = get_instance();

        $CI->load->model('Version');

        $finder_manager = init_finder_manager(__CLASS__, __METHOD__, $filter);

        $current_version = Version::retrieve_current_version();

        $CI->db
            ->select(
                'deck.id AS deck:id,'
                . 'deck.num AS deck:num,'
                . 'deck.name AS deck:name,'
                . 'version.id AS version:id,'
                . 'version.database_version AS version:database_version,'
                . 'version.app_version_code AS version:app_version_code,'
                . 'version.app_version_name AS version:app_version_name,'
                . 'version.created_at AS version:created_at'
            )
            ->from('deck')
            ->join('version', 'version.id = deck.id_version_when_created');

        $finder_manager->complete_query();

        $query = $CI->db->get();

        $retour = array();

        foreach ($query->result() as $row) {
            cast_row($row);

            if ($row->{'version:id'} == $current_version->get_id()) {
                $version_when_created = $current_version;
            } else {
                $version_when_created = Version::make(
                    $row->{'version:id'},
                    $row->{'version:database_version'},
                    $row->{'version:app_version_code'},
                    $row->{'version:app_version_name'},
                    $row->{'version:created_at'}
                );
            }

            $deck = self::make(
                $row->{'deck:id'},
                $row->{'deck:num'},
                $row->{'deck:name'}
            );

            $deck->set_version_when_created($version_when_created);

            $retour[] = $deck;
        }

        return $finder_manager->format_return($retour);
    }

    /**
    * Find the deck(s) with an extra attribute "contains_current_card"
    * This method is a parametered finder
    * @finder_param id_card The card id used to check for each deck if it contains the card
    * @param $finder_manager
    * @return The deck(s)
    */
    public static function find_with_contains_current_card(Finder_manager $finder_manager) {
        $CI = get_instance();

        $parameters = ['id_card'];

        $finder_manager->check_parameters($parameters);

        $id_card = $finder_manager->get_parameter('id_card');

        $decks = self::find();

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
    /*                   The retrievers                     */
    /********************************************************/
    /********************************************************/

    /********************************************************/
    /*                    The withers                       */
    /********************************************************/
    public function with_version_when_created() {
        $this->load->model('Version');

        $this->db
            ->select(
                'version.id AS version:id,'
                . 'version.database_version AS version:database_version,'
                . 'version.app_version_code AS version:app_version_code,'
                . 'version.app_version_name AS version:app_version_name,'
                . 'version.created_at AS version:created_at'
            )
            ->from('deck')
            ->join('version', 'version.id = deck.id_version_when_created')
            ->where('deck.id', $this->id);

        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            $row = $query->row();
            cast_row($row);

            $version_when_created = Version::make(
                $row->{'version:id'},
                $row->{'version:database_version'},
                $row->{'version:app_version_code'},
                $row->{'version:app_version_name'},
                $row->{'version:created_at'}
            );
        } else {
            return new Standard_error();
        }

        $this->set_version_when_created($version_when_created);
        
        return $this;
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
            return new Standard_error('INSERT_ERROR', "The deck number is not free.");
        }

        $current_version = Version::retrieve_current_version();

        $data = array(
            'num'                      => $num,
            'name'                     => $name,
            'id_version_when_created'  => $current_version->get_id(),
        );

        if ($CI->db->insert('deck', $data)) {
            return true;
        } else {
            $CI->transaction->set_as_rollback();
            return new Standard_error();
        }
    }

    public static function update($id, $data) {
        $CI = get_instance();

        if (self::deck_is_deleted($id)) {
            $CI->transaction->set_as_rollback();
            return new Standard_error('UPDATE_ERROR', "The deck doesn't exist anymore.");
        }

        $deck = self::find($id);

        if (isset($data['num'])
            && ($data['num'] != $deck->num)
        ) {
            if ( ! self::num_is_free($data['num'])) {
                $CI->transaction->set_as_rollback();
                return new Standard_error('UPDATE_ERROR', 'The deck number is not free.');
            }
        }

        $CI->db->set($data)
               ->where('id', $id);

        if ($CI->db->update('deck')) {
            return true;
        } else {
            $CI->transaction->set_as_rollback();
            return new Standard_error();
        }
    }

    public static function delete($id) {
        $CI = get_instance();

        $CI->db->where('id', $id);
        if ($CI->db->delete('deck')) {
            if ($CI->db->affected_rows() == 1) {
                return true;
            } else {
                return new Standard_error('DELETE_ERROR', "The deck doesn't exist anymore.");
            }
        } else {
            return new Standard_error();
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
            return new Standard_error();
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

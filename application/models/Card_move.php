<?php

class Card_move extends CI_Model
{
    private $type;
    private $is_last;

    private $card;
    private $deck;
    private $version;
    
    public static function make($type, $is_last) {
        $retour = new self();

        $retour->type = $type;
        $retour->is_last = $is_last;

        return $retour;
    }

    public function get_type() {
        return $this->type;
    }
    public function set_type($type) {
        $this->type = $type;
    }

    public function get_is_last() {
        return $this->is_last;
    }
    public function set_is_last($is_last) {
        $this->is_last = $is_last;
    }

    public function get_card() {
        return $this->card;
    }
    public function set_card($card) {
        $this->card = $card;
    }

    public function get_deck() {
        return $this->deck;
    }
    public function set_deck($deck) {
        $this->deck = $deck;
    }

    public function get_version() {
        return $this->version;
    }
    public function set_version($version) {
        $this->version = $version;
    }

    /********************************************************/

    public static function set_last_move($id_card, $id_deck, $is_last) {
        $CI = get_instance();

        $CI->db->from('card_deck_version')
               ->where('id_card', $id_card)
               ->where('id_deck', $id_deck);
        if ($CI->db->count_all_results() == 0) {
            $retour = true;
        } else {
            $CI->db->select('MAX(id_version) AS max_id_version')
                   ->from('card_deck_version')
                   ->where('id_card', $id_card)
                   ->where('id_deck', $id_deck);
            $query = $CI->db->get();
            $max_id_version = $query->row()->max_id_version;

            $CI->db->set('is_last', $is_last)
                   ->where('id_card', $id_card)
                   ->where('id_deck', $id_deck)
                   ->where('id_version', $max_id_version);
            if ($CI->db->update('card_deck_version')) {
                $retour = true;
            } else {
                $retour = false;
            }
        }

        return $retour;
    }

    public static function move($type, $id_card, $id_deck) {
        $CI = get_instance();

        $CI->load->model('Card');
        $CI->load->model('Version');

        if (Card::card_is_deleted($id_card)) {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error('MOVE_ERROR', 'The card has been deleted.');
        }

        $CI->db->select('type, id_version')
               ->from('card_deck_version')
               ->where('id_card', $id_card)
               ->where('id_deck', $id_deck)
               ->where('is_last', true);
        $query = $CI->db->get();

        if ($query->num_rows() != 0) {
            $row = $query->row();
        }

        if (($type == 'remove')
            && (($query->num_rows() == 0) || ($row->type == 'remove'))
        ) {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error('MOVE_ERROR', 'The card is not in the deck.');
        }

        if (($type == 'add')
            && ($query->num_rows() != 0)
            && ($row->type == 'add')
        ) {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error('MOVE_ERROR', 'The card is already in the deck.');
        }

        $current_version = Version::get_current_version();

        if (($query->num_rows() == 0)
            || ($row->id_version < $current_version->get_id())
        ) {
            if ($query->num_rows() != 0) {
                if ( ! self::set_last_move($id_card, $id_deck, false)) {
                    $CI->transaction->set_as_rollback();
                    return new utils\errors\DVB_Error();
                }
            }

            $data = array(
                'id_card'     => $id_card,
                'id_deck'     => $id_deck,
                'id_version'  => $current_version->get_id(),
                'type'        => $type,
                'is_last'     => true,
            );

            if ($CI->db->insert('card_deck_version', $data)) {
                return true;
            } else {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error();
            }
        } else {
            $CI->db->where('id_card', $id_card)
                   ->where('id_deck', $id_deck)
                   ->where('id_version', $current_version->get_id());
            if ( ! $CI->db->delete('card_deck_version')) {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error();
            }

            if (self::set_last_move($id_card, $id_deck, true)) {
                return true;
            } else {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error();
            }
        }
    }
}

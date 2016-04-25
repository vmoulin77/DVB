<?php

class Campaign extends MY_Model
{
    private $id;
    private $name;
    private $created_at;

    private $review_records = array();
    
    public static function make($id, $name, $created_at) {
        $retour = new self();

        $retour->id          = $id;
        $retour->name        = $name;
        $retour->created_at  = $created_at;

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

    public function get_name() {
        return $this->name;
    }
    public function set_name($name) {
        $this->name = $name;
    }

    public function get_created_at() {
        return $this->created_at;
    }
    public function set_created_at($created_at) {
        $this->created_at = $created_at;
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

        $CI->db->select('id, name, created_at')
               ->from('campaign');

        $finder_manager->complete_query();

        $query = $CI->db->get();

        $retour = array();

        foreach ($query->result() as $row) {
            $campaign = self::make(
                (int) $row->id,
                $row->name,
                new DateTime($row->created_at)
            );

            $retour[] = $campaign;
        }

        $finder_manager->exec_withers($retour);

        return $retour;
    }
    /********************************************************/

    /********************************************************/
    /*                    The withers                       */
    /********************************************************/
    public function with_next_id_card() {
        $this->db->select('id_card')
                 ->from('campaign_card')
                 ->where('id_campaign', $this->id)
                 ->where('is_done', false)
                 ->limit(1);

        $query = $this->db->get();
        if ($query->num_rows() == 0) {
            $this->next_id_card = null;
        } else {
            $row = $query->row();
            $this->next_id_card = $row->id_card;
        }

        return $this->next_id_card;
    }
    /********************************************************/

    /********************************************************/
    /*                   The modifiers                      */
    /********************************************************/
    public static function insert($name, $id_deck) {
        $CI = get_instance();

        $CI->load->model('Deck');

        if (Deck::deck_is_deleted($id_deck)) {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error('INSERT_ERROR', "The deck has been deleted.");
        }

        if (Deck::deck_is_empty($id_deck)) {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error('INSERT_ERROR', "The deck is empty.");
        }

        $now = new DateTime();

        $data = array(
            'name'        => $name,
            'created_at'  => $now->format('Y-m-d H:i:s'),
        );

        if ( ! $CI->db->insert('campaign', $data)) {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error();
        }

        $id_campaign = $CI->db->insert_id();

        $CI->db->select('id_card')
               ->from('card_deck_version')
               ->where('id_deck', $id_deck)
               ->where('is_last', true)
               ->where('type', 'add');

        $query = $CI->db->get();
        $data = array();
        foreach ($query->result() as $row) {
            $data[] = array(
                'id_campaign'  => $id_campaign,
                'id_card'      => $row->id_card,
                'is_done'      => false,
                'review_date'  => null,
            );
        }
        if ($CI->db->insert_batch('campaign_card', $data)) {
            return true;
        } else {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error();
        }
    }

    public static function update($id, $data) {
        $CI = get_instance();

        if (self::campaign_is_deleted($id)) {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error('UPDATE_ERROR', "The campaign doesn't exist anymore.");
        }

        $CI->db->set($data)
               ->where('id', $id);

        if ($CI->db->update('campaign')) {
            return true;
        } else {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error();
        }
    }

    public static function delete($id) {
        $CI = get_instance();

        $CI->db->where('id', $id);
        if ($CI->db->delete('campaign')) {
            if ($CI->db->affected_rows() == 1) {
                return true;
            } else {
                return new utils\errors\DVB_Error('DELETE_ERROR', "The campaign doesn't exist anymore.");
            }
        } else {
            return new utils\errors\DVB_Error();
        }
    }
    /********************************************************/

    public static function campaign_is_deleted($id) {
        $CI = get_instance();

        $CI->db->from('campaign')
               ->where('id', $id);
        if ($CI->db->count_all_results() == 0) {
            return true;
        } else {
            return false;
        }
    }
}

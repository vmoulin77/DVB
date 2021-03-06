<?php

use utils\errors\Standard_error;

class Review_record extends MY_Model
{
    private $is_done;
    private $card_is_modified;
    private $review_date;

    private $campaign;
    private $card;
    
    public static function make($is_done, $card_is_modified, $review_date) {
        $retour = new self();

        $retour->is_done = $is_done;
        $retour->card_is_modified = $card_is_modified;
        $retour->review_date = $review_date;

        return $retour;
    }

    /********************************************************/
    /*                 The getters/setters                  */
    /********************************************************/
    public function get_is_done() {
        return $this->is_done;
    }
    public function set_is_done($is_done) {
        $this->is_done = $is_done;
    }

    public function get_card_is_modified() {
        return $this->card_is_modified;
    }
    public function set_card_is_modified($card_is_modified) {
        $this->card_is_modified = $card_is_modified;
    }

    public function get_review_date() {
        return $this->review_date;
    }
    public function set_review_date($review_date) {
        $this->review_date = $review_date;
    }

    public function get_campaign() {
        return $this->campaign;
    }
    public function set_campaign($campaign) {
        $this->campaign = $campaign;
    }

    public function get_card() {
        return $this->card;
    }
    public function set_card($card) {
        $this->card = $card;
    }
    /********************************************************/

    /********************************************************/
    /*                    The finders                       */
    /********************************************************/
    /********************************************************/

    /********************************************************/
    /*                   The retrievers                     */
    /********************************************************/
    /********************************************************/

    /********************************************************/
    /*                    The withers                       */
    /********************************************************/
    /********************************************************/

    /********************************************************/
    /*                   The modifiers                      */
    /********************************************************/
    /********************************************************/

    public static function review_record_is_deleted($id_campaign, $id_card) {
        $CI = get_instance();

        $CI->db->from('campaign_card')
               ->where('id_campaign', $id_campaign)
               ->where('id_card', $id_card);

        if ($CI->db->count_all_results() == 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function review_record_is_done($id_campaign, $id_card) {
        $CI = get_instance();

        $CI->db->select('is_done AS campaign_card:is_done')
               ->from('campaign_card')
               ->where('id_campaign', $id_campaign)
               ->where('id_card', $id_card);

        $query = $CI->db->get();
        if ($query->num_rows() == 0) {
            return new Standard_error('ERROR', "The review record doesn't exist anymore.");
        }

        $row = $query->row();
        cast_row($row);

        return $row->{'campaign_card:is_done'};
    }
}

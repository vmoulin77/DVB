<?php

class Card_content extends CI_Model
{
    private $id;
    private $word_english;
    private $word_french;
    private $is_active_english;
    private $is_active_french;
    private $is_last;

    private $card;
    private $version;

    public static function make($id, $word_english, $word_french, $is_active_english, $is_active_french, $is_last) {
        $retour = new self();

        $retour->id = $id;
        $retour->word_english = $word_english;
        $retour->word_french = $word_french;
        $retour->is_active_english = $is_active_english;
        $retour->is_active_french = $is_active_french;
        $retour->is_last = $is_last;

        return $retour;
    }

    public function get_id() {
        return $this->id;
    }
    public function set_id($id) {
        $this->id = $id;
    }

    public function get_word_english() {
        return $this->word_english;
    }
    public function set_word_english($word_english) {
        $this->word_english = $word_english;
    }

    public function get_word_french() {
        return $this->word_french;
    }
    public function set_word_french($word_french) {
        $this->word_french = $word_french;
    }

    public function get_is_active_english() {
        return $this->is_active_english;
    }
    public function set_is_active_english($is_active_english) {
        $this->is_active_english = $is_active_english;
    }

    public function get_is_active_french() {
        return $this->is_active_french;
    }
    public function set_is_active_french($is_active_french) {
        $this->is_active_french = $is_active_french;
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

    public function get_version() {
        return $this->version;
    }
    public function set_version($version) {
        $this->version = $version;
    }

    /********************************************************/

    public function with_version() {
        $this->load->model('Version');

        $current_version = Version::get_current_version();

        $this->db->select('version.id, version.database_version, version.app_version_code, version.app_version_name, version.created_at')
                 ->from('card_content')
                 ->join('version', 'version.id = card_content.id_version')
                 ->where('card_content.id', $this->id);
        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            $row = $query->row();

            if ($row->id == $current_version->get_id()) {
                $this->set_version($current_version);
            } else {
                $version = Version::make(
                    (int) $row->id,
                    (int) $row->database_version,
                    (int) $row->app_version_code,
                    $row->app_version_name,
                    new DateTime($row->created_at)
                );

                $this->set_version($version);
            }

            return $this;
        } else {
            return new utils\errors\DVB_Error();
        }
    }

    public static function get_by_id($id, $return_format = 'standard') {
        $CI = get_instance();

        $CI->db->select('id, word_english, word_french, is_active_english, is_active_french, is_last')
               ->from('card_content')
               ->where('id', $id);
        $query = $CI->db->get();

        if ($query->num_rows() == 1) {
            $row = $query->row();
            
            if ($return_format == 'standard') {
                $card_content_id                 = (int) $row->id;
                $card_content_is_active_english  = (bool) $row->is_active_english;
                $card_content_is_active_french   = (bool) $row->is_active_french;
                $card_content_is_last            = (bool) $row->is_last;
            } elseif ($return_format == 'string') {
                $card_content_id                 = $row->id;
                $card_content_is_active_english  = $row->is_active_english;
                $card_content_is_active_french   = $row->is_active_french;
                $card_content_is_last            = $row->is_last;
            }

            return self::make(
                $card_content_id,
                $row->word_english,
                $row->word_french,
                $card_content_is_active_english,
                $card_content_is_active_french,
                $card_content_is_last
            );
        } else {
            return false;
        }
    }
}
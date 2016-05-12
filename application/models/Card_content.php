<?php

use utils\errors\DVB_error;

class Card_content extends MY_Model
{
    private $id;
    private $word_english;
    private $word_french;
    private $is_active_english;
    private $is_active_french;
    private $is_last;

    private $card;
    private $version;

    public static function make($id, $word_english, $word_french, $is_active_english, $is_active_french, $is_last, $make_type = MAKE_STANDARD) {
        if ($make_type === MAKE_STR_DB) {
            $id = (int) $id;
            // $word_english = $word_english;
            // $word_french = $word_french;
            $is_active_english = (bool) $is_active_english;
            $is_active_french = (bool) $is_active_french;
            $is_last = (bool) $is_last;
        }

        $retour = new self();

        $retour->id = $id;
        $retour->word_english = $word_english;
        $retour->word_french = $word_french;
        $retour->is_active_english = $is_active_english;
        $retour->is_active_french = $is_active_french;
        $retour->is_last = $is_last;

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

    /********************************************************/
    /*                    The finders                       */
    /********************************************************/
    public static function find($filter = null) {
        $CI = get_instance();

        $finder_manager = init_finder_manager(__CLASS__, __METHOD__, $filter);

        $CI->db->select('id, word_english, word_french, is_active_english, is_active_french, is_last')
               ->from('card_content');

        $finder_manager->complete_query();

        $query = $CI->db->get();

        $retour = array();

        foreach ($query->result() as $row) {
            $retour[] = self::make(
                $row->id,
                $row->word_english,
                $row->word_french,
                $row->is_active_english,
                $row->is_active_french,
                $row->is_last,
                MAKE_STR_DB
            );
        }

        return $finder_manager->format_return($retour);
    }
    /********************************************************/

    /********************************************************/
    /*                   The retrievers                     */
    /********************************************************/
    /********************************************************/

    /********************************************************/
    /*                    The withers                       */
    /********************************************************/
    public function with_version() {
        $this->load->model('Version');

        $this->db->select('version.id, version.database_version, version.app_version_code, version.app_version_name, version.created_at')
                 ->from('card_content')
                 ->join('version', 'version.id = card_content.id_version')
                 ->where('card_content.id', $this->id);

        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            $row = $query->row();

            $version = Version::make(
                $row->id,
                $row->database_version,
                $row->app_version_code,
                $row->app_version_name,
                $row->created_at,
                MAKE_STR_DB
            );

            $this->set_version($version);

            return $this;
        } else {
            return new DVB_error();
        }
    }
    /********************************************************/

    /********************************************************/
    /*                   The modifiers                      */
    /********************************************************/
    /********************************************************/
}

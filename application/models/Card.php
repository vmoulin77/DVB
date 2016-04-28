<?php

class Card extends MY_Model
{
    private $id;
    private $num;
    private $card_content;
    private $is_deleted;

    private $version_when_deleted;
    private $card_moves = array();
    private $card_contents_history = array();
    private $review_records = array();
    
    public static function make($id, $num, $card_content, $is_deleted) {
        $retour = new self();

        $retour->id = $id;
        $retour->num = $num;
        $retour->card_content = $card_content;
        $card_content->set_card($retour);
        $retour->is_deleted = $is_deleted;

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

    public function get_card_content() {
        return $this->card_content;
    }
    public function set_card_content($card_content) {
        $this->card_content = $card_content;
        $card_content->set_card($this);
    }

    public function get_is_deleted() {
        return $this->is_deleted;
    }
    public function set_is_deleted($is_deleted) {
        $this->is_deleted = $is_deleted;
    }

    public function get_version_when_deleted() {
        return $this->version_when_deleted;
    }
    public function set_version_when_deleted($version_when_deleted) {
        $this->version_when_deleted = $version_when_deleted;
    }

    public function get_card_moves() {
        return $this->card_moves;
    }
    public function set_card_moves($card_moves) {
        $this->card_moves = $card_moves;
    }

    public function get_card_contents_history() {
        return $this->card_contents_history;
    }
    public function set_card_contents_history($card_contents_history) {
        $this->card_contents_history = $card_contents_history;
    }

    public function get_review_records() {
        return $this->review_records;
    }
    public function set_review_records($review_records) {
        $this->review_records = $review_records;
    }
    /********************************************************/

    /********************************************************/
    /*                    The finders                       */
    /********************************************************/
    public static function find_all(utils\crud\Finder_manager $finder_manager = null) {
        $CI = get_instance();

        $CI->load->model('Card_content');

        if ($finder_manager === null) {
            $finder_manager = new utils\crud\Finder_manager(get_class());
        }

        $CI->db->select('card.id as card_id, card.num, card.is_deleted, card_content.id as card_content_id')
               ->from('card')
               ->join('card_content', 'card_content.id_card = card.id')
               ->where('card_content.is_last', true);

        $finder_manager->complete_query();

        $query = $CI->db->get();

        $retour = array();

        foreach ($query->result() as $row) {
            $card = self::make(
                (int) $row->card_id,
                (int) $row->num,
                Card_content::find($row->card_content_id),
                (bool) $row->is_deleted
            );

            $retour[] = $card;
        }

        $finder_manager->exec_withers($retour);

        return $retour;
    }

    public static function find_searched_cards($searched_str, $is_case_sensitive, $language, $state) {
        $CI = get_instance();

        $CI->load->model('Card_content');

        $retour = array();

        $CI->db->select('card.id as card_id, card.num, card.is_deleted, card_content.id as card_content_id, card_content.word_english, card_content.word_french, card_content.is_active_english, card_content.is_active_french');
        $CI->db->from('card');
        $CI->db->join('card_content', 'card.id = card_content.id_card');
        $CI->db->where('card_content.is_last', true);

        if ($state === 'deleted') {
            $CI->db->where('card.is_deleted', true);
        } elseif ($state === 'not_deleted') {
            $CI->db->where('card.is_deleted', false);
        }

        $query = $CI->db->get();

        $search_in_en = ($language == 'only_en') || ($language == 'both');
        $search_in_fr = ($language == 'only_fr') || ($language == 'both');

        foreach ($query->result() as $row) {
            if (($searched_str === '')
                || ($search_in_en && self::str_match($searched_str, $row->word_english, $is_case_sensitive))
                || ($search_in_fr && self::str_match($searched_str, $row->word_french, $is_case_sensitive))
            ) {
                $card_content = Card_content::make(
                    (int) $row->card_content_id,
                    $row->word_english,
                    $row->word_french,
                    (bool) $row->is_active_english,
                    (bool) $row->is_active_french,
                    true
                );
                $retour[] = self::make(
                    (int) $row->card_id,
                    (int) $row->num,
                    $card_content,
                    (bool) $row->is_deleted
                );
            }
        }

        return $retour;
    }
    /********************************************************/

    /********************************************************/
    /*                    The withers                       */
    /********************************************************/
    public function with_version_when_deleted() {
        $this->load->model('Version');

        if ($this->is_deleted) {
            $current_version = Version::find_current_version();

            $this->db->select('version.id, version.database_version, version.app_version_code, version.app_version_name, version.created_at')
                     ->from('card')
                     ->join('version', 'version.id = card.id_version_when_deleted')
                     ->where('card.id', $this->id);

            $query = $this->db->get();
            $row = $query->row();

            if ($query->num_rows() == 1) {
                if ($row->id == $current_version->get_id()) {
                    $version = $current_version;
                } else {
                    $version = Version::make(
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
        } else {
            $version = null;
        }

        $this->set_version_when_deleted($version);
        
        return $this->version_when_deleted;
    }

    public function with_card_contents_history() {
        $this->load->model('Card_content');

        $this->db->select('card_content.id AS card_content_id, card_content.word_english, card_content.word_french, card_content.is_active_english, card_content.is_active_french, card_content.is_last')
                 ->from('card')
                 ->join('card_content', 'card.id = card_content.id_card')
                 ->where('card.id', $this->id);

        $query = $this->db->get();

        $card_contents_history = array();

        foreach ($query->result() as $row) {
            $card_content = Card_content::make(
                (int) $row->card_content_id,
                $row->word_english,
                $row->word_french,
                (bool) $row->is_active_english,
                (bool) $row->is_active_french,
                (bool) $row->is_last
            );

            $card_contents_history[] = $card_content;
        }

        $this->set_card_contents_history($card_contents_history);

        return $this->card_contents_history;
    }

    public function with_card_content_version() {
        return $this->card_content->with_version();
    }
    /********************************************************/

    /********************************************************/
    /*                   The modifiers                      */
    /********************************************************/
    public static function insert($num, $word_english, $word_french, $is_active_english, $is_active_french) {
        $CI = get_instance();

        $CI->load->model('Version');

        if ( ! self::num_is_free($num)) {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error('INSERT_ERROR', 'The card number is not free.');
        }

        $data = array(
            'num'                      => $num,
            'is_deleted'               => false,
            'id_version_when_deleted'  => null,
        );
        if ($CI->db->insert('card', $data)) {
            $id = $CI->db->insert_id();
        } else {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error();
        }

        $data = array(
            'word_english'       => self::convert_word($word_english),
            'word_french'        => self::convert_word($word_french),
            'is_active_english'  => $is_active_english,
            'is_active_french'   => $is_active_french,
            'is_last'            => true,
            'id_version'         => Version::find_current_version()->get_id(),
            'id_card'            => $id,
        );
        if ($CI->db->insert('card_content', $data)) {
            return true;
        } else {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error();
        }
    }

    public static function update($id, $data, $id_campaign = null) {
        $CI = get_instance();

        $CI->load->model('Version');

        if (self::card_is_deleted($id)) {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error('UPDATE_ERROR', 'The card has been deleted.');
        }

        if ($id_campaign !== null) {
            $CI->load->model('Campaign');
            $CI->load->model('Review_record');

            if (Campaign::campaign_is_deleted($id_campaign)) {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error('UPDATE_ERROR', 'The campaign has been deleted.');
            }

            if (Review_record::review_record_is_deleted($id_campaign, $id)) {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error('UPDATE_ERROR', "The review record doesn't exist anymore.");
            }

            if (Review_record::review_record_is_done($id_campaign, $id)) {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error('UPDATE_ERROR', 'The card has already been reviewed.');
            }

            $CI->db->set('is_done', true)
                   ->where('id_campaign', $id_campaign)
                   ->where('id_card', $id);
            if ( ! $CI->db->update('campaign_card')) {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error();
            }
        }

        $card = self::find($id);

        if (isset($data['num'])
            && ($data['num'] != $card->num)
        ) {
            if (self::num_is_free($data['num'])) {
                $CI->db->set('num', $data['num'])
                       ->where('id', $id);
                if ( ! $CI->db->update('card')) {
                    $CI->transaction->set_as_rollback();
                    return new utils\errors\DVB_Error();
                }
            } else {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error('UPDATE_ERROR', 'The card number is not free.');
            }
        }
        
        $card->get_card_content()->with_version();
        $current_version = Version::find_current_version();

        $data_card_content = array(
            'word_english'       => self::convert_word($data['word_english']),
            'word_french'        => self::convert_word($data['word_french']),
            'is_active_english'  => $data['is_active_english'],
            'is_active_french'   => $data['is_active_french'],
        );

        if ($card->get_card_content()->get_version()->get_id() == $current_version->get_id()) {
            $CI->db->set($data_card_content)
                   ->where('id', $card->get_card_content()->get_id());
            if ($CI->db->update('card_content')) {
                return true;
            } else {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error();
            }
        } else {
            $CI->db->set(array('is_last' => false))
                   ->where('id', $card->get_card_content()->get_id());
            if ( ! $CI->db->update('card_content')) {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error();
            }

            $data_card_content['is_last']     = true;
            $data_card_content['id_version']  = $current_version->get_id();
            $data_card_content['id_card']     = $id;

            if ($CI->db->insert('card_content', $data_card_content)) {
                return true;
            } else {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error();
            }
        }
    }

    public static function delete($id) {
        $CI = get_instance();

        $CI->load->model('Card_move');
        $CI->load->model('Version');
        $CI->load->model('Deck');

        if (self::card_is_deleted($id)) {
            $CI->transaction->set_as_rollback();
            return new utils\errors\DVB_Error('DELETE_ERROR', 'The card has already been deleted.');
        }

        if (self::never_versioned($id)) {
            $CI->db->where('id', $id);
            if ($CI->db->delete('card')) {
                return true;
            } else {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error();
            }
        } else {
            $current_version = Version::find_current_version();

            /********************************************************/
            $CI->db->where('id_card', $id);
            if ( ! $CI->db->delete('campaign_card')) {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error();
            }
            /********************************************************/

            /********************************************************/
            $CI->db->select('id_deck')
                   ->from('card_deck_version')
                   ->where('id_card', $id)
                   ->where('id_version', $current_version->get_id())
                   ->where('type', 'add');
            $query = $CI->db->get();
            $ids_decks = array();
            foreach ($query->result() as $row) {
                $ids_decks[] = $row->id_deck;
            }

            $CI->db->where('id_card', $id)
                   ->where('id_version', $current_version->get_id())
                   ->where('type', 'add');
            if ( ! $CI->db->delete('card_deck_version')) {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error();
            }

            foreach ($ids_decks as $id_deck) {
                if ( ! Card_move::set_last_move($id, $id_deck, true)) {
                    $CI->transaction->set_as_rollback();
                    return new utils\errors\DVB_Error();
                }
            }
            /********************************************************/

            /********************************************************/
            $decks = Deck::find_all_with_contains_current_card($id);
            foreach ($decks as $deck) {
                if ($deck->contains_current_card) {
                    if ( ! Card_move::set_last_move($id, $deck->get_id(), false)) {
                        $CI->transaction->set_as_rollback();
                        return new utils\errors\DVB_Error();
                    }

                    $data = array(
                        'id_card'     => $id,
                        'id_deck'     => $deck->get_id(),
                        'id_version'  => $current_version->get_id(),
                        'type'        => 'remove',
                        'is_last'     => true,
                    );
                    if ( ! $CI->db->insert('card_deck_version', $data)) {
                        $CI->transaction->set_as_rollback();
                        return new utils\errors\DVB_Error();
                    }
                }
            }
            /********************************************************/

            $CI->db->where('id_card', $id)
                   ->where('id_version', $current_version->get_id());
            if ( ! $CI->db->delete('card_content')) {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error();
            }

            $CI->db->select('id')
                   ->from('card_content')
                   ->where('id_card', $id)
                   ->order_by('id_version', 'DESC')
                   ->limit(1);
            $query = $CI->db->get();

            if ($query->num_rows() == 0) {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error();
            } else {
                $row = $query->row();

                $CI->db->set(array('is_last' => true))
                       ->where('id', $row->id);
                if ( ! $CI->db->update('card_content')) {
                    $CI->transaction->set_as_rollback();
                    return new utils\errors\DVB_Error();
                }
            }

            $data = array(
                'is_deleted'               => true,
                'id_version_when_deleted'  => $current_version->get_id(),
            );
            $CI->db->set($data)
                   ->where('id', $id);

            if ($CI->db->update('card')) {
                return true;
            } else {
                $CI->transaction->set_as_rollback();
                return new utils\errors\DVB_Error();
            }
        }
    }
    /********************************************************/

    public static function get_max_num() {
        $CI = get_instance();

        $CI->db->select_max('num', 'max_num');
        $query = $CI->db->get('card');
        $row = $query->row();

        $retour = (int) $row->max_num;
        return $retour;
    }

    private static function convert_word($word) {
        $retour = $word;
        $retour = str_replace("\r\n", "\n", $retour);
        $retour = str_replace(
            array("\r", "\n"),
            "<br />",
            $retour
        );

        return $retour;
    }

    public static function num_is_free($num) {
        $CI = get_instance();

        $CI->db->from('card')
               ->where('num', $num)
               ->where('is_deleted', false);
        if ($CI->db->count_all_results() == 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function card_is_deleted($id) {
        $CI = get_instance();

        $CI->db->from('card')
               ->where('id', $id)
               ->where('is_deleted', false);
        if ($CI->db->count_all_results() == 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function never_versioned($id) {
        $CI = get_instance();

        $str_query = "SELECT COUNT(*) AS count "
                   . "FROM card_content "
                   . "WHERE id_card = " . $id . " "
                   . "AND id_version != (SELECT MAX(id) FROM version)";
        $query = $CI->db->query($str_query);

        $row = $query->row();

        if ($row->count == 0) {
            return true;
        } else {
            return false;
        }
    }

    private static function str_match($searched_str, $word, $is_case_sensitive) {
        $needle = preg_quote($searched_str, '/');
        $needle = str_replace('\\*', '.*', $needle);
        $needle = '/' . $needle . '/';
        if ( ! $is_case_sensitive) {
            $needle .= 'i';
        }

        $haystack = str_replace(
            array(
                '<br />',
                '<b>',
                '</b>',
                '<small>',
                '</small>',
            ),
            array(
                ' ',
                '',
                '',
                '',
                '',
            ),
            $word
        );

        $retour = (bool) preg_match($needle, $haystack);

        return $retour;
    }
}

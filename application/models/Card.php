<?php

use utils\errors\Standard_error;
use utils\crud\Finder_manager;

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

    public static function make($id, $num, Card_content $card_content, $is_deleted) {
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
    public static function find($filter = null) {
        $CI = get_instance();

        $CI->load->model('Card_content');

        $finder_manager = init_finder_manager(__CLASS__, __METHOD__, $filter);

        $CI->db
            ->select(
                'card.id AS card:id,'
                . 'card.num AS card:num,'
                . 'card.is_deleted AS card:is_deleted,'
                . 'card_content.id AS card_content:id,'
                . 'card_content.word_english AS card_content:word_english,'
                . 'card_content.word_french AS card_content:word_french,'
                . 'card_content.is_active_english AS card_content:is_active_english,'
                . 'card_content.is_active_french AS card_content:is_active_french'
            )
            ->from('card')
            ->join('card_content', 'card_content.id_card = card.id')
            ->where('card_content.is_last', true);

        $finder_manager->complete_query();

        $query = $CI->db->get();

        $retour = array();

        foreach ($query->result() as $row) {
            cast_row($row);

            $card_content = Card_content::make(
                $row->{'card_content:id'},
                $row->{'card_content:word_english'},
                $row->{'card_content:word_french'},
                $row->{'card_content:is_active_english'},
                $row->{'card_content:is_active_french'},
                true
            );

            $retour[] = self::make(
                $row->{'card:id'},
                $row->{'card:num'},
                $card_content,
                $row->{'card:is_deleted'}
            );
        }

        return $finder_manager->format_return($retour);
    }
    /********************************************************/

    /********************************************************/
    /*                   The retrievers                     */
    /********************************************************/
    public static function retrieve_searched_cards($searched_str, $is_case_sensitive, $language, $state) {
        $CI = get_instance();

        $CI->load->model('Card_content');

        $retour = array();

        $CI->db
            ->select(
                'card.id AS card:id,'
                . 'card.num AS card:num,'
                . 'card.is_deleted AS card:is_deleted,'
                . 'card_content.id AS card_content:id,'
                . 'card_content.word_english AS card_content:word_english,'
                . 'card_content.word_french AS card_content:word_french,'
                . 'card_content.is_active_english AS card_content:is_active_english,'
                . 'card_content.is_active_french AS card_content:is_active_french'
            )
            ->from('card')
            ->join('card_content', 'card_content.id_card = card.id')
            ->where('card_content.is_last', true);

        if ($state === 'deleted') {
            $CI->db->where('card.is_deleted', true);
        } elseif ($state === 'not_deleted') {
            $CI->db->where('card.is_deleted', false);
        }

        $query = $CI->db->get();

        $search_in_en = ($language == 'only_en') || ($language == 'both');
        $search_in_fr = ($language == 'only_fr') || ($language == 'both');

        foreach ($query->result() as $row) {
            cast_row($row);

            if (($searched_str === '')
                || ($search_in_en && self::str_match($searched_str, $row->{'card_content:word_english'}, $is_case_sensitive))
                || ($search_in_fr && self::str_match($searched_str, $row->{'card_content:word_french'}, $is_case_sensitive))
            ) {
                $card_content = Card_content::make(
                    $row->{'card_content:id'},
                    $row->{'card_content:word_english'},
                    $row->{'card_content:word_french'},
                    $row->{'card_content:is_active_english'},
                    $row->{'card_content:is_active_french'},
                    true
                );
                $retour[] = self::make(
                    $row->{'card:id'},
                    $row->{'card:num'},
                    $card_content,
                    $row->{'card:is_deleted'}
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
            $this->db
                ->select(
                    'version.id AS version:id,'
                    . 'version.database_version AS version:database_version,'
                    . 'version.app_version_code AS version:app_version_code,'
                    . 'version.app_version_name AS version:app_version_name,'
                    . 'version.created_at AS version:created_at'
                )
                ->from('card')
                ->join('version', 'version.id = card.id_version_when_deleted')
                ->where('card.id', $this->id);

            $query = $this->db->get();

            if ($query->num_rows() == 1) {
                $row = $query->row();
                cast_row($row);

                $version = Version::make(
                    $row->{'version:id'},
                    $row->{'version:database_version'},
                    $row->{'version:app_version_code'},
                    $row->{'version:app_version_name'},
                    $row->{'version:created_at'}
                );
            } else {
                return new Standard_error();
            }
        } else {
            $version = null;
        }

        $this->set_version_when_deleted($version);
        
        return $this;
    }

    public function with_card_contents_history() {
        $this->load->model('Card_content');

        $this->db
            ->select(
                'card_content.id AS card_content:id,'
                . 'card_content.word_english AS card_content:word_english,'
                . 'card_content.word_french AS card_content:word_french,'
                . 'card_content.is_active_english AS card_content:is_active_english,'
                . 'card_content.is_active_french AS card_content:is_active_french,'
                . 'card_content.is_last AS card_content:is_last'
            )
            ->from('card')
            ->join('card_content', 'card.id = card_content.id_card')
            ->where('card.id', $this->id);

        $query = $this->db->get();

        $card_contents_history = array();

        foreach ($query->result() as $row) {
            cast_row($row);

            $card_content = Card_content::make(
                $row->{'card_content:id'},
                $row->{'card_content:word_english'},
                $row->{'card_content:word_french'},
                $row->{'card_content:is_active_english'},
                $row->{'card_content:is_active_french'},
                $row->{'card_content:is_last'}
            );

            $card_contents_history[] = $card_content;
        }

        $this->set_card_contents_history($card_contents_history);

        return $this;
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
            return new Standard_error('INSERT_ERROR', 'The card number is not free.');
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
            return new Standard_error();
        }

        $data = array(
            'word_english'       => self::convert_word($word_english),
            'word_french'        => self::convert_word($word_french),
            'is_active_english'  => $is_active_english,
            'is_active_french'   => $is_active_french,
            'is_last'            => true,
            'id_version'         => Version::retrieve_current_version()->get_id(),
            'id_card'            => $id,
        );
        if ($CI->db->insert('card_content', $data)) {
            return true;
        } else {
            $CI->transaction->set_as_rollback();
            return new Standard_error();
        }
    }

    public static function update($id, $data, $id_campaign = null) {
        $CI = get_instance();

        $CI->load->model('Version');

        if (self::card_is_deleted($id)) {
            $CI->transaction->set_as_rollback();
            return new Standard_error('UPDATE_ERROR', 'The card has been deleted.');
        }

        if ($id_campaign !== null) {
            $CI->load->model('Campaign');
            $CI->load->model('Review_record');

            if (Campaign::campaign_is_deleted($id_campaign)) {
                $CI->transaction->set_as_rollback();
                return new Standard_error('UPDATE_ERROR', 'The campaign has been deleted.');
            }

            if (Review_record::review_record_is_deleted($id_campaign, $id)) {
                $CI->transaction->set_as_rollback();
                return new Standard_error('UPDATE_ERROR', "The review record doesn't exist anymore.");
            }

            if (Review_record::review_record_is_done($id_campaign, $id)) {
                $CI->transaction->set_as_rollback();
                return new Standard_error('UPDATE_ERROR', 'The card has already been reviewed.');
            }

            $CI->db->set('is_done', true)
                   ->where('id_campaign', $id_campaign)
                   ->where('id_card', $id);
            if ( ! $CI->db->update('campaign_card')) {
                $CI->transaction->set_as_rollback();
                return new Standard_error();
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
                    return new Standard_error();
                }
            } else {
                $CI->transaction->set_as_rollback();
                return new Standard_error('UPDATE_ERROR', 'The card number is not free.');
            }
        }
        
        $card->get_card_content()->with_version();
        $current_version = Version::retrieve_current_version();

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
                return new Standard_error();
            }
        } else {
            $CI->db->set(array('is_last' => false))
                   ->where('id', $card->get_card_content()->get_id());
            if ( ! $CI->db->update('card_content')) {
                $CI->transaction->set_as_rollback();
                return new Standard_error();
            }

            $data_card_content['is_last']     = true;
            $data_card_content['id_version']  = $current_version->get_id();
            $data_card_content['id_card']     = $id;

            if ($CI->db->insert('card_content', $data_card_content)) {
                return true;
            } else {
                $CI->transaction->set_as_rollback();
                return new Standard_error();
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
            return new Standard_error('DELETE_ERROR', 'The card has already been deleted.');
        }

        if (self::never_versioned($id)) {
            $CI->db->where('id', $id);
            if ($CI->db->delete('card')) {
                return true;
            } else {
                $CI->transaction->set_as_rollback();
                return new Standard_error();
            }
        } else {
            $current_version = Version::retrieve_current_version();

            /********************************************************/
            $CI->db->where('id_card', $id);
            if ( ! $CI->db->delete('campaign_card')) {
                $CI->transaction->set_as_rollback();
                return new Standard_error();
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
                return new Standard_error();
            }

            foreach ($ids_decks as $id_deck) {
                if ( ! Card_move::set_last_move($id, $id_deck, true)) {
                    $CI->transaction->set_as_rollback();
                    return new Standard_error();
                }
            }
            /********************************************************/

            /********************************************************/
            $finder_manager = new Finder_manager(
                'Deck',
                'find_with_contains_current_card',
                FIND_MANY,
                ['id_card' => $id]
            );
            $decks = $finder_manager->get();
            foreach ($decks as $deck) {
                if ($deck->contains_current_card) {
                    if ( ! Card_move::set_last_move($id, $deck->get_id(), false)) {
                        $CI->transaction->set_as_rollback();
                        return new Standard_error();
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
                        return new Standard_error();
                    }
                }
            }
            /********************************************************/

            $CI->db->where('id_card', $id)
                   ->where('id_version', $current_version->get_id());
            if ( ! $CI->db->delete('card_content')) {
                $CI->transaction->set_as_rollback();
                return new Standard_error();
            }

            $CI->db->select('id')
                   ->from('card_content')
                   ->where('id_card', $id)
                   ->order_by('id_version', 'DESC')
                   ->limit(1);
            $query = $CI->db->get();

            if ($query->num_rows() == 0) {
                $CI->transaction->set_as_rollback();
                return new Standard_error();
            } else {
                $row = $query->row();

                $CI->db->set(array('is_last' => true))
                       ->where('id', $row->id);
                if ( ! $CI->db->update('card_content')) {
                    $CI->transaction->set_as_rollback();
                    return new Standard_error();
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
                return new Standard_error();
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

<?php

class Export extends CI_Model
{
    private static function my_xml_entities($text) {
        $xml_entities = array("<" => "&lt;", ">" => "&gt;", "&" => "&amp;", "\"" => "&quot;", "'" => "&apos;");
        return strtr($text, $xml_entities);
    }

    public static function process() {
        $CI = get_instance();

        $retour = '<?xml version ="1.0" encoding="utf-8" ?>';
        $retour .= '<deck>';

        $CI->db->select('card.num as card_num, card_content.word_english, card_content.word_french, card_content.is_active_english, card_content.is_active_french')
                 ->from('deck')
                 ->join('card_deck_version', 'card_deck_version.id_deck = deck.id')
                 ->join('card', 'card.id = card_deck_version.id_card')
                 ->join('card_content', 'card_content.id_card = card.id')
                 ->where('deck.id', 1)
                 ->where('card_deck_version.is_last', true)
                 ->where('card_deck_version.type', 'add')
                 ->where('card_content.is_last', true);

        $query = $CI->db->get();
        foreach ($query->result() as $row) {
            $retour .= '<card>';
            $retour .= '<e1>' . $row->card_num . '</e1>';
            $retour .= '<e2>' . self::my_xml_entities($row->word_english) . '</e2>';
            $retour .= '<e3>' . self::my_xml_entities($row->word_french) . '</e3>';
            $retour .= '<e4>' . $row->is_active_english . '</e4>';
            $retour .= '<e5>' . $row->is_active_french . '</e5>';
            $retour .= '</card>';
        }

        $retour .= '</deck>';

        return $retour;
    }
}

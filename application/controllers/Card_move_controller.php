<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Card_move_controller extends CI_Controller
{
    public function ajax_move($type, $id_card, $id_deck) {
        if ( ! in_array($type, ['add', 'remove'])) {
            $ajax_response = new utils\ajax\responses\Standard('NOK', 'Incorrect request');
            echo $ajax_response->produce();
            return;
        }

        $this->load->model('Card_move');

        $result = Card_move::move($type, $id_card, $id_deck);

        if ($result === true) {
            $ajax_response = new utils\ajax\responses\Standard('OK', 'Your request has been processed.');
        } elseif ($result instanceof  utils\errors\DVB_error) {
            $ajax_response = new utils\ajax\responses\Standard('NOK', $result->message);
        }
        echo $ajax_response->produce();
        return;
    }
}

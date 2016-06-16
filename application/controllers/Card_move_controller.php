<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use utils\errors\Standard_error;
use utils\ajax\responses\Ajax_response_standard;

class Card_move_controller extends CI_Controller
{
    public function ajax_move($type, $id_card, $id_deck) {
        if ( ! in_array($type, ['add', 'remove'])) {
            $ajax_response = new Ajax_response_standard('NOK', 'Incorrect request');
            echo $ajax_response->produce();
            return;
        }

        $this->load->model('Card_move');

        $result = Card_move::move($type, $id_card, $id_deck);

        if ($result === true) {
            $ajax_response = new Ajax_response_standard('OK', 'Your request has been processed.');
        } elseif ($result instanceof  Standard_error) {
            $ajax_response = new Ajax_response_standard('NOK', $result->message);
        }
        echo $ajax_response->produce();
        return;
    }
}

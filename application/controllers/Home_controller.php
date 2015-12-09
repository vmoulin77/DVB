<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home_controller extends CI_Controller
{
    public function index() {
        $this->layout->add_basic_assets()
                     ->menu()
                     ->action_view();
    }
}

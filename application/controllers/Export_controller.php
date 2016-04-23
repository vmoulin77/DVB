<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Export_controller extends CI_Controller
{
    public function index() {
        $this->layout->add_basic_assets()
                     ->menu()
                     ->action_view();
    }

    public function process() {
        $this->output->set_content_type('text/xml');
        $this->output->set_header('Content-Disposition: attachment; filename="DVB_Export.xml"');
        $this->output->set_output($this->export->process());
    }
}

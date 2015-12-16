<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Version_controller extends CI_Controller
{
    public function index() {
        $this->layout->add_basic_assets()
                     ->menu()
                     ->action_view();
    }

    public function freeze() {
        //$this->output->enable_profiler(true);
        
        $this->load->model('Version');

        $this->form_validation->set_rules('database_version', 'Database version', 'required|integer');
        $this->form_validation->set_rules('app_version_code', 'Application version code', 'required|integer');
        $this->form_validation->set_rules('app_version_name', 'Application version name', 'required');

        $this->layout->add_basic_assets()
                     ->menu();

        if ($this->form_validation->run() === false) {
            $this->layout->action_view();
        } else {
            $result = Version::freeze(
                $this->input->post('database_version'),
                $this->input->post('app_version_code'),
                $this->input->post('app_version_name')
            );

            if ($result) {
                $this->layout->view('others/form_success');
            } else {
                $this->layout->view('others/form_failure');
            }
        }
    }

    public function compare() {
        //$this->output->enable_profiler(true);
        
        $this->load->model('Version');

        $this->form_validation->set_rules('version_from', 'Version', 'required');
        $this->form_validation->set_rules('version_to', 'Version', 'required');

        $data = array();

        if ($this->form_validation->run() === true) {
            $version_from  = Version::get_by_id($this->input->post('version_from'));
            $version_to    = Version::get_by_id($this->input->post('version_to'));

            if (($version_from === true)
                && ($version_to === true)
            ) {
                $comparison = $version_to->compare($version_from);

                $data['comparison'] = $comparison;
            }
        }

        $this->layout->add_basic_assets()
                     ->menu()
                     ->action_view($data);
    }
}

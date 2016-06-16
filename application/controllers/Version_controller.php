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

        $this->form_validation->set_rules('version_before', 'Version', 'required|integer');
        $this->form_validation->set_rules('version_after', 'Version', 'required|integer');

        $data = array();

        if ($this->form_validation->run() === true) {
            $version_before  = Version::find($this->input->post('version_before'));
            $version_after   = Version::find($this->input->post('version_after'));

            if (($version_before !== false)
                && ($version_after !== false)
            ) {
                $comparison = $version_after->compare_to($version_before);
/*
echo '<pre>';
var_dump($comparison);
echo '</pre>';
*/
                $data['comparison'] = $comparison;
            }
        }

        $this->layout->add_basic_assets()
                     ->menu()
                     ->action_view($data);
    }
}

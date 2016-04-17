<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Campaign_controller extends CI_Controller
{
    public function index() {
        $this->layout->add_basic_assets()
                     ->menu()
                     ->action_view();
    }

    public function create() {
        $this->load->model('Campaign');
        $this->load->model('Deck');

        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('id_deck', 'Deck', 'required');
        
        $this->layout->add_basic_assets()
                     ->menu();

        $decks = Deck::find_all();

        if ($this->form_validation->run() === false) {
            $data = array(
                'action'  => $this->router->method,
                'name'    => '',
                'decks'   => $decks,
            );

            $create_edit = $this->layout->views('controllers/Campaign/actions/create_edit', $data, array(), true);

            $this->layout->action_view(array('create_edit' => $create_edit));
        } else {
            $result = Campaign::insert(
                $this->input->post('name'),
                $this->input->post('id_deck')
            );

            if ($result === true) {
                $this->layout->view('others/form_success');
            } elseif ($result instanceof utils\errors\DVB_Error) {
                $this->layout->views('others/form_failure', array('message' => $result->message));

                $data = array(
                    'action'  => $this->router->method,
                    'name'    => $this->input->post('name'),
                    'decks'   => $decks,
                );
                $create_edit = $this->layout->views('controllers/Campaign/actions/create_edit', $data, array(), true);
                $this->layout->action_view(array('create_edit' => $create_edit));
            }
        }
    }

    public function edit($id) {
        //$this->output->enable_profiler(true);

        $id = (int) $id;

        $this->load->model('Campaign');

        $this->form_validation->set_rules('name', 'Name', 'required');
        
        $this->layout->add_basic_assets()
                     ->menu();

        if ($this->form_validation->run() === false) {
            $campaign = Campaign::find($id);

            $data = array(
                'action'  => $this->router->method,
                'id'      => $id,
                'name'    => $campaign->get_name(),
            );

            $create_edit = $this->layout->views('controllers/Campaign/actions/create_edit', $data, array(), true);

            $this->layout->action_view(array('create_edit' => $create_edit));
        } else {
            $data = array(
                'name' => $this->input->post('name'),
            );

            $result = Campaign::update(
                $id,
                $data
            );

            if ($result === true) {
                $this->layout->view('others/form_success');
            } elseif ($result instanceof utils\errors\DVB_Error) {
                $this->layout->views('others/form_failure', array('message' => $result->message));

                $data = array(
                    'action'  => $this->router->method,
                    'id'      => $id,
                    'name'    => $this->input->post('name'),
                );
                $create_edit = $this->layout->views('controllers/Campaign/actions/create_edit', $data, array(), true);
                $this->layout->action_view(array('create_edit' => $create_edit));
            }
        }
    }

    public function delete($id) {
        $id = (int) $id;
        
        $this->load->model('Campaign');

        $result = Campaign::delete($id);

        if ($result === true) {
            redirect('/Campaign/view_all');
        } elseif ($result instanceof utils\errors\DVB_Error) {
            $this->layout->add_basic_assets()
                         ->menu()
                         ->view('others/form_failure', array('message' => $result->message));
        }
    }

    public function view_all() {
        $this->load->model('Campaign');

        $campaigns = Campaign::find_all_with_next_id_card();

        $this->layout->add_basic_assets()
                     ->menu()
                     ->action_view(array('campaigns' => $campaigns));
    }
}

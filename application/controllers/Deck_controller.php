<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Deck_controller extends CI_Controller
{
    public function index() {
        $this->layout->add_basic_assets()
                     ->menu()
                     ->action_view();
    }
    
    public function create() {
        $this->load->model('Deck');

        $this->form_validation->set_rules('num', 'Number', 'required|integer|is_unique[deck.num]');
        $this->form_validation->set_rules('name', 'Name', 'required');
        
        $this->layout->add_basic_assets()
                     ->menu();

        if ($this->form_validation->run() === false) {
            $default_num = Deck::get_max_num() + 1;

            $data = array(
                'num'   => $default_num,
                'name'  => '',
            );

            $create_edit = $this->layout->views('controllers/Deck/actions/create_edit', $data, array(), true);

            $this->layout->action_view(array('create_edit' => $create_edit));
        } else {
            $result = Deck::insert(
                (int) $this->input->post('num'),
                $this->input->post('name')
            );

            if ($result === true) {
                $this->layout->view('others/form_success');
            } elseif ($result instanceof utils\errors\DVB_Error) {
                $this->layout->views('others/form_failure', array('message' => $result->message));

                $data = array(
                    'num'   => $this->input->post('num'),
                    'name'  => $this->input->post('name'),
                );
                $create_edit = $this->layout->views('controllers/Deck/actions/create_edit', $data, array(), true);
                $this->layout->action_view(array('create_edit' => $create_edit));
            }
        }
    }

    public function edit($id) {
        //$this->output->enable_profiler(true);

        $id = (int) $id;

        $this->load->model('Deck');

        $this->form_validation->set_rules('num', 'Number', 'required|integer');
        $this->form_validation->set_rules('name', 'Name', 'required');
        
        $this->layout->add_basic_assets()
                     ->menu();

        if ($this->form_validation->run() === false) {
            $deck = Deck::find($id);

            $data = array(
                'id'    => $id,
                'num'   => $deck->get_num(),
                'name'  => $deck->get_name(),
            );

            $create_edit = $this->layout->views('controllers/Deck/actions/create_edit', $data, array(), true);

            $this->layout->action_view(array('create_edit' => $create_edit));
        } else {
            $data = array(
                'num'   => (int) $this->input->post('num'),
                'name'  => $this->input->post('name'),
            );

            $result = Deck::update(
                $id,
                $data
            );

            if ($result === true) {
                $this->layout->view('others/form_success');
            } elseif ($result instanceof utils\errors\DVB_Error) {
                $this->layout->views('others/form_failure', array('message' => $result->message));

                $data = array(
                    'id'    => $id,
                    'num'   => $this->input->post('num'),
                    'name'  => $this->input->post('name'),
                );
                $create_edit = $this->layout->views('controllers/Deck/actions/create_edit', $data, array(), true);
                $this->layout->action_view(array('create_edit' => $create_edit));
            }
        }
    }

    public function delete($id) {
        $id = (int) $id;
        
        $this->load->model('Deck');

        $result = Deck::delete($id);

        if ($result === true) {
            redirect('/Deck/view_all');
        } elseif ($result instanceof utils\errors\DVB_Error) {
            $this->layout->add_basic_assets()
                         ->menu()
                         ->view('others/form_failure', array('message' => $result->message));
        }
    }

    public function view_all() {
        $this->output->enable_profiler(true);
        $this->load->model('Deck');

        $finder_manager = new utils\finder\Finder_manager();
        $finder_manager->model('Deck');
        $finder_manager->where('num', 2);
        $finder_manager->or_where('num', 1);
        $finder_manager->order_by('name', 'asc');
        $finder_manager->with('version_when_created');
        $decks = $finder_manager->find_all();

/*
        $deck = Deck::find(2);
        $deck->with_version_when_created();
        $decks = array();
        $decks[] = $deck;
*/
/*
echo '<pre>';
print_r($decks);
echo '</pre>';
*/
        //$decks = Deck::find_all_with_version_when_created();

        $this->layout->add_basic_assets()
                     ->menu()
                     ->action_view(array('decks' => $decks));
    }
}

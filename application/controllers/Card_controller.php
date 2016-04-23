<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Card_controller extends CI_Controller
{
    public function index() {
        $this->layout->add_basic_assets()
                     ->menu()
                     ->action_view();
    }

    public function create() {
        //$this->output->enable_profiler(true);

        $this->load->model('Card');

        $this->form_validation->set_rules('num', 'Number', 'required|integer');
        $this->form_validation->set_rules('word_english', 'English', 'required');
        $this->form_validation->set_rules('word_french', 'French', 'required');
        
        $this->layout->add_basic_assets()
                     ->menu();

        if ($this->form_validation->run() === false) {
            $default_num = Card::get_max_num() + 1;

            $data = array(
                'num'                => $default_num,
                'word_english'       => '',
                'word_french'        => '',
                'is_active_english'  => 1,
                'is_active_french'   => 1,
            );

            $create_edit = $this->layout->views('controllers/Card/actions/create_edit', $data, array('css', 'js'), true);

            $this->layout->action_view(array('create_edit' => $create_edit));
        } else {
            $result = Card::insert(
                (int) $this->input->post('num'),
                $this->input->post('word_english'),
                $this->input->post('word_french'),
                (bool) $this->input->post('is_active_english'),
                (bool) $this->input->post('is_active_french')
            );

            if ($result === true) {
                $this->layout->view('others/form_success');
            } elseif ($result instanceof utils\errors\DVB_Error) {
                $this->layout->views('others/form_failure', array('message' => $result->message));
                
                $data = array(
                    'num'                => $this->input->post('num'),
                    'word_english'       => $this->input->post('word_english'),
                    'word_french'        => $this->input->post('word_french'),
                    'is_active_english'  => $this->input->post('is_active_english'),
                    'is_active_french'   => $this->input->post('is_active_french'),
                );
                $create_edit = $this->layout->views('controllers/Card/actions/create_edit', $data, array('css', 'js'), true);
                $this->layout->action_view(array('create_edit' => $create_edit));
            }
        }
    }

    public function edit($id, $id_campaign = null) {
        //$this->output->enable_profiler(true);

        $id = (int) $id;

        $this->load->model('Card');

        $this->form_validation->set_rules('num', 'Number', 'required|integer');
        $this->form_validation->set_rules('word_english', 'English', 'required');
        $this->form_validation->set_rules('word_french', 'French', 'required');
        
        $this->layout->add_basic_assets()
                     ->menu();

        if ($this->form_validation->run() === false) {
            $card = Card::find($id);
            $data = array(
                'id'                 => $id,
                'num'                => $card->get_num(),
                'word_english'       => str_replace('<br />', "\n", $card->get_card_content()->get_word_english()),
                'word_french'        => str_replace('<br />', "\n", $card->get_card_content()->get_word_french()),
                'is_active_english'  => $card->get_card_content()->get_is_active_english(),
                'is_active_french'   => $card->get_card_content()->get_is_active_french(),
                'id_campaign'        => $id_campaign,
            );
            $create_edit = $this->layout->views('controllers/Card/actions/create_edit', $data, array('css', 'js'), true);
            $this->layout->action_view(array('create_edit' => $create_edit));
        } else {
            $data = array(
                'num'                => (int) $this->input->post('num'),
                'word_english'       => $this->input->post('word_english'),
                'word_french'        => $this->input->post('word_french'),
                'is_active_english'  => (bool) $this->input->post('is_active_english'),
                'is_active_french'   => (bool) $this->input->post('is_active_french'),
            );

            $result = Card::update(
                $id,
                $data,
                $id_campaign
            );

            if ($result === true) {
                if ($id_campaign === null) {
                    $this->layout->view('others/form_success');
                } else {
                    $this->load->model('Campaign');

                    $campaign = Campaign::find($id_campaign);
                    $campaign->with_next_id_card();
                    if ($campaign->next_id_card === null) {
                        $this->layout->view('controllers/Campaign/actions/completed');
                    } else {
                        redirect('/Card/edit/' . $campaign->next_id_card . '/' . $id_campaign);
                    }
                }
            } elseif ($result instanceof utils\errors\DVB_Error) {
                $this->layout->views('others/form_failure', array('message' => $result->message));

                $data = array(
                    'id'                 => $id,
                    'num'                => $this->input->post('num'),
                    'word_english'       => $this->input->post('word_english'),
                    'word_french'        => $this->input->post('word_french'),
                    'is_active_english'  => $this->input->post('is_active_english'),
                    'is_active_french'   => $this->input->post('is_active_french'),
                    'id_campaign'        => $id_campaign,
                );
                $create_edit = $this->layout->views('controllers/Card/actions/create_edit', $data, array('css', 'js'), true);
                $this->layout->action_view(array('create_edit' => $create_edit));
            }
        }
    }

    public function delete($id) {
        $id = (int) $id;
        
        $this->load->model('Card');

        $result = Card::delete($id);

        if ($result === true) {
            redirect('/Card/search');
        } elseif ($result instanceof utils\errors\DVB_Error) {
            $this->layout->add_basic_assets()
                         ->menu()
                         ->view('others/form_failure', array('message' => $result->message));
        }
    }

    public function search() {
        //$this->output->enable_profiler(true);

        $this->load->model('Card');

        $data = array();
        $data['method'] = $this->input->method();

        if ($data['method'] === 'post') {
            $searched_str       = $this->input->post('searched_str');
            $is_case_sensitive  = ($this->input->post('case_sensitive') !== null) ? true : false;
            $language           = ($this->input->post('language') !== null) ? $this->input->post('language') : 'both';
            $state              = ($this->input->post('state') !== null) ? $this->input->post('state') : 'both';

            $searched_cards = Card::find_searched_cards($searched_str, $is_case_sensitive, $language, $state);
            $data['searched_cards'] = $searched_cards;
        }

        $this->layout->add_basic_assets()
                     ->menu()
                     ->action_view($data);
    }

    public function view($id) {
        //$this->output->enable_profiler(true);
        
        $id = (int) $id;

        $this->load->model('Card');
        $this->load->model('Deck');

        $data['id'] = $id;

        $card = Card::find($id);
        $card->with_version_when_deleted();
        $card->with_card_contents_history();
        foreach ($card->get_card_contents_history() as &$card_content) {
            $card_content->with_version();
        }
        $data['card'] = $card;

        $decks = Deck::find_all_with_contains_current_card($id);
        $data['decks'] = $decks;

        $this->layout->add_basic_assets()
                     ->menu()
                     ->action_view($data);
    }
}

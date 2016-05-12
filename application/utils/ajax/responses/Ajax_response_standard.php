<?php
namespace utils\ajax\responses;

class Ajax_response_standard
{
    public $status;
    public $title = 'Server response';
    public $message;

    public function __construct($status, $message) {
        $this->status   = $status;
        $this->message  = $message;
    }

    public function produce($format = 'json') {
        switch ($format) {
            case 'json':
                return '{"ajax_response": {"status": "' . $this->status . '", "title": "' . $this->title . '", "message": "' . $this->message . '"}}';
                break;
            case 'xml':
                return '<ajax_response><status>' . $this->status . '</status><title>' . $this->title . '</title><message>' . $this->message . '</message></ajax_response>';
                break;
        }
    }
}

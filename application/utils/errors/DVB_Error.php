<?php
namespace utils\errors;

class DVB_Error
{
    public $type;
    public $message;

    public function __construct($type = 'UNKNOWN_ERROR', $message = 'An error occurred.') {
        $this->type = $type;
        $this->message = $message;
    }
}

<?php
namespace utils\errors;

class Finder_param_error
{
    public $type;
    public $message;

    public function __construct($type = 'FINDER_PARAM_ERROR', $message = 'A parameter is missing.') {
        $this->type = $type;
        $this->message = $message;
    }
}

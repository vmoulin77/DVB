<?php
namespace exceptions;

class Database_exception extends \Exception {
    public function __toString() {
        return 'TEST EXCEPTION';
    }
}

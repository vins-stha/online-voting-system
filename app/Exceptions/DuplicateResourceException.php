<?php

namespace App\Exceptions;
use Exception;

class DuplicateResourceException extends Exception {

    function __construct($message)
    {
       $this->message = $message;
    }
    
}
<?php

namespace App\Exceptions;
use Exception;

class DuplicateDataException extends Exception {

    function __construct()
    {
        parent::__construct();
    }
    
}
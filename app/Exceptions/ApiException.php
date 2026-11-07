<?php

namespace App\Exceptions;

use App\Builder\ReturnApi;
use Exception;

class ApiException extends Exception
{
    protected $code = 500;
    protected $message = "Erro inesperado";
    public $data = [];
    public function __construct($message = null, $code = 500, $data = [])
    {
        if ($message) {
            $this->message = $message;
        }

        $this->code = $code;
        $this->data = $data;
       
        parent::__construct($this->message, $this->code);
      
    }
    public function render()
    {
        return ReturnApi::error(message: $this->message, data: $this->data, status: $this->code);
    }
}

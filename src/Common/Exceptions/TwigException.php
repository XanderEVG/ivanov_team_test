<?php


namespace App\Common\Exceptions;


use Exception;

class TwigException extends Exception
{
    /**
     * Коструктор каласса.
     *
     * @param string $msg
     */
    public function __construct(string $msg)
    {
        parent::__construct($msg);
    }
}
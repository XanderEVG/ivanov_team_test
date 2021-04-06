<?php
namespace App\Command\Exceptions;

use Exception;

/**
 * Class TrailerParseDataFailed
 * @package App\Command\Exceptions
 */
class TrailerParseDataFailed  extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
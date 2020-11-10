<?php

namespace Programgames\OroDev\Exception;

use Exception;
use Throwable;

class ParameterNotFoundException extends Exception
{
    public function __construct(string $parameter, $code = 0, Throwable $previous = null)
    {
        parent::__construct('Parameter : ' . $parameter . 'not found', $code, $previous);
    }
}

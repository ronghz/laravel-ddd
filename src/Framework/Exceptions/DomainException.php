<?php

namespace Ronghz\LaravelDdd\Framework\Exceptions;

use Throwable;

/**
 * 领域异常,根据code取message
 * Class DomainException
 * @package Ronghz\LaravelDdd\Framework\Exceptions
 */
class DomainException extends \Exception
{
    protected $code;

    public function __construct($code = 0, $message = '', ?Throwable $previous = null)
    {
        $this->code = $code ? $code : $this->code;
        $this->message = $message ? $message : $this->message;
        if (empty($this->message)) {
            $this->message = $this->getMessageByCode();
        }
        parent::__construct($this->message, $this->code, $previous);
    }

    protected function getMessageByCode()
    {
        $message = \App\Exceptions\ExceptionCode::getText($this->code);
        if ($message) {
            return $message;
        }
        return ExceptionCode::getText($this->code);
    }
}

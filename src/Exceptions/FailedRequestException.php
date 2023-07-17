<?php

namespace Triverla\LaravelMonnify\Exceptions;


use Throwable;

class FailedRequestException extends \Exception
{

    /**
     * FailedRequestException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param int $code
     * @return FailedRequestException
     *
     * @link https://docs.teamapt.com/display/MON/Transaction+Responses
     */
    public static function byCode(int $code): FailedRequestException
    {
        switch ($code) {
            case 0:
                $message = "Request was successfully processed";
                break;
            case 99:
                $message = "Request processing failed. A responseMessage field will be included with details of reason for failed request.";
                break;
            case 100:
                $message = "Attempt to initialize an already completed card transaction.";
                break;
            default:
                $message = "Unhandled error occurred.";
                break;

        }
        return new self($message, $code);
    }
}

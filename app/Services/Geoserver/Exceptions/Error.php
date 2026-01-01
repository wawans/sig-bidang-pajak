<?php

namespace App\Services\Geoserver\Exceptions;

use Exception;
use GuzzleHttp\Exception\ClientException;

class Error extends Exception
{
    /**
     * Server responded with an error.
     *
     * @return static
     */
    public static function serviceRespondedWithAnError($response)
    {
        return new static('Server responded with an error: `'.$response.'`');
    }

    /**
     * Thrown on a generic error.
     *
     * @param  mixed  $message
     * @return static
     */
    public static function genericMessage($message)
    {
        return new static($message);
    }

    /**
     * Thrown if a 400-level Http error was encountered whilst attempting to deliver the
     * request.
     *
     * @return static
     */
    public static function clientError(ClientException $exception)
    {
        if (! $exception->hasResponse()) {
            return new static('Server responded with an error but no response body was available');
        }

        $statusCode = $exception->getResponse()->getStatusCode();
        $description = $exception->getMessage();

        return new static(
            "Failed to send request, encountered client error: `{$statusCode} - {$description}`"
        );
    }

    /**
     * Thrown if an unexpected exception was encountered whilst attempting to deliver the
     * request.
     *
     * @return static
     */
    public static function unexpectedException(Exception $exception)
    {
        return new static(
            'Failed to send request, unexpected exception encountered: `'.$exception->getMessage().'`',
            0,
            $exception
        );
    }
}

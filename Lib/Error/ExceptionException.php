<?php
/**
 * ExceptionException
 *
 */
class ExceptionException extends CakeException
{

    public function __construct($message = null, $code = 500)
    {
        if (empty($message)) {
            $message = __('Exception Error.');
        }
        parent::__construct($message, $code);
    }
}

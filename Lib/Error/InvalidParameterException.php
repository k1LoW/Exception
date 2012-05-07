<?php
App::uses('ExceptionException', 'Exception.Error');

/**
 * InvalidParameterException
 *
 * jpn:Modelメソッドなどでパラメータが不十分の場合に投げる例外
 */
class InvalidParameterException extends ExceptionException {

    public function __construct($message = null, $code = 200) {
        if (empty($message)) {
            $message = __('Invalid Parameter Error.');
        }
        parent::__construct($message, $code);
    }
}
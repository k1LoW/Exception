<?php
App::uses('ExceptionException', 'Exception.Error');

/**
 * ValidationException
 *
 * jpn:Modelでバリデーションエラーが発生した場合に投げられる例外
 */
class ValidationException extends ExceptionException {

    public function __construct($message = null, $code = 422) {
        if (empty($message)) {
            $message = __('Validation Error.');
        }
        parent::__construct($message, $code);
    }
}
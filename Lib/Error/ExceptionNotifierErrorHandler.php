<?php
/**
 * ExceptionNotifierErrorHandler
 * @see https://github.com/kozo/cakephp_exception_notifier/blob/2.0/Lib/Error/ExceptionNotifierErrorHandler.php
 */
App::uses('ExceptionText', 'Exception.Lib');
class ExceptionNotifierErrorHandler extends ErrorHandler {
    public static function handleError($code, $description, $file = null, $line = null, $context = null) {

        $errorConf = Configure::read('Error');
        if(!($errorConf['level'] & $code)){
            return;
        }

        parent::handleError($code, $description, $file, $line, $context);

        $errorInfo = self::mapErrorCode($code);

        try{
            $mail = new CakeEmail('error');
            $text = ExceptionText::getText($errorInfo[0] . ':' . $description, $file, $line, $context);
            $mail->send($text);
        } catch(Exception $e){
            $message = $e->getMessage();
            CakeLog::write(LOG_ERROR, $message);
        }
    }
}

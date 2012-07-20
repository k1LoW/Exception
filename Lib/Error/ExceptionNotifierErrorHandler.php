<?php
/**
 * ExceptionNotifierErrorHandler
 * @see https://github.com/kozo/cakephp_exception_notifier/blob/2.0/Lib/Error/ExceptionNotifierErrorHandler.php
 */
App::uses('ExceptionText', 'Exception.Lib');
class ExceptionNotifierErrorHandler extends ErrorHandler {
    public static function handleError($code, $description, $file = null, $line = null, $context = null) {
        parent::handleError($code, $description, $file, $line, $context);

        $errorConf = Configure::read('Error');
        if(!($errorConf['level'] & $code)){
            return;
        }

        $force = Configure::read('ExceptionNotifier.force');
        $debug = Configure::read('debug');
        if (!$force && $debug > 0) {
            return;
        }

        list($error, $log) = self::mapErrorCode($code);

        try{
            $email = new CakeEmail('error');
            $prefix = Configure::read('ExceptionNotifier.prefix');
            $from = $email->from();
            if (empty($from)) {
                $email->from('exception.notifier@default.com', 'Exception Notifier');
            }
            $subject = $email->subject();
            if (empty($subject)) {
                $email->subject($prefix . '['. date('Ymd H:i:s') . '][' . strtoupper($error) . '][' . ExceptionText::getUrl() . '] ' . $description);
            }
            $text = ExceptionText::getText($error . ':' . $description, $file, $line, $context);
            return $email->send($text);
        } catch(Exception $e){
            $message = $e->getMessage();
            return CakeLog::write(LOG_ERROR, $message);
        }
    }
}

<?php
/**
 * ExceptionNotifierErrorHandler
 * @see https://github.com/kozo/cakephp_exception_notifier/blob/2.0/Lib/Error/ExceptionNotifierErrorHandler.php
 */
App::uses('CakeEmail', 'Lib.Email.Network');
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

    /**
     * handleException
     *
     * @param Exception $exception
     */
    public static function handleException(Exception $exception){

        /**
         * @see ErrorHandler::handleException
         */
        $config = Configure::read('Exception');
        if (!empty($config['log'])) {
            $message = sprintf("[%s] %s\n%s",
                               get_class($exception),
                               $exception->getMessage(),
                               $exception->getTraceAsString()
                               );
            CakeLog::write(LOG_ERR, $message);
        }
        $renderer = $config['renderer'];
        if ($renderer !== 'ExceptionRenderer') {
            list($plugin, $renderer) = pluginSplit($renderer, true);
            App::uses($renderer, $plugin . 'Error');
        }

        $force = Configure::read('ExceptionNotifier.force');
        $debug = Configure::read('debug');
        if (!$force && $debug > 0) {
            return;
        }

        if (self::_checkAllowed($exception)) {
            try{
                $email = new CakeEmail('error');
                $prefix = Configure::read('ExceptionNotifier.prefix');
                $from = $email->from();
                if (empty($from)) {
                    $email->from('exception.notifier@default.com', 'Exception Notifier');
                }
                $subject = $email->subject();
                if (empty($subject)) {
                    $email->subject($prefix . '['. date('Ymd H:i:s') . '][Exception][' . ExceptionText::getUrl() . '] ' . $exception->getMessage());
                }
                $text = ExceptionText::getText($exception->getMessage(), $exception->getFile(), $exception->getLine());
                $email->send($text);
            } catch(Exception $e){
                $message = $e->getMessage();
                CakeLog::write(LOG_ERROR, $message);
            }
        }

        /**
         * @see ErrorHandler::handleException
         */
        try {
            $error = new $renderer($exception);
            $error->render();
        } catch (Exception $e) {
            set_error_handler(Configure::read('Error.handler')); // Should be using configured ErrorHandler
            Configure::write('Error.trace', false); // trace is useless here since it's internal
            $message = sprintf("[%s] %s\n%s", // Keeping same message format
                               get_class($e),
                               $e->getMessage(),
                               $e->getTraceAsString()
                               );
            trigger_error($message, E_USER_ERROR);
        }
    }

    /**
     * _checkAllowed
     *
     */
    private static function _checkAllowed(Exception $exception){
        $allow = Configure::read('ExceptionNotifier.allowedException');
        foreach ((array)$allow as $exceptionName) {
            if ($exception instanceof $exceptionName) {
                return true;
            }
        }
        $deny = Configure::read('ExceptionNotifier.deniedException');
        foreach ((array)$deny as $exceptionName) {
            if ($exception instanceof $exceptionName) {
                return false;
            }
        }
        return true;
    }
}

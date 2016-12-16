<?php
/**
 * ExceptionNotifierErrorHandler
 * @see https://github.com/kozo/cakephp_exception_notifier/blob/2.0/Lib/Error/ExceptionNotifierErrorHandler.php
 */
App::uses('ExceptionText', 'Exception.Lib');
App::uses('ExceptionMail', 'Exception.Network/Email');
App::uses('ExceptionFatalErrorException', 'Exception.Lib/Error');
App::uses('ExceptionStrictException', 'Exception.Lib/Error');
App::uses('ExceptionNoticeException', 'Exception.Lib/Error');
App::uses('ExceptionWarningException', 'Exception.Lib/Error');
App::uses('ExceptionDeprecatedException', 'Exception.Lib/Error');

class ExceptionNotifierErrorHandler extends ErrorHandler
{
    public static function handleError($code, $description, $file = null, $line = null, $context = null)
    {

        parent::handleError($code, $description, $file, $line, $context);

        $errorConf = Configure::read('Error');
        if(!($errorConf['level'] & $code)){
            return;
        }

        if (!self::notifyAllowed()) {
            return;
        }

        list($error, $log) = self::mapErrorCode($code);

        $class = 'Exception' . str_replace(' ', '', $error) . 'Exception';

        self::handleException(new $class($description, 0, $code, $file, $line));
    }

    /**
     * handleException
     *
     * @param Exception $exception
     */
    public static function handleException(Exception $exception)
    {

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

        if (self::notifyAllowed() && self::checkAllowed($exception)) {
            $trace = Debugger::trace(array('start' => 2, 'format' => 'base'));
            self::execute($exception, $trace);
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
     * execute
     *
     */
    public static function execute(Exception $exception, $trace){
        $error = array(
            'exception' => $exception,
            'trace' => $trace,
            'params' => Router::getRequest(),
            'environment' => $_SERVER,
            'session' => $session = isset($_SESSION) ? $_SESSION : array(),
            'cookie' => $_COOKIE,
        );

        ExceptionMail::send($error);
    }

    private static function notifyAllowed() {
        $force = Configure::read('ExceptionNotifier.force');
        $debug = Configure::read('debug');
        return ($force || $debug == 0);
    }

    /**
     * checkAllowed
     *
     */
    private static function checkAllowed(Exception $exception)
    {
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
        $codes = Configure::read('ExceptionNotifier.deniedStatusCode');
        foreach ((array)$codes as $code) {
            if ($exception->getCode() == $code) {
                return false;
            }
        }

        return true;
    }
}

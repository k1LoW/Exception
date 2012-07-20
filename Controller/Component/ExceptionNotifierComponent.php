<?php
/**
 * ExceptionNotifierComponent for CakePHP 2.x
 *
 * @see original: https://github.com/milk1000cc/cakephp_exception_notifier
 */
App::uses('Component', 'Controller');
App::uses('CakeEmail', 'Network/Email');
App::uses('ErrorHandler', 'Error');
App::uses('ExceptionText', 'Exception.Lib');
class ExceptionNotifierComponent extends Component {

    public $ERR_TYPE = array(
                             E_ERROR => 'FATAL',
                             E_WARNING => 'WARNING',
                             E_NOTICE => 'NOTICE',
                             E_STRICT => 'STRICT'
                             );

    // Mail configuration
    public $useSmtp = false;
    public $smtpParams = array(
                               'host'=>'smtp.default.com',
                               'port'=>'25',
                               );

    // Exception error configuration
    public $observeNotice = true;
    public $observeWarning = true;
    public $observeStrict = false;

    public $observeException = true;

    private $_controller;
    private $_exception;

    public function initialize(Controller $controller) {
        $this->_controller = $controller;
    }

    public function handleShutdown() {
        $error = error_get_last();
        switch ($error['type']) {
        case E_ERROR:
        case E_PARSE:
        case E_CORE_ERROR:
        case E_CORE_WARNING:
        case E_COMPILE_ERROR:
        case E_COMPILE_WARNING:
            $this->handleException(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']), true);
        }
    }

    public function handleException(Exception $exception, $shutdown = false) {
        $this->_exception = $exception;
        $email = new CakeEmail('error');
        $prefix = Configure::read('ExceptionNotifier.prefix');
        $from = $email->from();
        if (empty($from)) {
            $email->from('exception.notifier@default.com', 'Exception Notifier');
        }
        $subject = $email->subject();
        if (empty($subject)) {
            $email->subject($prefix . '['. date('Ymd H:i:s') . '][' . $this->_getSeverityAsString() . '][' . ExceptionText::getUrl() . '] ' . $exception->getMessage());
        }
        if ($this->useSmtp) {
            $email->transport('Smtp');
            $email->config($this->smtpParams);
        }

        $text = ExceptionText::getText($exception->getMessage(), $exception->getFile(), $exception->getLine());
        $email->send($text);

        // return Exception.handler
        if ($shutdown || !($this->_exception instanceof ErrorException)) {
            $config = Configure::read('Exception');
            $handler = $config['handler'];
            if (is_string($handler)) {
                call_user_func($handler, $exception);
            } elseif (is_array($handler)) {
                call_user_func_array($handler, $exception);
            }
        }
    }

    public function handleError($code, $description, $file = null, $line = null, $context = null) {
        $cakePath = CAKE_CORE_INCLUDE_PATH . DS . CAKE;
        if (ErrorHandler::handleError($code, $description, $file, $line, $context) !== false && !preg_match('!^' . $cakePath . '!', $file)) {
            $this->handleException(new ErrorException($description, 0, $code, $file, $line));
            return true;
        }
        return false;
    }

    public function observe() {
        $force = Configure::read('ExceptionNotifier.force');
        $debug = Configure::read('debug');
        if (!$force && $debug > 0) {
            return;
        }

        // error_reporting(E_ALL) and don't display errors
        if (Configure::read('debug') == 0) {
            error_reporting(E_ALL);
            ini_set('display_errors', 0);
        }

        register_shutdown_function(array($this, 'handleShutdown'));
        if ($this->observeException) {
            set_exception_handler(array($this, 'handleException'));
        }

        $errTypes = 0;
        if ($this->observeNotice) $errTypes = $errTypes | E_NOTICE;
        if ($this->observeWarning) $errTypes = $errTypes | E_WARNING;
        if ($this->observeStrict) $errTypes = $errTypes | E_STRICT;
        if ($errTypes) set_error_handler(array($this, 'handleError'), $errTypes);
    }

    private function _getSeverityAsString() {
        if (!method_exists($this->_exception, 'getSeverity')) return 'ERROR';

        $errNo = $this->_exception->getSeverity();
        return array_key_exists($errNo, $this->ERR_TYPE) ? $this->ERR_TYPE[$errNo] : "(errno: {$errNo})";
    }

}
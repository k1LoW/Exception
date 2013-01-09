<?php
App::uses('CakeEmail', 'Network/Email');
class ExceptionMail extends CakeMail{

    private static function _getText($errorInfo, $description, $file, $line, $context){
        $params = Router::getRequest();
        $trace = Debugger::trace(array('start' => 2, 'format' => 'base'));
        $session = isset($_SESSION) ? $_SESSION : array();

        $msg = array(
            $errorInfo[0] . ':' . $description,
            $file . '(' . $line . ')',
            '',
            '-------------------------------',
            'Request:',
            '-------------------------------',
            '',
            '* URL       : ' . self::_getUrl(),
            '* IP address: ' . env('REMOTE_ADDR'),
            '* Parameters: ' . trim(print_r($params, true)),
            '* Cake root : ' . APP,
            '',
            '-------------------------------',
            'Environment:',
            '-------------------------------',
            '',
            trim(print_r($_SERVER, true)),
            '',
            '-------------------------------',
            'Session:',
            '-------------------------------',
            '',
            trim(print_r($session, true)),
            '',
            '-------------------------------',
            'Cookie:',
            '-------------------------------',
            '',
            trim(print_r($_COOKIE, true)),
            '',
            '-------------------------------',
            'Backtrace:',
            '-------------------------------',
            '',
            trim($trace),
            '',
            '-------------------------------',
            'Context:',
            '-------------------------------',
            '',
            trim(print_r($context, true)),
            '',
            );

        return join("\n", $msg);
    }

}
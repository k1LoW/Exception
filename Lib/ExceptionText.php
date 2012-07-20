<?php
class ExceptionText {

    public static function getText($message, $file, $line, $context = null){
        $params = Router::getRequest();
        $trace = Debugger::trace(array('start' => 2, 'format' => 'base'));
        $session = isset($_SESSION) ? $_SESSION : array();

        $msg = array(
            $message,
            $file . '(' . $line . ')',
            '',
            '-------------------------------',
            'Request:',
            '-------------------------------',
            '',
            '* URL       : ' . self::getUrl(),
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

    public static function getUrl() {
        if (PHP_SAPI == 'cli') {
            return '';
        }

        $protocol = array_key_exists('HTTPS', $_SERVER) ? 'https' : 'http';
        return $protocol . '://' . env('HTTP_HOST') . env('REQUEST_URI');
    }
}
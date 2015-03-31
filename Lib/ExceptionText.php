<?php
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class ExceptionText {

    private static $handler;

    public static function getBody($message, $file, $line, $context = null){
        $html = Configure::read('ExceptionNotifier.html');
        if ($html) {
            return self::getHtml($message, $file, $line, $context);
        }
        return self::getText($message, $file, $line, $context);
    }

    public static function getText($message, $file, $line, $context = null){
        $params = Router::getRequest();
        $trace = Debugger::trace(array('start' => 2, 'format' => 'base'));
        $session = isset($_SESSION) ? $_SESSION : array();

        $msg = array(
            $message,
            $file . '(' . $line . ')',
            '',
            '-------------------------------',
            'Backtrace:',
            '-------------------------------',
            '',
            trim($trace),
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
            'Context:',
            '-------------------------------',
            '',
            trim(print_r($context, true)),
            '',
            );

        return join("\n", $msg);
    }

    public static function getHtml($message, $file, $line, $context = null){
        $params = Router::getRequest();
        $trace = Debugger::trace(array('start' => 2, 'format' => 'base'));
        $session = isset($_SESSION) ? $_SESSION : array();

        $msg = array(
            '<p><strong>',
            $message,
            '</strong></p>',
            '<p>',
            $file . '(' . $line . ')',
            '</p>',
            '',
            '<h2>',
            'Backtrace:',
            '</h2>',
            '',
            '<pre>',
            trim($trace),
            '</pre>',
            '',
            '<h2>',
            'Request:',
            '</h2>',
            '',
            '<h3>URL</h3>',
            self::getUrl(),
            '<h3>IP address</h3>',
            env('REMOTE_ADDR'),
            '<h3>Parameters</h3>',
            self::dumper($params),
            '<h3>Cake root</h3>',
            APP,
            '',
            '<h2>',
            'Environment:',
            '</h2>',
            '',
            self::dumper($_SERVER),
            '',
            '<h2>',
            'Session:',
            '</h2>',
            '',
            self::dumper($session),
            '',
            '<h2>',
            'Cookie:',
            '</h2>',
            '',
            self::dumper($_COOKIE),
            '',
            '<h2>',
            'Context:',
            '</h2>',
            '',
            self::dumper($context),
            '',
            );

        return join("\n", $msg);
    }

    public static function dumper($obj) {
        ob_start();
        $cloner = new VarCloner();
        $dumper = new HtmlDumper();
        self::$handler = function ($obj) use ($cloner, $dumper) {
            $dumper->dump($cloner->cloneVar($obj));
        };
        call_user_func(self::$handler, $obj);
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }

    public static function getUrl() {
        if (PHP_SAPI == 'cli') {
            return '';
        }

        $protocol = array_key_exists('HTTPS', $_SERVER) ? 'https' : 'http';
        return $protocol . '://' . env('HTTP_HOST') . env('REQUEST_URI');
    }
}

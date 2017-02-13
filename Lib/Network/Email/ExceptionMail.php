<?php
App::uses('CakeEmail', 'Network/Email');
App::uses('ExceptionText', 'Exception.Lib');
App::uses('ExceptionSenderInterface', 'Exception.Lib');
class ExceptionMail implements ExceptionSenderInterface
{

    /**
     * send
     *
     * @param $error
     */
    public static function send($error)
    {
        $prefix = Configure::read('ExceptionNotifier.prefix');
        $subject = $prefix . '['. date('Ymd H:i:s') . '][' . get_class($error['exception']) . '][' . ExceptionText::getUrl() . '] ' . $error['exception']->getMessage();
        $body = ExceptionText::getBody($error['exception']->getMessage(), $error['exception']->getFile(), $error['exception']->getLine(), $error['context']);
        try{
            $email = new CakeEmail('error');
            $html = Configure::read('ExceptionNotifier.html');
            if ($html) {
                $email->emailFormat('html');
            }
            $from = $email->from();
            if (empty($from)) {
                $email->from('exception.notifier@default.com', 'Exception Notifier');
            }
            $defaultSubject = $email->subject();
            if (empty($defaultSubject)) {
                $email->subject($subject);
            }
            return $email->send($body);
        } catch(Exception $e){
            $message = $e->getMessage();
            return CakeLog::write(LOG_ERR, $message);
        }
    }

}

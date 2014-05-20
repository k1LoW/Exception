<?php
App::uses('CakeEmail', 'Network/Email');
App::uses('ExceptionText', 'Exception.Lib');
class ExceptionMail {

    /**
     * send
     *
     * @param $subject
     * @param $body
     */
    public static function send($subject, $body){
        try{
            $email = new CakeEmail('error');
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
            return CakeLog::write(LOG_ERROR, $message);
        }
    }

}

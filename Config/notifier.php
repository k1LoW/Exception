<?php
require 'bootstrap.php';
App::uses('ExceptionNotifierErrorHandler', 'Exception.Error');
Configure::write('Error.handler', 'ExceptionNotifierErrorHandler::handleError');
Configure::write('Exception.handler', 'ExceptionNotifierErrorHandler::handleException');

Configure::write('ExceptionNotifier.senders', [
    array('ExceptionMail', 'Exception.Network/Email'),
]);

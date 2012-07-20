<?php
require 'bootstrap.php';
App::uses('ExceptionNotifierErrorHandler', 'Exception.Error');
Configure::write('Error.handler', 'ExceptionNotifierErrorHandler::handleError');
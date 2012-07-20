# Exception Template for CakePHP

Exception class template.

## Install

Install 'Exception' by [recipe.php](https://github.com/k1LoW/recipe).

## Usage

### Exception class template

Set `CakePlugin::load('Exception', array('bootstrap' => true));`.

### ExceptionNotifier

Set `EmailConfig::error' option in app/Config/email.php.

#### ExceptionNotifierErrorHandler

Set `CakePlugin::load('Exception', array('bootstrap' => 'notifier));`.

#### ExceptionNotifierComponent

Add the following code in app_controller.php

    <?php
        class AppController extends Controller {
            var $components = array('Exception.ExceptionNotifier');
            
            public function beforeFilter() {
                $this->ExceptionNotifier->observe();
            }
        }

#### Configuration

- ExceptionNotifier.force
- ExceptionNotifier.prefix

## License
the MIT License

### ExceptionNotifierComponent original lisence
Copyright © 2009-2010 milk1000cc, released under the MIT license.


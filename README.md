# Exception plugin for CakePHP

Exception class template.

## Install

Install 'Exception' by [recipe.php](https://github.com/k1LoW/recipe).

## Usage: Exception class template

Set `CakePlugin::load('Exception', array('bootstrap' => true));`.

## Usage: ExceptionNotifier

Set `EmailConfig::error` option in app/Config/email.php.

### ExceptionNotifierErrorHandler

Set `CakePlugin::load('Exception', array('bootstrap' => 'notifier'));`.

### ExceptionNotifierComponent [Deprecated]

Add the following code in AppController.php

    <?php
        class AppController extends Controller {
            var $components = array('Exception.ExceptionNotifier');
            
            public function beforeFilter() {
                $this->ExceptionNotifier->observe();
            }
        }

### Configuration

- ExceptionNotifier.force
- ExceptionNotifier.prefix

## Authors

- [k1LoW](https://github.com/k1LoW)
- [kozo](https://github.com/kozo)

## License
the MIT License

### ExceptionNotifierComponent original lisence
Copyright © 2009-2010 milk1000cc, released under the MIT license.

<?php

use App\Kernel;

date_default_timezone_set('Europe/Paris');

## chemin vers le fichier autoload.php en local
#require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

## chemin vers le fichier autoload.php de symfony sur le serveur
require_once dirname(__DIR__).'/symfony/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};

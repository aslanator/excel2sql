<?php

require __DIR__ . '/vendor/autoload.php';

Mustache_Autoloader::register();

CModule::AddAutoloadClasses('excel2sql', array(
    'Excel2sql\Excel2sqlTable' => 'lib/excel2sqlTable.php',
    'Excel2sql\Excel2sqlMustache' => 'lib/excel2sqlMustache.php'
));
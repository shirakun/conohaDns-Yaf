<?php
define('BASE_PATH', __DIR__ . '/../');
define('APP_PATH', BASE_PATH . 'application/');

$app = new Yaf\Application(BASE_PATH . 'conf/app.ini');

$app->bootstrap()->run();

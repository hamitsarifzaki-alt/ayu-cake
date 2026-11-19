<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Bootstrap.php';

require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/App.php';

require_once APP_PATH . '/controllers/user/HomeController.php';

$app = new App();
$app->run();

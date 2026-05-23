<?php

require_once __DIR__ . '/../src/Gateway.php';

use Gateway\Gateway;

$gateway = new Gateway();
$gateway->handle();

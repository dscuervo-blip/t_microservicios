<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Gateway\Gateway;

$gateway = new Gateway();
$gateway->handle();

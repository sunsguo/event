<?php
include_once '../sys/core/init.inc.php';

$obj = new Admin($dbo);

$hash = $obj->testSaltHash("123456");

echo $hash;

echo "fdlsjf";
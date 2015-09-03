<?php

require __DIR__ . '/../vendor/autoload.php';


use M1n05\BackgroundProcess\BackgroundProcess;

define('ROOT_PATH', __DIR__ . '/..');



$pid = BackgroundProcess::run("php ". realpath(ROOT_PATH) . DIRECTORY_SEPARATOR .  "tests". DIRECTORY_SEPARATOR . "test2.php", realpath(ROOT_PATH) . DIRECTORY_SEPARATOR . "test.txt");


var_dump($pid);

var_dump(BackgroundProcess::isRunning($pid));
sleep(10);
echo 'after' . PHP_EOL;
var_dump(BackgroundProcess::stop($pid));
var_dump(BackgroundProcess::isRunning($pid));
//var_dump($backgroundprocess->getPid());
//var_dump($backgroundprocess->isRunning());

        

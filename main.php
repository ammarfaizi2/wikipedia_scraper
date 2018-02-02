<?php

// require __DIR__."/src/Wikipedia.php";
require __DIR__."/vendor/autoload.php";


$query = $argv[1];
$wiki = new Wikipedia\Wikipedia($query);
$wiki = $wiki->search();

var_dump($wiki);
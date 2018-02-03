<?php

require __DIR__."/src/Wikipedia.php";
// require __DIR__."/vendor/autoload.php";

define("WIKIPEDIA_DATA_DIR", __DIR__."/data");

$query = $argv[1];
$wiki = new Wikipedia\Wikipedia($query, "en");
$wiki = $wiki->search();

var_dump($wiki);

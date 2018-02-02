<?php

require __DIR__."/src/Wikipedia.php";


$query = $argv[1];
$wiki = new Wikipedia\Wikipedia($query);
$wiki = $wiki->search();

var_dump($wiki);
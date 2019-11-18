<?php

$settings = require __DIR__ . '/../settings.php';

$settings = require __DIR__ . '/../app/Model.php';
$settings = require __DIR__ . '/../app/Importer.php';
$settings = require __DIR__ . '/../app/PHPExcel.php';
$settings = require __DIR__ . '/../app/PHPExcel/IOFactory.php';

$importer = new \App\Importer();
//var_dump($importer->import());
var_dump($importer->checkExistanceByKey('name', 'butter'));

/*var_dump($importer->update(array(
'name' => 'sweet',
'descr' => 'великолепные'
),
1
));*/

var_dump($importer->odku());

?>
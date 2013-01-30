<?php

require_once 'lib/core.php';

$ping = new Ping();
$ping->aid = $_REQUEST['aid'];
$ping->time = time();
$ping->save();

$images = Image::listByAid($_REQUEST['aid']);
$device = Device::getByAid($_REQUEST['aid']);

if (count($images) !== count(glob("content/{$device->shop_name}/{$device->point_name}/*"))){
    echo json_encode(array('action' => 'reload', 'info' => 'count files', 'images' => $images, 'glob' => glob("content/{$device->shop_name}/{$device->point_name}/*")));
    exit();
}

foreach ($images as $image){
    $file = 'content/' . $image->path;
    if (file_exists($file) === false || filectime($file) > $image->time){
        echo json_encode(array('action' => 'reload', 'info' => 'changefile'));
        exit();
    }
}

echo json_encode(array('success' => true));

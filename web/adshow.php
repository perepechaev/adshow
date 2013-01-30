<?php

$json = " [
    {time:4000,src:'http://img08.slando.ru/images_slandoru/79338033_2_644x461_rakovina-rakushka-160-mm-fotografii.jpg'},
    {time:8000,src:'http://i.sunhome.ru/foto/24/rakushka2.jpg'},
    {time:16000,src:'http://dcp.sovserv.ru/media/images/0/b/3/165927.jpg'}
]
";

file_put_contents("log/adshow.log", date('Y-m-d H:i:s') . "\t" . $_SERVER["REMOTE_ADDR"] . "\t" . $_SERVER["REQUEST_URI"] . "\n", FILE_APPEND);

require 'lib/core.php';

$point = basename(trim($_GET['point']));
$shop  = basename(trim($_GET['shop']));

$dir = realpath("content/$shop/$point");

if (strpos($dir, dirname(__FILE__) . "/content") === false){
    header('HTTP/1.1 403 Forbidden');
    echo "Directory not found";
    file_put_contents("log/alert.log", date('Y-m-d H:i:s') . "\t" . $_SERVER["REMOTE_ADDR"] . "\t" . $_SERVER["REQUEST_URI"] . "\n", FILE_APPEND);
    exit(0);
}

$files = glob($dir . "/*");
$json = array();

$images = array();
foreach ($files as $file){
    $file = basename($file);
    $json[] = array(
        'time' => 5000,
        'src'  => 'http://' . $_SERVER['SERVER_NAME'] . "/content/$shop/$point/$file"
    );
    $images[] = array(
        'path' => "$shop/$point/$file",
        'time' => filectime("content/$shop/$point/$file"),
        'aid' => $_GET['aid']
    );
}
echo json_encode($json);

Image::replace($images);

$manage = new Manage();
$manage->scan();
$manage->storeDevice($_GET['aid'], $_GET['shop'], $_GET['point']);

<?php

class DB
{
    private $pdo;
    public function __construct(){
        $this->pdo = new PDO('sqlite:db/adshow.db');
        $this->pdo->query("CREATE TABLE IF NOT EXISTS shop (id INTEGER PRIMARY KEY ASC, name TEXT)");
        $this->pdo->query("CREATE TABLE IF NOT EXISTS point (id INTEGER PRIMARY KEY ASC, shop INTEGER, name TEXT)");
        $this->pdo->query("CREATE TABLE IF NOT EXISTS device (aid TEXT PRIMARY KEY ASC, shop INTEGER, point INTEGER)");
        $this->pdo->query("CREATE TABLE IF NOT EXISTS ping (aid TEXT, time INTEGER)");
        $this->pdo->query("CREATE TABLE IF NOT EXISTS image (aid TEXT, path TEXT, time INTEGER, PRIMARY KEY (aid, path), UNIQUE(aid, path))");
    }
    public function prepare($query, $bindParams = null){
        $stmt = $this->pdo->prepare($query);
        if (is_null($bindParams) === false && is_array($bindParams) === false){
            $stmt->bindParam(1, $bindParams);
        }
        if ($stmt === false){
            throw new Exception("SQL: ". $query . "<br />" . print_r($this->pdo->errorInfo(), true));
        }
        assert(is_object($stmt) === true);
        $stmt->execute(is_array($bindParams) ? $bindParams : null);
        return $stmt;
    }

    public function query($query, $bindParams = null){
        $stmt = $this->prepare($query, $bindParams);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchCol($query, $bindParams = null){
        $stmt = $this->prepare($query, $bindParams);
        return $stmt->fetchColumn();
    }

    public function fetchObjects($className, $query, $bindParams = null){
        $stmt = $this->prepare($query, $bindParams);
        if ($stmt){
            $result = array();
            while ($obj = $stmt->fetchObject($className)){
                $result[] = $obj;
            }

            return $result;
        }
        return false;
    }

    public function insert($table, $map, $replace = false){

        $stmt = $this->prepareInsertSql($table, $map, $replace);
        $values = array_values($map);

        foreach ($values as $key => &$value){
            $stmt->bindParam($key + 1, $value);
        }
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function inserts($table, $map, $replaces = false){
        $stmt = $this->prepareInsertSql($table, current($map), $replaces);

        foreach ($map as $item){
            $values = array_values($item);
            $stmt->execute($values);
        }
    }

    public function replace($table, $map){
        $this->insert($table, $map, true);
    }

    public function replaces($table, $map){
        $this->inserts($table, $map, true);
    }

    public function delete($table){
        $this->pdo->query("DELETE FROM $table");
    }

    private function prepareInsertSql($table, $map, $replace = false){
        $fields = implode(",", array_keys($map));
        $values = implode(',', array_fill(0, count($map), '?'));

        $op = $replace ? "REPLACE" : "INSERT";
        $sql = "$op INTO $table ($fields) VALUES ($values)";
        $stmt = $this->pdo->prepare($sql);

        if ($stmt === false){
            throw new Exception("SQL: " . $sql . "<br/>" . print_r($this->pdo->errorInfo(), true));
        }
        return $stmt;
    }
}

class App
{
    static private $db;

    static public function db(){
        if (self::$db === null){
            self::$db = new DB();
        }
        return self::$db;
    }
}

class Manage
{
    public function __construct(){
    }

    public function scan(){
        $db = new DB();

        foreach ( glob('content/*') as $file){
            if (is_dir($file) === false){
                continue;
            }

            $shop_id = $db->fetchCol("SELECT id FROM shop WHERE name = ?", basename($file));
            if ($shop_id === false){
                $shop_id = $db->insert('shop', array(
                    'name' => basename($file)
                ));
            }

            foreach ( glob("$file/*") as $point){

                if (is_dir($point) === false){
                    continue;
                }

                $point_id = $db->fetchCol("SELECT id FROM point WHERE name = ? AND shop = ?", array(basename($point), $shop_id));
                if ($point_id === false){
                    $point_id = $db->insert('point', array(
                        'name' => basename($point),
                        'shop' => $shop_id
                    ));
                }
            }
        }
    }

    public function storeDevice($aid, $shop, $point){
        $db = App::db();
        $device_id = $db->fetchCol("SELECT aid FROM device WHERE aid = ?", $aid);
        $res = $db->query('SELECT point.id point_id, shop.id shop_id FROM point INNER JOIN shop ON shop.id = point.shop WHERE shop.name = ? AND point.name = ?', array($shop, $point));

        $db->replace('device', array(
            'aid' => $aid,
            'shop' => $res[0]['shop_id'],
            'point' => $res[0]['point_id']
        ));
    }
}

class Shop
{
    public $id;
    public $name;

    public function listPoints(){
        assert($this->id);
        $points = Point::listByShopId($this->id);
        foreach ($points as $point) {
            $point->shop_name = $this->name;
        }
        return $points;
    }

    public static function listAll(){
        $db = App::db();
        $stmt = $db->prepare("SELECT * FROM shop");

        if ($stmt){
            $result = array();
            while ($obj = $stmt->fetchObject(__CLASS__)){
                $result[] = $obj;
            }

            return $result;
        }
        return false;
    }
}

class Point
{
    public function listDevices(){
        return Device::listByPoint($this->id);
    }

    public function getDevicesName(){
        $arr = $this->listDevices();
        $result = array();
        foreach ($arr as $device){
            $result[] = $device->aid;
        }
        return $result;
    }

    public function listImages(){
        $path = 'content/' . $this->shop_name . '/' . $this->name . '/';
        $imgs = glob($path . '*');
        $result = array();
        foreach ($imgs as $value) {
            if (is_file($value)){
                $img['image'] = $value;
                $img['url'] = $path . basename($value);
                $img['thumb'] = $path . basename($value);
                $result[] = $img;
            }
        }
        return $result;
    }

    public static function listByShopId($shop_id){
        return App::db()->fetchObjects(__CLASS__, "SELECT * FROM point WHERE shop = ?", $shop_id);
    }

    public static function listAll(){
        return App::db()->query("SELECT * FROM point");
    }
}

class Device
{
    public $aid;
    public $point;

    public static function listByPoint($point_id){
        return App::db()->fetchObjects(__CLASS__, "SELECT * FROM device WHERE point = ?", $point_id);
    }

    public function __toString(){
        return $this->aid;
    }

    public static function getByAid($aid){
        $sql = "
            SELECT device.aid as aid, point.name as point_name, shop.name as shop_name
            FROM device
            LEFT JOIN point ON point.id = device.point
            LEFT JOIN shop ON shop.id = point.shop
            WHERE device.aid = ?
        ";

        $stmt = App::db()->prepare($sql, $aid);
        return $stmt->fetchObject(__CLASS__);
    }

    public static function listAll(){
        $sql = "
            SELECT device.aid as aid, point.name as point_name, shop.name as shop_name, MAX(ping.time) as last_time
            FROM device
            LEFT JOIN point ON point.id = device.point
            LEFT JOIN shop ON shop.id = point.shop
            LEFT JOIN ping ON ping.aid = device.aid
            GROUP BY device.aid
        ";
        return App::db()->fetchObjects(__CLASS__, $sql);
    }
}

class Ping
{
    public $aid;
    public $time;

    public function save(){
        App::db()->insert('ping', array(
            'aid' => $this->aid,
            'time' => $this->time
        ));
    }
}

class Image
{
    public $aid;
    public $path;
    public $time;

    static public function replace($arr){
        $item = current($arr);
        App::db()->query('DELETE FROM image WHERE aid = ?', $item['aid']);
        App::db()->replaces('image', $arr);
    }

    static public function listByAid($aid){
        return App::db()->fetchObjects(__CLASS__, "SELECT * FROM image WHERE aid = ?", $aid);
    }
}

class Template
{
    private $content;
    public function __construct($name){
        $name = preg_replace('/[^\d\w]/', '', $name);
        ob_start();
        include "view/$name.php";
        $this->content = ob_get_contents();
        ob_end_clean();
    }

    public function __toString(){
        return $this->content;
    }
}

class Auth
{
    static public function isLogin(){
        return isset($_SESSION['user']);
    }

    static public function request(){

        if (isset($_COOKIE[session_name()])){
            session_start();
        }

        if (empty($_SESSION['user']) && isset($_POST['auth'])){
            $users = include 'etc/users.php';
            $username = $_POST['name'];
            if (isset($users[$username]) && $users[$username] === $_POST['pwd']){
                if (empty($_COOKIE[session_name()])){
                    session_start();
                }
                $_SESSION['user'] = $username;
            }
        }

        if (empty($_SESSION['user'])){
            throw new AuthException();
        }
    }

    static public function logout(){
        session_destroy();
        header("Location: /monitor.php",TRUE,307);
        exit();
    }
}

class AuthException extends Exception
{

}

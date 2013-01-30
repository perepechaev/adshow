<?php

assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_QUIET_EVAL, 1);

// Create a handler function
function my_assert_handler($file, $line, $code, $msg = '')
{
    throw new Exception($msg, (int) $code);
}
function my_error_handler($errno, $errstr, $errfile, $errline)
{
    throw new Exception("<h3>$errstr</h3>$errfile:$errline", (int) $code);
}
// Set up the callback
assert_options(ASSERT_CALLBACK, 'my_assert_handler');
set_error_handler("my_error_handler", E_ALL | E_STRICT);


try {
    date_default_timezone_set('Europe/Moscow');

    ob_start();

    require_once 'lib/core.php';

    Auth::request();

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'main';
    if ($action === 'logout'){
        Auth::logout();
    }

    $template = new Template($action);
    include "view/layout.php";

    ob_end_flush();
}
catch (AuthException $e){
    $template = new Template('auth');
    include "view/layout.php";
}
catch (Exception $e){
    echo "<h1>Runtime error</h1>";
    echo "{$e->getMessage()}";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

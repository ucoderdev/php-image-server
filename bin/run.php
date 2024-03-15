<?php
$base_path = dirname(__DIR__);
$autoload_file = $base_path . '/vendor/autoload.php';
$config_file = $base_path . '/config.php';

if (!is_file($autoload_file)) {
    exit("Error: The 'vendor' path not found. Please install the composer dependencies before you start!\n");
}

if (!is_file($config_file)) {
    exit("Error: Configuration file not found. Please run bin/setup.php command before you start!\n");
}

// Errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Write errors to file
ini_set('log_errors', TRUE);
ini_set('error_log', $base_path . '/errors.log');

// Composer autoload
require $autoload_file;

// Config
$config = require $config_file;

// Message
echo "Starting the server..." . PHP_EOL;

// Validation
$port = $config['port'];
$ip_address = $config['ip_address'];
$images_dir = $config['images_dir'];
$image_extension = $config['image_extension'];

if (!is_dir($images_dir)) {
    exit("Error: Images directory (images_dir) not found!\n");
}

if (!is_readable($images_dir)) {
    exit("Error: Images directory (images_dir) is not readable!\n");
}

if (empty($image_extension)) {
    exit("Error: Image extension is empty!\n");
}

// Server
try {
    $router = new App\Router($base_path, $images_dir, $image_extension);

    // HTTP Server
    $http = new React\Http\HttpServer(function (Psr\Http\Message\ServerRequestInterface $request) use ($router) {
        return $router->response($request);
    });

    $http->on('error', function (Exception $e) {
        $previous = $e->getPrevious();
        
        if ($previous) {
            echo "\n----------------\n\n";
            echo 'Server Error: ' . $previous->getMessage() . PHP_EOL;
            echo 'File: ' . $previous->getFile() . PHP_EOL;
            echo 'Line: ' . $previous->getLine() . PHP_EOL . PHP_EOL;
            
            echo "----------------\n";
            echo $previous->getTraceAsString() . PHP_EOL;
            echo "----------------\n\n";
        } else {
            echo "\n----------------\n\n";
            echo 'Server Error: ' . $e->getMessage() . PHP_EOL;
            echo 'File: ' . $e->getFile() . PHP_EOL;
            echo 'Line: ' . $e->getLine() . PHP_EOL . PHP_EOL;
    
            echo "----------------\n";
            echo $e->getTraceAsString() . PHP_EOL;
            echo "----------------\n\n";
        }
    });

    // Socket
    $socket = new React\Socket\SocketServer($ip_address . ':' . $port);
    $http->listen($socket);

    // Message
    echo "The server is running at " . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
} catch (\Throwable $th) {
    echo $th->getMessage() . PHP_EOL;
}

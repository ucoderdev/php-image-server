<?php 

$base_dir = dirname(__DIR__);
$config_file = $base_dir . '/config.php';

$config = array(
    'images_dir' => '/var/www/domain.com/uploads',
    'image_extension' => 'gd',
    'port' => 8080,
    'ip_address' => '127.0.0.1',
);

if (!is_file($config_file)) {
    file_put_contents($config_file, '<?php return ' . var_export($config, true) . ';');
    echo "The configuration file created successfully!";
    echo PHP_EOL;
}
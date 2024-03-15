# PHP Image Server

Image manipulation server for resizing and converting images based on PHP.

The main goal is to run the HTTP server for image manipulation API.

This project is based on [ReactPHP](https://reactphp.org/) [HttpServer](https://reactphp.org/http/#server-usage).

As PHP extension, it supports libraries such as [GD](https://www.php.net/manual/en/book.image.php), [Imagick](https://www.php.net/manual/en/book.imagick.php) or [Imageflow](https://github.com/imazen/imageflow).

## Examples

Convert the image to another format: **png**, **jpg**, **jpeg**, **webp**

```
http://localhost:8080/pathname/image.jpg?format=webp
http://localhost:8080/pathname/image.jpg?format=png
```

Resize the image: **width**, **height**

```
http://localhost:8080/pathname/image.jpg?format=webp&with=100
http://localhost:8080/pathname/image.jpg?format=webp&with=100&height=100
```

Set quality: `0` - `100`

```
http://localhost:8080/pathname/image.jpg?format=webp&quality=80
```

Set blurred: `true`, `false`

```
http://localhost:8080/pathname/image.jpg?format=webp&blurred=true
```

## Getting started

First of all, clone the repository from GitHub:

```ssh
git clone https://github.com/ucoderdev/php-image-server
```

Switch to the cloned folder in your terminal:

```ssh
cd php-image-server
```

Install dependencies:

```ssh
composer update
```

Create the configuration file:

```ssh
php bin/setup.php
```

## Configuration

Before running the server, change the `config.php` file.

### Images directory

The server will find all images from the `images_dir` directory. Before starting the server, set it in the `config.php` file.

### PHP Image Extensions

The project supports `PHP extensions` such as [GD](https://www.php.net/manual/en/book.image.php), [Imagick](https://www.php.net/manual/en/book.imagick.php) or [Imageflow](https://github.com/imazen/imageflow). You can use any of them by changing the `image_extension` value.

```php
return array(
    'images_dir' => '/path/domain.com/uploads',
    'image_extension' => 'gd', // gd, imagick, imageflow
    'port' => 8080,
    'ip_address' => '127.0.0.1',
);
```

## Start the server

After **configuration** the server, you can start using CLI:

```ssh
php bin/run.php
```

Note: By default the server starts on port `8080`.

Note: By default the server starts at `127.0.0.1`.

If you want to run the server in a background, you can use **PM2** or **nohup**. 

Running in [PM2](https://pm2.keymetrics.io/docs/usage/quick-start/):

```ssh
pm2 start bin/run.php --name=php-image-server
```

Running in [nohup](https://www.digitalocean.com/community/tutorials/nohup-command-in-linux):

```ssh
nohup php bin/run.php &
```

## NGINX

Below is the NGINX configration:

```nginx
server {
    ...

    autoindex off;

    location / {
        autoindex off;
        proxy_pass  http://127.0.0.1:8080;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Port $server_port;
    }

    ...
}
```

## Imageflow installation

Install Rust by running

```
curl https://sh.rustup.rs -sSf | sh -s -- -y --default-toolchain stable
```

Ensure build tools are installed (git, curl, wget, gcc, g++, nasm, pkg-config, openssl, ca-certificates)

```
sudo apt-get install git wget curl build-essential pkg-config libssl-dev libpng-dev nasm
```

Clone and cd into this repository

```
git clone https://github.com/imazen/imageflow.git && cd imageflow
```

If you are using bash on any platform, you should be able to use `build.sh`

- `./build.sh clean` : clean
- `./build.sh release` : generate release binaries
- `./build.sh install` :  install release binaries to /usr/local

Detailed installation:

`https://github.com/imazen/imageflow/blob/main/README.md#building-from-source-without-docker`

# License

The repository is open-sourced software licensed under the [MIT license](https://opensource.org/license/MIT).

# Security Vulnerabilities

If you discover a security vulnerability, please contact me at hello@ucoder.dev.
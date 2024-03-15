<?php

namespace App;

use Extensions\Image;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class Router
{
    /**
     * Base directory
     *
     * @var string
     */
    private $base_dir;

    /**
     * Images dir
     *
     * @var string
     */
    private $images_dir;

    /**
     * Image extension
     *
     * @var string
     */
    private $image_extension;

    /**
     * Class constructor
     *
     * @param string $base_dir
     * @param string $images_dir
     * @param string $image_extension
     */
    public function __construct($base_dir, $images_dir, $image_extension)
    {
        $this->base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR);
        $this->images_dir = rtrim($images_dir, DIRECTORY_SEPARATOR);
        $this->image_extension = strtolower($image_extension);
    }

    /**
     * Response
     *
     * @param ServerRequestInterface $request
     * @return React\Http\Message\Response
     */
    public function response(ServerRequestInterface $request)
    {
        $uri = $request->getUri();
        $uri_path = $uri->getPath();

        if ($uri_path != '/') {
            $file = $this->images_dir . urldecode($uri_path);

            if (is_file($file) && is_readable($file)) {
                $queryParams = $request->getQueryParams();

                return $this->responseConvertedFile($file, $queryParams);
            }
        }

        return $this->notFound();
    }

    /**
     * Not found
     *
     * @return React\Http\Message\Response
     */
    public function notFound()
    {
        return new Response(
            Response::STATUS_NOT_FOUND,
            array(
                'Content-Type' => 'text/plain; charset=utf-8'
            ),
            "File not found!"
        );
    }

    /**
     * Response file
     *
     * @param string $file
     * @return React\Http\Message\Response
     */
    public function responseFile($file)
    {
        return new Response(
            Response::STATUS_OK,
            array(
                'Content-Type' => mime_content_type($file),
                'Content-Length' => getimagesize($file),
            ),
            file_get_contents($file)
        );
    }

    /**
     * Response converted file
     *
     * @param string $file
     * @param array $queryParams
     * @return void
     */
    private function responseConvertedFile($file, $queryParams = array())
    {
        $run = false;
        $image_extension = $this->image_extension;

        $allowed_types = array(
            'png', 'jpg', 'jpeg', 'webp'
        );

        $pathinfo = pathinfo($file);
        $extension = $pathinfo['extension'];

        // Extension
        $ext = $extension;

        if (isset($queryParams['convert']) && in_array($queryParams['convert'], $allowed_types)) {
            $ext = $queryParams['convert'];
            $run = true;
        }

        // Quality
        $quality = 100;

        // Blurred
        $blurred = false;

        if (isset($queryParams['blurred']) && $queryParams['blurred'] == 'true') {
            $run = true;
            $blurred = true;
            $ext = 'jpeg';
            $quality = 100;
        }

        // Width
        $width = 0;

        if (isset($queryParams['width']) && is_numeric($queryParams['width'])) {
            $width = (int) $queryParams['width'];

            if ($width > 0) {
                $run = true;
            }
        }

        // Height
        $height = 0;

        if (isset($queryParams['height']) && is_numeric($queryParams['height'])) {
            $height = (int) $queryParams['height'];

            if ($height > 0) {
                $run = true;
            }
        }

        // Cache dir
        $cache_dir = $this->base_dir . DIRECTORY_SEPARATOR . 'cache';

        // Hash file
        $hash_array = array(
            'format:' . ($ext ? $ext : $extension),
            'width:' . $width,
            'height:' . $height,
            'quality:' . $quality,
            'blurred:' . ($blurred ? 'yes' : 'no'),
        );

        $hash_key = implode('-', $hash_array);

        if ($run && $image_extension) {
            $cache_dir = $cache_dir . DIRECTORY_SEPARATOR . $ext;
            $cache_file = $cache_dir . DIRECTORY_SEPARATOR . md5($file . $hash_key) . '.' . $ext;

            if (!is_dir($cache_dir)) {
                mkdir($cache_dir, 0755, true);
            }

            if (is_file($cache_file) && is_readable($cache_file)) {
                return $this->responseFile($cache_file);
            }

            // Image
            $image = null;

            if ($image_extension == Image::GD) {
                $image = new \Extensions\GD($file);
            } elseif ($image_extension == Image::IMAGICK) {
                $image = new \Extensions\Imagick($file);
            } elseif ($image_extension == Image::IMAGEFLOW) {
                $image = new \Extensions\Imageflow($file);
            }

            if ($image) {
                $image->setBlurred($blurred);
                $image->setResizeWidth($width);
                $image->setResizeHeight($height);
                $image->setQuality($quality);
                $image->writeFile($cache_file);
            }

            if (is_file($cache_file) && is_readable($cache_file)) {
                return $this->responseFile($cache_file);
            }
        }

        return $this->responseFile($file);
    }
}

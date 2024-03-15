<?php

namespace Extensions;

/**
 * Imagick
 * 
 * Documentation: https://www.php.net/manual/en/book.imagick.php
 */
class Imagick extends Image
{
    const TOPLEFT = 'topleft';
    const TOPCENTER = 'topcenter';
    const TOPRIGHT = 'topright';
    const MIDDLELEFT = 'middleleft';
    const MIDDLECENTER = 'middlecenter';
    const MIDDLERIGHT = 'middleright';
    const BOTTOMLEFT = 'bottomleft';
    const BOTTOMCENTER = 'bottomcenter';
    const BOTTOMRIGHT = 'bottomright';

    /**
     * The Imagick class
     *
     * @var \Imagick
     */
    protected $imagick;

    /**
     * Class constructor
     *
     * @param string $file
     */
    public function __construct(string $file)
    {
        parent::__construct($file);

        $this->imagick = new \Imagick();
        $this->imagick->setResolution($this->getFileWidth(), $this->getFileHeight());
        $this->imagick->readImage($this->getFile());
    }

    /**
     * Blurred image
     *
     * @param integer $radius
     * @param integer $sigma
     * @return void
     */
    public function blurred(int $radius = 5, int $sigma = 3)
    {
        $this->imagick->blurImage($radius, $sigma);
    }

    /**
     * Crops image according to the given width, height and crop position
     *
     * @param integer $width
     * @param integer $height
     * @param integer $position
     * @return void
     */
    public function crop(int $width, int $height, $position = self::MIDDLECENTER)
    {
        $this->imagick->cropThumbnailImage($width, $height);
    }

    /**
     * Resizes image according to the given width and height
     *
     * @param integer $width
     * @param integer $height
     * @param integer $blurFactor
     * @return static
     */
    public function resize(int $width, int $height, $blurFactor = 1)
    {
        $this->imagick->resizeImage($width, $height, \Imagick::FILTER_CATROM, $blurFactor);
    }

    /**
     * Resizes image according to the given width (height proportional)
     *
     * @param integer $width
     * @param integer $blurFactor
     * @return void
     */
    public function resizeToWidth($width, $blurFactor = 1)
    {
        $ratio  = $width / $this->getFileWidth();
        $height = (int) round($this->getFileHeight() * $ratio);

        $this->resize($width, $height, $blurFactor);
    }

    /**
     * Resizes image according to the given height (width proportional)
     *
     * @param integer $height
     * @param integer $blurFactor
     * @return void
     */
    public function resizeToHeight($height, $blurFactor = 1)
    {
        $ratio = $height / $this->getFileHeight();
        $width = (int) round($this->getFileWidth() * $ratio);

        $this->resize($width, $height, $blurFactor);
    }

    /**
     * Save file
     *
     * @param string $filename
     * @return void
     */
    public function save(string $filename)
    {
        $quality = $this->getQuality();

        // Ext
        $extension = $this->pathExtension($filename);

        if ($extension == 'jpg' || $extension == 'jpeg') {
            $this->imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
        } elseif ($extension == 'png') {
            $this->imagick->setImageCompression(\Imagick::COMPRESSION_ZIP);
        } elseif ($extension == 'gif') {
            $this->imagick->setImageCompression(\Imagick::COMPRESSION_LZW);
        }

        // Set format
        $this->imagick->setImageFormat($extension);

        if ($extension == 'webp') {
            $this->imagick->setOption('webp:lossless', 'true');
        }

        $this->imagick->setImageCompressionQuality($quality);
        $this->imagick->writeImage($filename);
        $this->imagick->destroy();
    }

    /**
     * Write file
     *
     * @param string $file
     * @return void
     */
    public function writeFile(string $file)
    {
        $blurred = $this->getBlurred();
        $resize_width = $this->getResizeWidth();
        $resize_height = $this->getResizeHeight();

        // Resize to width
        if ($resize_width > 0 && $resize_height == 0) {
            $this->resizeToWidth($resize_width);
        }

        // Resize to height
        if ($resize_height > 0 && $resize_width == 0) {
            $this->resizeToHeight($resize_height);
        }

        // Crop image
        if ($resize_width > 0 && $resize_height > 0) {
            $this->crop($resize_width, $resize_height);
        }

        // Blurred
        if ($blurred) {
            $this->blurred(8, 5);
        }

        // Save
        $this->save($file);
    }
}

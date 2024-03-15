<?php

namespace Extensions;

/**
 * Imageflow
 * 
 * Documentation: https://docs.imageflow.io/ 
 */
class Imageflow extends Image
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
     * Commands list
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Class constructor
     * 
     * @param string $file
     */
    public function __construct($file)
    {
        $this->commands = array();

        parent::__construct($file);
    }

    /**
     * Crops image according to the given width, height and crop position
     *
     * @param integer $width
     * @param integer $height
     * @param integer $position
     * @return void
     */
    public function crop($width, $height, $position = self::MIDDLECENTER)
    {
        $this->resize($width, $height, 'crop');

        if ($position) {
            $this->scale($position);
        }
    }

    /**
     * Resizes image according to the given width and height
     *
     * @param integer $width
     * @param integer $height
     * @param string $mode
     * @return void
     */
    public function resize(int $width, int $height, $mode = 'max')
    {
        if ($width > 0) {
            $this->commands[] = 'width=' . $width;
        }

        if ($height > 0) {
            $this->commands[] = 'height=' . $height;
        }

        if ($mode) {
            $this->commands[] = 'mode=' . $mode;
        }
    }

    /**
     * Resizes image according to the given width (height proportional)
     *
     * @param integer $width
     * @param string $mode
     * @return void
     */
    public function resizeToWidth($width, $mode = null)
    {
        $ratio  = $width / $this->getFileWidth();
        $height = (int) round($this->getFileHeight() * $ratio);

        $this->resize($width, $height, $mode);
    }

    /**
     * Resizes image according to the given height (width proportional)
     *
     * @param integer $height
     * @param string $mode
     * @return void
     */
    public function resizeToHeight($height, $mode = null)
    {
        $ratio = $height / $this->getFileHeight();
        $width = (int) round($this->getFileWidth() * $ratio);

        $this->resize($width, $height, $mode);
    }

    /**
     * Save file
     *
     * @param string $file
     * @return void
     */
    public function save(string $filename)
    {
        $quality = $this->getQuality();

        // Ext
        $extension = $this->pathExtension($filename);

        // Commands
        $cmd = array_merge($this->commands, array(
            "format=" . $extension,
            "ignore_icc_errors=true"
        ));

        if ($extension == 'jpg' || $extension == 'jpeg') {
            $cmd[] = "jpeg.quality=" . $quality;
            $cmd[] = "jpeg.turbo=false";
        }

        if ($extension == 'png') {
            $cmd[] = "png.quality=" . $quality;

            if ($quality < 90) {
                $cmd[] = "png.lossless=true";
            }
        }

        if ($extension == 'webp') {
            $cmd[] = "webp.quality=" . $quality;

            if ($quality < 90) {
                $cmd[] = "webp.lossless=true";
            }
        }

        $command = "imageflow_tool v1/querystring --quiet --in " . $this->getFile() . ' --out ' . $filename . ' --command "' . implode('&', $cmd) . '"';

        // Exec
        exec($command);
    }

    /**
     * Scale
     *
     * @param string $scale
     * @return void
     */
    public function scale($scale)
    {
        $this->commands[] = 'scale=' . $scale;
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

        // Save
        $this->save($file);

        // Blurred 
        if ($blurred === true) {
            $gd = new GD($file);
            $gd->createBlurImage($file);
        }
    }
}

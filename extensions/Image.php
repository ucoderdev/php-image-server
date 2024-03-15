<?php

namespace Extensions;

class Image
{
    const GD = 'gd';
    const IMAGICK = 'imagick';
    const IMAGEFLOW = 'imageflow';

    /**
     * Blurred image
     *
     * @var boolean
     */
    private $blurred = false;

    /**
     * Image file
     *
     * @var string
     */
    private $file;

    /**
     * Image file width
     *
     * @var integer
     */
    private $file_width = 0;

    /**
     * Image file height
     *
     * @var integer
     */
    private $file_height = 0;

    /**
     * Resize width
     *
     * @var integer
     */
    private $resize_width = 0;

    /**
     * Reize height
     *
     * @var integer
     */
    private $resize_height = 0;

    /**
     * Quality
     *
     * @var integer
     */
    private $quality = 0;

    /**
     * Class constructor
     *
     * @param string $file
     */
    public function __construct($file)
    {
        if (!is_file($file)) {
            throw new \Exception("Error: The given file not found!");
        }

        if (!is_readable($file)) {
            throw new \Exception("Error: The given file not readable!");
        }

        // Image file
        $this->file = $file;

        // File sizes
        $image_size = getimagesize($file);

        $this->file_width = $image_size[0];
        $this->file_height = $image_size[1];
    }

    /**
     * Blurred image
     *
     * @return integer
     */
    public function getBlurred()
    {
        return $this->blurred;
    }

    /**
     * Get image file
     *
     * @return integer
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get image height
     *
     * @return integer
     */
    public function getFileHeight()
    {
        return $this->file_height;
    }

    /**
     * Get file width
     *
     * @return integer
     */
    public function getFileWidth()
    {
        return $this->file_width;
    }

    /**
     * Get resize height
     *
     * @return integer
     */
    public function getResizeHeight()
    {
        return $this->resize_height;
    }

    /**
     * Get resize width
     *
     * @return integer
     */
    public function getResizeWidth()
    {
        return $this->resize_width;
    }

    /**
     * Get quality
     *
     * @return integer
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * Get path extension
     *
     * @param string $path
     * @return string
     */
    public function pathExtension($path)
    {
        $pathInfo = (array) pathinfo($path);

        if (!array_key_exists('extension', $pathInfo)) {
            throw new \InvalidArgumentException(sprintf('Cannot find extension from %s', $path));
        }

        return $pathInfo['extension'];
    }

    /**
     * Set blurred
     *
     * @param boolean $blurred
     * @return void
     */
    public function setBlurred(bool $blurred)
    {
        $this->blurred = $blurred;
    }

    /**
     * Set resize height
     *
     * @param integer $height
     * @return void
     */
    public function setResizeHeight(int $height)
    {
        $this->resize_height = $height;
    }

    /**
     * Set resize width
     *
     * @param integer $width
     * @return void
     */
    public function setResizeWidth(int $width)
    {
        $this->resize_width = $width;
    }

    /**
     * Set quality
     *
     * @param integer $width
     * @return void
     */
    public function setQuality(int $quality)
    {
        $this->quality = $quality;
    }
}

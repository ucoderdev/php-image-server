<?php

namespace Extensions;

use Gumlet\ImageResize;

/**
 * GD Image
 * Documentation: https://github.com/gumlet/php-image-resize
 */
class GD extends Image
{
    /**
     * Image Resize Class
     *
     * @var \Gumlet\ImageResize
     */
    protected $image;

    /**
     * Class constructor
     * 
     * @param string $file
     */
    public function __construct($file)
    {
        parent::__construct($file);

        $this->image = new ImageResize($this->getFile());
    }

    /**
     * Create blurred image
     *
     * @param string $from
     * @param string $to
     * @param integer $blurFactor
     * @return void
     */
    public function createBlurImage($filename, $blurFactor = 3)
    {
        $image = $this->imageCreateFrom($this->getFile());

        // blurFactor has to be an integer
        $blurFactor = round($blurFactor);

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        $smallestWidth = ceil($originalWidth * pow(0.5, $blurFactor));
        $smallestHeight = ceil($originalHeight * pow(0.5, $blurFactor));

        $prevImage = $image;
        $prevWidth = $originalWidth;
        $prevHeight = $originalHeight;

        for ($i = 0; $i < $blurFactor; $i += 1) {
            // determine dimensions of next image
            $nextWidth = $smallestWidth * pow(2, $i);
            $nextHeight = $smallestHeight * pow(2, $i);

            // resize previous image to next size
            $nextImage = imagecreatetruecolor($nextWidth, $nextHeight);
            imagecopyresampled(
                $nextImage,
                $prevImage,
                0,
                0,
                0,
                0,
                $nextWidth,
                $nextHeight,
                $prevWidth,
                $prevHeight
            );

            // apply blur filter
            imagefilter($nextImage, IMG_FILTER_GAUSSIAN_BLUR);

            // now the new image becomes the previous image for the next step
            $prevImage = $nextImage;
            $prevWidth = $nextWidth;
            $prevHeight = $nextHeight;
        }

        // scale back to original size and blur one more time
        imagecopyresampled(
            $image,
            $nextImage,
            0,
            0,
            0,
            0,
            $originalWidth,
            $originalHeight,
            $nextWidth,
            $nextHeight
        );

        imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);

        // clean up
        imagedestroy($prevImage);

        // Save
        return $this->image->save($filename, null, 100);
    }

    /**
     * Crete image from
     *
     * @param string $file
     * @return mixed
     */
    public function imageCreateFrom(string $file)
    {
        $imageFormat = array(
            IMAGETYPE_GIF => 'gif',
            IMAGETYPE_JPEG => 'jpeg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
        );

        try {
            $ext = exif_imagetype($file);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage()) . PHP_EOL;
        }

        if (!array_key_exists($ext, $imageFormat)) {
            throw new \InvalidArgumentException(sprintf('The %s extension is unsupported', $ext));
        }

        $format = $imageFormat[$ext];

        switch ($format) {
            case 'gif':
                $image = imagecreatefromgif($file);
                break;
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($file);
                break;
            case 'png':
                $image = imagecreatefrompng($file);
                break;
            case 'webp':
                $image = imagecreatefromwebp($file);
                break;
            default:
                $image = null;
        }

        return $image;
    }

    /**
     * Save file
     *
     * @param string $filename
     * @return void
     */
    public function save(string $filename)
    {
        $this->image->save($filename);
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
            $this->image->resizeToWidth($resize_width);
        }

        // Resize to height
        if ($resize_height > 0 && $resize_width == 0) {
            $this->image->resizeToHeight($resize_height);
        }

        // Crop image
        if ($resize_width > 0 && $resize_height > 0) {
            $this->image->crop($resize_width, $resize_height);
        }

        // Save
        $this->save($file);

        // Blurred image
        if ($blurred === true) {
            $gd = new GD($file);
            $gd->createBlurImage($file);
        }
    }
}

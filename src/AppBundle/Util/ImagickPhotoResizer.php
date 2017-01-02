<?php

namespace AppBundle\Util;

class ImagickPhotoResizer implements PhotoResizerInterface
{
    private $img;

    public function __construct($inputFilePath)
    {
        $this->img = new \Imagick($inputFilePath);
        $this->autoRotate($this->img);
    }

    public function setInputFile($inputFilePath)
    {
        $this->img = new \Imagick($inputFilePath);
        $this->autoRotate($this->img);

        return $this;
    }

    public function resize($outputFilePath, $maxWidth, $maxHeight)
    {
        try {
            $aspectRatio = $this->img->getImageWidth() / $this->img->getImageHeight();
            $width = $this->img->getImageWidth();
            $height = $this->img->getImageHeight();

            if ($width > $maxWidth) {
                $width = $maxWidth;
                $height = $width * (1 / $aspectRatio);
            }

            if ($height > $maxHeight) {
                $height = min($maxHeight, $maxWidth * (1 / $aspectRatio));
                $width = $height * $aspectRatio;
            }

            $this->img->scaleImage($width, $height);
            $this->img->writeImage($outputFilePath);
        } catch (\ImagickException $e) {
            throw new PhotoResizingException($e);
        }
    }

    public function scale($outputFilePath, $scale)
    {
        try {
            $this->img->scaleImage($this->img->getImageWidth() * $scale, 0);
            $this->img->writeImage($outputFilePath);
        } catch (\ImagickException $e) {
            throw new PhotoResizingException($e);
        }
    }

    public function resizeToSquare($outputFilePath, $side)
    {
        try {
            $this->img->cropThumbnailImage($side, $side);
            $this->img->writeImage($outputFilePath);
        } catch (\ImagickException $e) {
            throw new PhotoResizingException($e);
        }
    }

    // Adapted from http://php.net/manual/fr/imagick.getimageorientation.php#111448
    private function autoRotate($image)
    {
        $orientation = $image->getImageOrientation();

        switch($orientation) {
            case \Imagick::ORIENTATION_BOTTOMRIGHT:
                $image->rotateimage("#000", 180);
                break;

            case \Imagick::ORIENTATION_RIGHTTOP:
                $image->rotateimage("#000", 90);
                break;

            case \Imagick::ORIENTATION_LEFTBOTTOM:
                $image->rotateimage("#000", -90);
                break;
        }

        $image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
    }
}

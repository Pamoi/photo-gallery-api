<?php

namespace AppBundle\Util;

class ImagickPhotoResizer implements PhotoResizerInterface
{
    private $img;

    public function __construct($inputFilePath)
    {
        $this->img = new \Imagick($inputFilePath);
    }

    public function setInputFile($inputFilePath)
    {
        $this->img = new \Imagick($inputFilePath);

        return $this;
    }

    public function resize($outputFilePath, $width, $height)
    {
        $this->img->resizeImage($width, $height, \Imagick::FILTER_CATROM, 1);
        $this->img->writeImage($outputFilePath);
    }

    public function resizeFixedWidth($outputFilePath, $width)
    {
        $this->img->scaleImage($width, 0);
        $this->img->writeImage($outputFilePath);
    }

    public function resizeFixedHeight($outputFilePath, $height)
    {
        $this->img->scaleImage(0, $height);
        $this->img->writeImage($outputFilePath);
    }

    public function scale($outputFilePath, $scale)
    {
        $this->img->scaleImage($this->img->getImageWidth() * $scale, 0);
        $this->img->writeImage($outputFilePath);
    }

    public function resizeToSquare($outputFilePath, $side)
    {
        // TODO
    }
}
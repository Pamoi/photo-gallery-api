<?php

namespace AppBundle\Util;

class ImagickPhotoResizer implements PhotoResizerInterface
{
    public function resize($inputFile, $outputFile, $maxWidth, $maxHeight)
    {
        $img = new \Imagick($inputFile);
        $this->autoRotate($img);

        try {
            $aspectRatio = $img->getImageWidth() / $img->getImageHeight();
            $width = $img->getImageWidth();
            $height = $img->getImageHeight();

            if ($width > $maxWidth) {
                $width = $maxWidth;
                $height = $width * (1 / $aspectRatio);
            }

            if ($height > $maxHeight) {
                $height = min($maxHeight, $maxWidth * (1 / $aspectRatio));
                $width = $height * $aspectRatio;
            }

            $img->scaleImage($width, $height);
            $img->writeImage($outputFile);
        } catch (\ImagickException $e) {
            throw new PhotoResizingException($e);
        }
    }

    public function scale($inputFile, $outputFile, $scale)
    {
        $img = new \Imagick($inputFile);
        $this->autoRotate($img);

        try {
            $img->scaleImage($img->getImageWidth() * $scale, 0);
            $img->writeImage($outputFile);
        } catch (\ImagickException $e) {
            throw new PhotoResizingException($e);
        }
    }

    public function resizeToSquare($inputFile, $outputFile, $side)
    {
        $img = new \Imagick($inputFile);
        $this->autoRotate($img);

        try {
            $img->cropThumbnailImage($side, $side);
            $img->writeImage($outputFile);
        } catch (\ImagickException $e) {
            throw new PhotoResizingException($e);
        }
    }

    public function cropToAspectRatio($inputFile, $outputFile, $aspectRatio, $maxWidth)
    {
        $img = new \Imagick($inputFile);
        $this->autoRotate($img);

        if ($img->getImageWidth() < $maxWidth) {
            $maxWidth = $img->getImageWidth();
        }

        try {
            $img->cropThumbnailImage($maxWidth, $maxWidth / $aspectRatio);
            $img->writeImage($outputFile);
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

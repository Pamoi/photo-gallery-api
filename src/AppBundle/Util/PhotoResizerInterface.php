<?php

namespace AppBundle\Util;

interface PhotoResizerInterface
{
    /**
     * Sets the file from which the photo to resize will be read.
     *
     * @param string $inputFilePath
     */
    public function setInputFile($inputFilePath);

    /**
     * Resize down the image preserving the aspect ratio with width and height limited by
     * $maxWidth and $maxHeight.
     *
     * @param string $outputFilePath
     * @param int $maxWidth
     * @param int $maxHeight
     *
     * @throws PhotoResizingException if an error occurs during resizing.
     */
    public function resize($outputFilePath, $maxWidth, $maxHeight);

    /**
     * Scales the image by a given factor.
     *
     * @param string $outputFilePath
     * @param int $scale
     *
     * @throws PhotoResizingException if an error occurs during resizing.
     */
    public function scale($outputFilePath, $scale);

    /**
     * Resize and crop the image to a square of fixed size.
     * The square covers the largest possible area of the image.
     *
     * @param string $outputFilePath
     * @param int $side
     *
     * @throws PhotoResizingException if an error occurs during resizing.
     */
    public function resizeToSquare($outputFilePath, $side);
}
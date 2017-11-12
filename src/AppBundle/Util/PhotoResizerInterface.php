<?php

namespace AppBundle\Util;

interface PhotoResizerInterface
{
    /**
     * Resize down an image preserving the aspect ratio with width and height limited by
     * $maxWidth and $maxHeight.
     *
     * @param string $inputFile
     * @param string $outputFile
     * @param int $maxWidth
     * @param int $maxHeight
     *
     * @throws PhotoResizingException if an error occurs during resizing.
     */
    public function resize($inputFile, $outputFile, $maxWidth, $maxHeight);

    /**
     * Scales an image by a given factor.
     *
     *
     * @param string $inputFile
     * @param string $outputFile
     * @param int $scale
     *
     * @throws PhotoResizingException if an error occurs during resizing.
     */
    public function scale($inputFile, $outputFile, $scale);

    /**
     * Resize and crop an image to a square of fixed size.
     * The square covers the largest possible area of the image.
     *
     * @param string $inputFile
     * @param string $outputFile
     * @param int $side
     *
     * @throws PhotoResizingException if an error occurs during resizing.
     */
    public function resizeToSquare($inputFile, $outputFile, $side);

    /**
     * Resize an image to fit in $maxWidth and crop it to fit $aspectRatio.
     *
     * @param string $inputFile
     * @param string $outputFile
     * @param float $aspectRatio
     * @param int $maxWidth
     *
     * @throws PhotoResizingException if an error occurs during resizing.
     */
    public function cropToAspectRatio($inputFile, $outputFile, $aspectRatio, $maxWidth);
}
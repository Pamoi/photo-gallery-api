<?php
/**
 * Created by PhpStorm.
 * User: matthieu
 * Date: 03/01/16
 * Time: 13:46
 */
namespace AppBundle\Util;

interface PhotoResizerInterface
{
    /**
     * Sets the file from which the photo to resize will be read.
     *
     * @param string $inputFilePath
     * @return mixed
     */
    public function setInputFile($inputFilePath);

    /**
     * Resize the image with a given width and height.
     *
     * @param string $outputFilePath
     * @param int $width
     * @param int $height
     * @return bool
     */
    public function resize($outputFilePath, $width, $height);

    /**
     * Resize the image with a fixed width and adapts the height to preserve aspect ratio.
     *
     * @param string $outputFilePath
     * @param int $width
     * @return bool
     */
    public function resizeFixedWidth($outputFilePath, $width);

    /**
     * Resize the image with a fixed height and adapts the width to preserve aspect ratio.
     *
     * @param string $outputFilePath
     * @param int $height
     * @return bool
     */
    public function resizeFixedHeight($outputFilePath, $height);

    /**
     * Scales the image to a multiple of the original image.
     *
     * @param string $outputFilePath
     * @param int $scale
     * @return bool
     */
    public function scale($outputFilePath, $scale);

    /**
     * Resize and crop the image to a square of fixed size.
     * The square covers the biggest possible area of the image.
     *
     * @param string $outputFilePath
     * @param int $side
     * @return mixed
     */
    public function resizeToSquare($outputFilePath, $side);
}
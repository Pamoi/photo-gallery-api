<?php

namespace AppBundle\Service\ColorExtractor;

interface ColorExtractorInterface
{
    /**
     * Extract the dominant color from an image.
     *
     * @param $inputFile string The file name of the image.
     * @return string The hex string representing the color.
     */
    public function extractMainColor($inputFile);
}
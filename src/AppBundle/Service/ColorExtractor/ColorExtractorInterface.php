<?php

namespace AppBundle\Service\ColorExtractor;

interface ColorExtractorInterface
{
    /**
     * Extract the dominant color from an image.
     *
     * @param $inputFile string The file name of the image.
     * @return array The red, green and blue values of the dominant color.
     */
    public function extractMainColor($inputFile);
}
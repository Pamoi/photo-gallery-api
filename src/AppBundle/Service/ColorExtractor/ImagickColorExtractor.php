<?php

namespace AppBundle\Service\ColorExtractor;


class ImagickColorExtractor implements ColorExtractorInterface
{

    /**
     * Extract the dominant color from an image.
     *
     * @param $inputFile string The file name of the image.
     * @return string The hex string representing the color.
     * @throws ColorExtractionException
     */
    public function extractMainColor($inputFile)
    {
        $img = new \Imagick($inputFile);

        $pixels = $img->getImageHistogram();

        if (count($pixels) < 1) {
            return array(0, 0, 0);
        } else {
            $topColor = $pixels[0];
            $topCount = $pixels[0]->getColorCount();

            foreach ($pixels as $p) {
                if ($p->getColorCount() > $topCount) {
                    $topCount = $p->getColorCount();
                    $topColor = $p->getColor();
                }
            }

            return "#" .
                substr("0".dechex($topColor['r']),-2) .
                substr("0".dechex($topColor['g']),-2) .
                substr("0".dechex($topColor['b']),-2);
        }
    }
}
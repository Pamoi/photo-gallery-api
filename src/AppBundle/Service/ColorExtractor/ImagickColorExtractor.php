<?php

namespace AppBundle\Service\ColorExtractor;


class ImagickColorExtractor implements ColorExtractorInterface
{

    /**
     * Extract the dominant color from an image.
     *
     * @param $inputFile string The file name of the image.
     * @return array The red, green and blue values of the dominant color.
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

            return array($topColor['r'], $topColor['g'], $topColor['b']);
        }
    }
}
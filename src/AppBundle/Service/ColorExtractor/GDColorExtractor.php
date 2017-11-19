<?php

namespace AppBundle\Service\ColorExtractor;


class GDColorExtractor implements ColorExtractorInterface
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
        $imageType = exif_imagetype($inputFile);

        if ($imageType == IMAGETYPE_JPEG) {
            $i = imagecreatefromjpeg($inputFile);
        } else if ($imageType == IMAGETYPE_PNG) {
            $i = imagecreatefrompng($inputFile);
        } else {
            throw new ColorExtractionException("Unsupported file type.");
        }

        $rTotal = 0;
        $gTotal = 0;
        $bTotal = 0;
        $total = 0;

        for ($x = 0; $x < imagesx($i); $x++) {
            for ($y = 0; $y < imagesy($i); $y++) {
                $rgb = imagecolorat($i, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $rTotal += $r;
                $gTotal += $g;
                $bTotal += $b;
                $total++;
            }
        }

        $rAverage = round($rTotal / $total);
        $gAverage = round($gTotal / $total);
        $bAverage = round($bTotal / $total);

        return "#" .
            substr("0".dechex($rAverage),-2) .
            substr("0".dechex($gAverage),-2) .
            substr("0".dechex($bAverage),-2);
    }
}
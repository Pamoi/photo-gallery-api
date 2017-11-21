<?php

namespace AppBundle\Service\ColorExtractor;
use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;

/**
 * Class LeagueColorExtractor implements the ColorExtractorInterface using the
 * color-extractor (https://github.com/thephpleague/color-extractor) library.
 *
 * @package AppBundle\Service\ColorExtractor
 */
class LeagueColorExtractor implements ColorExtractorInterface
{
    private static $DARKENING_FACTOR = 0.8;

    /**
     * Extract the dominant color from an image.
     *
     * @param $inputFile string The file name of the image.
     * @return string The hex string representing the color.
     */
    public function extractMainColor($inputFile)
    {
        $palette = Palette::fromFilename($inputFile);
        $extractor = new ColorExtractor($palette);
        $colors = $extractor->extract();

        $color = 0;

        if (count($colors) > 0) {
            $color = $colors[0];
        }

        $r = round(LeagueColorExtractor::$DARKENING_FACTOR * ($color >> 16 & 0xFF));
        $g = round(LeagueColorExtractor::$DARKENING_FACTOR * ($color >> 8 & 0xFF));
        $b = round(LeagueColorExtractor::$DARKENING_FACTOR * ($color & 0xFF));

        $darkenedColor = $r * 65536 + $g * 256 + $b;

        return Color::fromIntToHex($darkenedColor);
    }
}
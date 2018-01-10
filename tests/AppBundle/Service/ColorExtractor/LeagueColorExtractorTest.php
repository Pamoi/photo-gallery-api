<?php

namespace Tests\AppBundle\Service\ColorExtractor;


use AppBundle\Service\ColorExtractor\LeagueColorExtractor;

class LeagueColorExtractorTest extends \PHPUnit\Framework\TestCase {

    public function testExtractMainColor() {
        $extractor = new LeagueColorExtractor();
        $inputFile = __DIR__ . '/../../test_file.jpg';
        $expectedColor = "#000000";

        $color = $extractor->extractMainColor($inputFile);

        $this->assertEquals($expectedColor, $color);
    }
}
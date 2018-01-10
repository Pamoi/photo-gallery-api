<?php

namespace Tests\AppBundle\Service\ColorExtractor;


use AppBundle\Service\ColorExtractor\ImagickColorExtractor;

class ImagickColorExtractorTest extends \PHPUnit\Framework\TestCase {

    public function testExtractMainColor() {
        $extractor = new ImagickColorExtractor();
        $inputFile = __DIR__ . '/../../test_file.jpg';
        $expectedColor = "#ffffff";

        $color = $extractor->extractMainColor($inputFile);

        $this->assertEquals($expectedColor, $color);
    }
}
<?php

namespace Tests\AppBundle\Service\ColorExtractor;


use AppBundle\Service\ColorExtractor\GDColorExtractor;

class GDColorExtractorTest extends \PHPUnit_Framework_TestCase {

    public function testExtractMainColor() {
        $extractor = new GDColorExtractor();
        $inputFile = __DIR__ . '/../../test_file.jpg';
        $expectedColor = "#f0f0f0";

        $color = $extractor->extractMainColor($inputFile);

        $this->assertEquals($expectedColor, $color);
    }
}
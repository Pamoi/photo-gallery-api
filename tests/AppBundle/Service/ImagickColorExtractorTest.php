<?php

namespace Tests\AppBundle\Service;


use AppBundle\Service\ColorExtractor\ImagickColorExtractor;

class ImagickColorExtractorTest extends \PHPUnit_Framework_TestCase {

    public function testExtractMainColor() {
        $extractor = new ImagickColorExtractor();
        $inputFile = __DIR__ . '/../test_file.jpg';
        $expectedColor = array(255, 255, 255);

        $color = $extractor->extractMainColor($inputFile);

        $this->assertEquals($expectedColor, $color);
    }
}
<?php

namespace Tests\AppBundle\ServicePhotoResizer;


use AppBundle\Service\PhotoResizer\ImagickPhotoResizer;

class ImagickPhotoResizerTest extends \PHPUnit\Framework\TestCase {

    public function testResize() {
        $resizer = new ImagickPhotoResizer();
        $inputFile = __DIR__ . '/../../test_file.jpg';
        $outputFile = sys_get_temp_dir() . '/test_photo.jpg';
        $maxWidth = 300;
        $maxHeight = 300;
        $expectedHeight = 168; // Resizing preserves aspect ratio

        $resizer->resize($inputFile, $outputFile, $maxWidth, $maxHeight);

        $img = new \Imagick($outputFile);

        $this->assertEquals($maxWidth, $img->getImageWidth());
        $this->assertEquals($expectedHeight, $img->getImageHeight());
    }

    public function testThumbnail() {
        $resizer = new ImagickPhotoResizer();
        $inputFile = __DIR__ . '/../../test_file.jpg';
        $outputFile = sys_get_temp_dir() . '/test_photo.jpg';
        $thumbnailSize = 300;

        $resizer->resizeToSquare($inputFile, $outputFile, $thumbnailSize);

        $img = new \Imagick($outputFile);

        $this->assertEquals($thumbnailSize, $img->getImageWidth());
        $this->assertEquals($thumbnailSize, $img->getImageHeight());
    }

    public function testScale() {
        $resizer = new ImagickPhotoResizer();
        $inputFile = __DIR__ . '/../../test_file.jpg';
        $outputFile = sys_get_temp_dir() . '/test_photo.jpg';
        $scale = 0.1;

        $resizer->scale($inputFile, $outputFile, $scale);

        $img = new \Imagick($outputFile);

        $this->assertEquals(192, $img->getImageWidth());
        $this->assertEquals(108, $img->getImageHeight());
    }

    public function testCropToAspectRatio() {
        $resizer = new ImagickPhotoResizer();
        $inputFile = __DIR__ . '/../../test_file.jpg';
        $outputFile = sys_get_temp_dir() . '/test_photo.jpg';
        $aspectRatio = 1.0;
        $maxWidth = 300;

        $resizer->cropToAspectRatio($inputFile, $outputFile, $aspectRatio, $maxWidth);

        $img = new \Imagick($outputFile);

        $this->assertEquals(300, $img->getImageWidth());
        $this->assertEquals(300, $img->getImageHeight());
    }
}
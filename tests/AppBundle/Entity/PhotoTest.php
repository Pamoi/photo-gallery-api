<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Photo;
use AppBundle\Entity\User;

class PhotoTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $photo = new Photo();

        $this->assertInstanceOf('\DateTime', $photo->getDate());
        $this->assertInstanceOf('\DateTime', $photo->getUploadDate());
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $photo->getComments());
    }

    public function testFileNames()
    {
        $photo = new Photo();
        $this->setId($photo, 13);
        $photo->setExtension('jpeg');

        $this->assertEquals('13.jpeg', $photo->getFilename());
        $this->assertEquals('thumb-13.jpeg', $photo->getThumbFilename());
        $this->assertEquals('resized-13.jpeg', $photo->getResizedFilename());
    }

    public function testStoreTempFileNames()
    {
        $photo = new Photo();
        $this->setId($photo, 13);
        $photo->setExtension('png');

        $photo->storeTempFileNames();

        $correct = array(
            '13.png',
            'thumb-13.png',
            'resized-13.png',
            'cover-13.png'
        );

        $this->assertEquals($correct, $photo->getTempFileNames());
    }

    public function testToJson()
    {
        $author = new User();
        $this->setId($author, 11);
        $author->setUsername('Toto');

        $date = new \DateTime('11-01-2016 06:06:06');
        $creationDate = new \DateTime('11-01-2016');
        $photo = new Photo();
        $this->setId($photo, 22);
        $photo->setDate($date);
        $photo->setUploadDate($creationDate);
        $photo->setExtension('jpg');
        $photo->setAuthor($author);
        $photo->setDominantColor('#123456');

        $correct = array(
            'id' => 22,
            'date' => $date->format(\DateTime::ISO8601),
            'uploadDate' => $creationDate->format(\DateTime::ISO8601),
            'author' => array(
                'id' => 11,
                'username' => 'Toto'
            ),
            'comments' => array(),
            'dominantColor' => '#123456',
        );

        $this->assertEquals($correct, $photo->toJson());
    }

    private function setId($entity, $id)
    {
        // Using reflection to set ids for the tests
        $reflection = new \ReflectionObject($entity);
        $idField = $reflection->getProperty('id');
        $idField->setAccessible(true);
        $idField->setValue($entity, $id);
    }
}

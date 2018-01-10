<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Album;
use AppBundle\Entity\User;

class AlbumTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $album = new Album();

        $this->assertInstanceOf('\DateTime', $album->getDate());
        $this->assertInstanceOf('\DateTime', $album->getCreationDate());
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $album->getPhotos());
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $album->getComments());
        $this->assertTrue($album->isPublic());
    }

    public function testToJson()
    {
        $author = new User();
        $this->setId($author, 11);
        $author->setUsername('Toto');

        $date = new \DateTime('11-01-2016 06:06:06');
        $creationDate = new \DateTime('11-01-2016');
        $album = new Album();
        $this->setId($album, 22);
        $album->setDate($date);
        $album->setCreationDate($creationDate);
        $album->setTitle('My title');
        $album->setDescription('Superb photos !');
        $album->addAuthor($author);

        $correct = array(
            'id' => 22,
            'title' => 'My title',
            'description' => 'Superb photos !',
            'date' => $date->format(\DateTime::ISO8601),
            'creationDate' => $creationDate->format(\DateTime::ISO8601),
            'authors' => array(array(
                'id' => 11,
                'username' => 'Toto'
            )),
            'photos' => array(),
            'comments' => array()
        );

        $this->assertEquals($correct, $album->toJson());
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

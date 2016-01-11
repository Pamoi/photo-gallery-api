<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Album;
use AppBundle\Entity\User;

class PhotoTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $album = new Album();

        $this->assertInstanceOf('\DateTime', $album->getDate());
        $this->assertInstanceOf('\DateTime', $album->getCreationDate());
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $album->getPhotos());
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $album->getComments());
    }

    public function testToJson()
    {
        $author = new User();
        $this->setId($author, 11);
        $author->setUsername('Toto');

        $album = new Album();
        $this->setId($album, 22);
        $album->setDate(new \DateTime('11-01-2016'));
        $album->setCreationDate(new \DateTime('11-01-2016 06:06:06'));
        $album->setTitle('My title');
        $album->setDescription('Superb photos !');
        $album->addAuthor($author);

        $correct = array(
            'id' => 22,
            'title' => 'My title',
            'description' => 'Superb photos !',
            'date' => '11-01-2016 00:00:00',
            'creationDate' => '11-01-2016 06:06:06',
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

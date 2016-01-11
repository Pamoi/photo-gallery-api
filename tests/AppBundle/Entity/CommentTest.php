<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Comment;
use AppBundle\Entity\User;

class CommentTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $comment = new Comment();

        $this->assertInstanceOf('\DateTime', $comment->getDate());
        $this->assertEquals(null, $comment->getEditDate());
    }

    public function testToJson()
    {
        $author = new User();
        $this->setId($author, 11);
        $author->setUsername('Toto');

        $comment = new Comment();
        $this->setId($comment, 5);
        $comment->setDate(new \DateTime('11-01-2016'));
        $comment->setText('Some text here.');
        $comment->setAuthor($author);

        $correct = array(
            'id' => 5,
            'date' => '11-01-2016 00:00:00',
            'editDate' => null,
            'author' => array(
                'id' => 11,
                'username' => 'Toto'
            ),
            'text' => 'Some text here.',
        );

        $this->assertEquals($correct, $comment->toJson());

        $comment->setEditDate(new \DateTime('12-01-2016 15:30:22'));
        $correct['editDate'] = '12-01-2016 15:30:22';

        $this->assertEquals($correct, $comment->toJson());
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

<?php

namespace Tests\AppBundle\Controller;

use Firebase\JWT\JWT;

class AlbumControllerTest extends CommandWebTestCase
{
    private static $token;
    private static $albumId;
    private static $commentId;

    public static function setUpBeforeClass()
    {
        self::freeApplication();
        self::runCommand('doctrine:database:drop --force');
        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:update --force');
        self::runCommand('user:add toto toto@example.com pwd123');
        self::runCommand('user:add titi titi@example.com pwd123');

        $payload = array(
            'username' => 'toto'
        );

        $secret = self::getApplication()->getKernel()->getContainer()->getParameter('secret');
        self::$token = JWT::encode($payload, $secret);
    }

    public function testPostAlbum()
    {
        $client = static::createClient();

        $dateString = '12-01-2016';
        $date = new \DateTime($dateString);

        $client->request(
            'POST',
            '/album',
            array(
                'title' => 'The title',
                'description' => 'I am a description',
                'date' => $dateString,
                'authorsIds' => '2'
            ),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $album = (array) json_decode($client->getResponse()->getContent());
        $today = new \DateTime();

        $this->assertGreaterThan(0, $album['id']);
        self::$albumId = $album['id'];
        $this->assertEquals('The title', $album['title']);
        $this->assertEquals($date->format(\DateTime::ISO8601), $album['date']);
        $this->assertEquals(0, $today->diff(new \DateTime($album['creationDate']))->i);
        $this->assertEquals(0, count($album['photos']));
        $this->assertEquals(0, count($album['comments']));
        $this->assertEquals('toto', $album['authors'][0]->username);
        $this->assertEquals(2, count($album['authors']));
    }

    public function testInvalidPostAlbum()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/album',
            array(
                'title' => '',
                'description' => 'I am a description',
                'date' => '12-01-2016',
            ),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $json = (array) json_decode($client->getResponse()->getContent());

        $this->assertEquals('Invalid arguments.', $json['message']);
        $this->assertGreaterThan(0, count($json['list']));
    }

    public function testInvalidDatePostAlbum()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/album',
            array(
                'title' => 'The title',
                'description' => 'I am a description',
                'date' => 'toto',
            ),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $json = (array) json_decode($client->getResponse()->getContent());

        $this->assertEquals('Invalid arguments.', $json['message']);
        $this->assertEquals('date: Unable to parse string.', $json['list'][0]);
    }

    /**
     * @depends testPostAlbum
     */
    public function testGetAlbum()
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/album/list',
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $json = (array) json_decode($client->getResponse()->getContent());

        $this->assertEquals(1, count($json));
        $this->assertEquals('The title', $json[0]->title);
    }

    /**
     * @depends testPostAlbum
     */
    public function testGetAlbumPage2()
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/album/list/2',
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @depends testPostAlbum
     */
    public function testCommentAlbum()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/album/' . self::$albumId . '/comment',
            array(
                'text' => 'I am a comment !'
            ),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $json = (array) json_decode($client->getResponse()->getContent());

        $this->assertEquals(1, count($json['comments']));
        $this->assertEquals('I am a comment !', $json['comments'][0]->text);
        $this->assertEquals('toto', $json['comments'][0]->author->username);
        self::$commentId = $json['comments'][0]->id;
    }

    /**
     * @depends testPostAlbum
     */
    public function testSearchAlbum()
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/album/search/title',
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $json = (array) json_decode($client->getResponse()->getContent());

        $this->assertEquals(1, count($json));
        $this->assertEquals('The title', $json[0]->title);
    }

    /**
     * @depends testCommentAlbum
     */
    public function testDeleteComment()
    {
        $client = static::createClient();

        $client->request(
            'DELETE',
            '/album/' . self::$albumId . '/comment/' . self::$commentId,
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $json = (array) json_decode($client->getResponse()->getContent());

        $this->assertEquals('Comment deleted.', $json['message']);
    }

    /**
     * @depends testDeleteComment
     */
    public function testDeleteAlbum()
    {
        $client = static::createClient();

        $client->request(
            'DELETE',
            '/album/' . self::$albumId,
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $json = (array) json_decode($client->getResponse()->getContent());

        $this->assertEquals('Album deleted.', $json['message']);
    }
}
<?php

namespace Tests\AppBundle\Controller;

use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoControllerTest extends CommandWebTestCase
{
    private static $token;
    private static $uploadDir;

    public static function setUpBeforeClass()
    {
        self::$uploadDir = self::getApplication()->getKernel()->getContainer()->getParameter('photo_upload_dir');
        mkdir(self::$uploadDir);

        self::runCommand('doctrine:database:drop --force');
        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:update --force');
        self::runCommand('user:add toto toto@example.com pwd123');

        $payload = array(
            'username' => 'toto'
        );

        $secret = self::getApplication()->getKernel()->getContainer()->getParameter('secret');
        self::$token = JWT::encode($payload, $secret);
    }

    public static function tearDownAfterClass()
    {
        rmdir(static::$uploadDir);
    }

    public function testPostSinglePhoto()
    {
        $client = static::createClient();

        // Create an album
        $client->request(
            'POST',
            '/album',
            array(
                'title' => 'The title',
                'description' => 'I am a description',
                'date' => '12-01-2016',
            ),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $albumId = json_decode($client->getResponse()->getContent())->id;

        // Upload a photo
        $client->request(
            'POST',
            '/photo',
            array(
                'albumId' => $albumId,
                'date' => '12-01-2016',
                'photo' => new UploadedFile(__DIR__ . '/test_file.jpg', 'photo.jpg')
            ),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        echo $client->getResponse()->getContent();

        // Get the album back
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
        $json = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(1, count($json[0]['photos']));
    }
}
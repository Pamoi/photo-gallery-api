<?php

namespace Tests\AppBundle\Controller;

use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoControllerTest extends CommandWebTestCase
{
    private static $token;
    private static $uploadDir;
    private static $albumId;
    private static $photoId;
    private static $commentId;

    public static function setUpBeforeClass()
    {
        self::freeApplication();
        self::$uploadDir = self::getApplication()->getKernel()->getContainer()->getParameter('photo_upload_dir');

        if (!file_exists(self::$uploadDir)) {
            mkdir(self::$uploadDir);
        }

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
        // Recursive deletion of upload directory
        $dir = self::$uploadDir;
        $it = new \RecursiveDirectoryIterator($dir);
        $it = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($it as $file) {
            if ('.' === $file->getBasename() || '..' ===  $file->getBasename()) continue;
            if ($file->isDir()) rmdir($file->getPathname());
            else unlink($file->getPathname());
        }
        rmdir($dir);
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
        static::$albumId = json_decode($client->getResponse()->getContent())->id;

        // Upload a photo
        $copyPath = 'photo.jpg';
        copy(__DIR__ . '/test_file.jpg', $copyPath);

        $client->request(
            'POST',
            '/photo',
            array(
                'albumId' => static::$albumId,
                'date' => '12-01-2016',
            ),
            array(
                'photo' => new UploadedFile($copyPath, 'photo.jpg')
            ),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

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
        self::$photoId = $json[0]['photos'][0]['id'];

        // Get the photo back
        $client->request(
            'GET',
            '/photo/' . self::$photoId,
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('image/jpeg', $client->getResponse()->headers->get('Content-Type'));

        // Get thumbnail and resized versions
        $client->request(
            'GET',
            '/photo/' . self::$photoId . '/thumb',
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('image/jpeg', $client->getResponse()->headers->get('Content-Type'));

        $client->request(
            'GET',
            '/photo/' . self::$photoId . '/resized',
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('image/jpeg', $client->getResponse()->headers->get('Content-Type'));
    }
    
    /**
     * @depends testPostSinglePhoto
     */
    public function testAlbumArchive()
    {
    	$archiveName = static::$uploadDir . '/album-' . static::$albumId . '.zip';
    	$this->assertTrue(file_exists($archiveName), 'Archive file does not exist.');
    	
    	$zip = new \ZipArchive();
    	
    	if ($zip->open($archiveName) !== true) {
    		$this->fail('Cannot open archive');
    	}
    	
    	$content = $zip->getFromName(static::$photoId . '.jpeg');
    	if ($content === false) {
    		$this->fail('Archive does not contain photo file.');
    	}
    	
    	$zip->close();
    }

    /**
     * @depends testPostSinglePhoto
     */
    public function testPostMultiplePhotos()
    {
        $client = static::createClient();

        $copyPath1 = 'photo1.jpg';
        copy(__DIR__ . '/test_file.jpg', $copyPath1);
        $copyPath2 = 'photo2.jpg';
        copy(__DIR__ . '/test_file.jpg', $copyPath2);

        $client->request(
            'POST',
            '/photo',
            array(
                'albumId' => static::$albumId,
                'date' => '12-01-2016',
            ),
            array(
                'photo' => array(
                    new UploadedFile($copyPath1, 'photo.jpg'),
                    new UploadedFile($copyPath2, 'photo2.jpg'))
            ),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

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

        $this->assertEquals(3, count($json[0]['photos']));
    }

    /**
     * @depends testPostSinglePhoto
     */
    public function testCommentPhoto()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/photo/' . self::$photoId . '/comment',
            array(
                'text' => 'Hello there'
            ),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

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

        $this->assertEquals('Hello there', $json[0]['photos'][0]['comments'][0]['text']);
        self::$commentId = $json[0]['photos'][0]['comments'][0]['id'];
    }

    /**
     * @depends testCommentPhoto
     */
    public function testDeletePhotoComment()
    {
        $client = static::createClient();

        $client->request(
            'DELETE',
            '/photo/' . self::$photoId . '/comment/' . self::$commentId,
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

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

        $this->assertEquals(0, count($json[0]['photos'][0]['comments']));
    }

    /**
     * @depends testDeletePhotoComment
     */
    public function testDeletePhoto()
    {
        $client = static::createClient();

        // Regression testing: check that the photo can be deleted if it contains a comment
        $client->request(
            'POST',
            '/photo/' . self::$photoId . '/comment',
            array(
                'text' => 'Hello there'
            ),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request(
            'DELETE',
            '/photo/' . self::$photoId,
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

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

        $this->assertEquals(2, count($json[0]['photos']));
    }
    
    /**
     * @depends testDeletePhoto
     */
    public function testDeletePhotoFromArchive()
    {
    	$archiveName = static::$uploadDir . '/album-' . static::$albumId . '.zip';
    	
    	// Archive must exist as the album contains other photos than the one we deleted.
    	$this->assertTrue(file_exists($archiveName), 'Archive file does not exist.');
    	 
    	$zip = new \ZipArchive();
    	 
    	if ($zip->open($archiveName) !== true) {
    		$this->fail('Cannot open archive');
    	}
    	 
    	$content = $zip->getFromName(static::$photoId . '.jpeg');
    	if ($content !== false) {
    		$this->fail('Photo file not deleted from archive.');
    	}
    	 
    	$zip->close();
    }
}
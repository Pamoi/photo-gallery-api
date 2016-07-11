<?php

namespace Tests\AppBundle\Controller;

use Firebase\JWT\JWT;

class UserControllerTest extends CommandWebTestCase
{
    private static $token;

    public static function setUpBeforeClass()
    {
        self::freeApplication();
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

    public function testAuthenticate()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/authenticate',
            array(
                'username' => 'toto',
                'password' => 'pwd123'
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = (array) json_decode($client->getResponse()->getContent());
        $this->assertEquals(self::$token, $data['token']);
        $this->assertEquals('toto', $data['username']);
        $this->assertEquals(1, $data['id']);
    }

    public function testInvalidCredentials()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/authenticate',
            array(
                'username' => 'bob',
                'password' => 'iLoveAlice'
            )
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        $data = (array) json_decode($client->getResponse()->getContent());
        $this->assertEquals('Invalid username or password.', $data['message']);
    }

    public function testAuthenticateWithEmail()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/authenticate',
            array(
                'username' => 'toto@example.com',
                'password' => 'pwd123'
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = (array) json_decode($client->getResponse()->getContent());
        $this->assertEquals(self::$token, $data['token']);
        $this->assertEquals('toto', $data['username']);
        $this->assertEquals(1, $data['id']);
    }

    public function testAuthenticationToken()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/authenticate',
            array(
                'username' => 'toto',
                'password' => 'pwd123'
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = (array) json_decode($client->getResponse()->getContent());

        $token = $data['token'];

        $client->request(
            'GET',
            '/',
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => $token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testInvalidToken()
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/',
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => 'IamNotAToken'
            )
        );

        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $data = (array) json_decode($client->getResponse()->getContent());
        $this->assertEquals('Invalid token', $data['message']);
    }

    public function testNoTokenProvided()
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/'
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        $data = (array) json_decode($client->getResponse()->getContent());
        $this->assertEquals('No token provided', $data['message']);
    }

    public function testGetUserList()
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/user/list',
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(1, count($data));
        $this->assertEquals('toto', $data[0]['username']);
    }

    public function testSetPassword()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/password',
            array(
                'username' => 'toto',
                'oldPass' => 'pwd123',
                'newPass' => 'pwd456'
            ),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @depends testSetPassword
     */
    public function testCanAuthenticateWithNewPassword()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/authenticate',
            array(
                'username' => 'toto',
                'password' => 'pwd456'
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = (array) json_decode($client->getResponse()->getContent());
        $this->assertEquals(self::$token, $data['token']);
        $this->assertEquals('toto', $data['username']);
        $this->assertEquals(1, $data['id']);
    }

    /**
     * @depends testSetPassword
     */
    public function testCannotAuthenticateWithOldPassword()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/authenticate',
            array(
                'username' => 'toto',
                'password' => 'pwd123'
            )
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        $data = (array) json_decode($client->getResponse()->getContent());
        $this->assertEquals('Invalid username or password.', $data['message']);
    }

    public function testShortPassword()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/password',
            array(
                'username' => 'toto',
                'oldPass' => 'pwd123',
                'newPass' => 'pwd'
            ),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        $data = (array) json_decode($client->getResponse()->getContent());
        $this->assertEquals('The new password must be between 4 and 4096 characters long.', $data['message']);
    }

    public function testInvalidSetPassword()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/password',
            array(
                'username' => 'toto',
                'oldPass' => 'notThePassword',
                'newPass' => 'pwd456'
            ),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => self::$token
            )
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        $data = (array) json_decode($client->getResponse()->getContent());
        $this->assertEquals('Invalid username or password.', $data['message']);
    }
}
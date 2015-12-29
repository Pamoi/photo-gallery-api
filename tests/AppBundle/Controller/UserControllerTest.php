<?php

namespace Tests\AppBundle\Controller;

use Firebase\JWT\JWT;

class UserControllerTest extends CommandWebTestCase
{
    public static function setUpBeforeClass()
    {
        self::runCommand('doctrine:database:drop --force');
        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:update --force');
        self::runCommand('user:add toto toto@example.com pwd123');
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
        $token = (array) JWT::decode($data['token'], 'the_secret', array('HS256'));
        $this->assertEquals('toto', $token['username']);
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
        $this->assertEquals('Invalid username or password', $data['message']);
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
        $token = (array) JWT::decode($data['token'], 'the_secret', array('HS256'));
        $this->assertEquals('toto', $token['username']);
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
}
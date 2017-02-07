<?php

namespace Tests\AppBundle\Controller;

use Firebase\JWT\JWT;

class UserControllerTest extends CommandWebTestCase
{
    private static $secret;

    public static function setUpBeforeClass()
    {
        self::freeApplication();
        self::runCommand('doctrine:database:drop --force');
        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:update --force');
        self::runCommand('user:add toto toto@example.com pwd123 ROLE_ADMIN');

        static::$secret = self::getApplication()->getKernel()->getContainer()->getParameter('secret');
    }

    public static function getToken($time = 0)
    {
        // If unspecified, use standard expiration date
        if ($time == 0) {
            $time = time() + (60 * 60 * 24 * 7);
        }

        $payload = array(
            'username' => 'toto',
            'exp' => $time
        );

        return JWT::encode($payload, static::$secret);
    }

    /* Returns a lisit of tokens with three consecutive expiration times in
     * case the test request takes 1 or 2 seconds to execute.
     */
    public static function getTokens($time = 0)
    {
        if ($time == 0) {
            $time = time() + (60 * 60 * 24 * 7);
        }

        $tokens = array(
            static::getToken($time),
            static::getToken($time + 1),
            static::getToken($time + 2)
        );

        return $tokens;
    }

    public function testAuthenticate()
    {
        $client = static::createClient();
        $tokens = static::getTokens();

        $client->request(
            'POST',
            '/authenticate',
            array(
                'username' => 'toto',
                'password' => 'pwd123'
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertContains($data['token'], $tokens);
        $this->assertEquals('toto', $data['username']);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals(true, $data['admin']);
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
        $tokens = static::getTokens();

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
        $this->assertContains($data['token'], $tokens);
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

    public function testExpiredToken()
    {
        $client = static::createClient();
        $token = static::getToken(time() - 1000);

        $client->request(
            'GET',
            '/',
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => $token
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
        $token = static::getToken();

        $client->request(
            'GET',
            '/user/list',
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => $token
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(1, count($data));
        $this->assertEquals('toto', $data[0]['username']);
        $this->assertEquals(false, array_key_exists('admin', $data[0]));
    }

    public function testSetPassword()
    {
        $client = static::createClient();
        $token = static::getToken();

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
                'HTTP_X_AUTH_TOKEN' => $token
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
        $tokens = static::getTokens();

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
        $this->assertContains($data['token'], $tokens);
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
        $token = static::getToken();

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
                'HTTP_X_AUTH_TOKEN' => $token
            )
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        $data = (array) json_decode($client->getResponse()->getContent());
        $this->assertEquals('The new password must be between 4 and 4096 characters long.', $data['message']);
    }

    public function testInvalidSetPassword()
    {
        $client = static::createClient();
        $token = static::getToken();

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
                'HTTP_X_AUTH_TOKEN' => $token
            )
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        $data = (array) json_decode($client->getResponse()->getContent());
        $this->assertEquals('Invalid username or password.', $data['message']);
    }
}

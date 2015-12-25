<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $user = new User();
        $user2 = new User();

        $this->assertInternalType('string', $user->getSalt());
        $this->assertNotEmpty($user->getSalt());
        $this->assertFalse($user->getSalt() == $user2->getSalt());

        $this->assertTrue($user->isActive());

        $this->assertInternalType('array', $user->getRoles());
    }

    public function testBasicGettersSetters()
    {
        $user = new User();

        $user->setUsername('Toto');
        $user->setEmail('toto@tata.com');
        $user->setIsActive(false);
        $user->setPassword('1234');

        $this->assertEquals('Toto', $user->getUsername());
        $this->assertEquals('toto@tata.com', $user->getEmail());
        $this->assertEquals(false, $user->isActive());
        $this->assertEquals('1234', $user->getPassword());
    }

    public function testRoles()
    {
        $user = new User();

        $this->assertTrue(in_array(User::$ROLE_USER, $user->getRoles()));

        $user->addRole(User::$ROLE_ADMIN);

        $this->assertTrue(in_array(User::$ROLE_USER, $user->getRoles()));
        $this->assertTrue(in_array(User::$ROLE_ADMIN, $user->getRoles()));

        $user->removeRole(User::$ROLE_ADMIN);

        $this->assertFalse(in_array(User::$ROLE_ADMIN, $user->getRoles()));
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Unexpected user role: Toto
     */
    public function testRolesException()
    {
        $user = new User();

        $user->addRole('Toto');
    }
}

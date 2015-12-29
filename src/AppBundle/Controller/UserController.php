<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\ORM\EntityManager;
use Firebase\JWT\JWT;

/**
 * @Route(service="app.user_controller")
 */
class UserController
{
    private $em;
    private $encoder;
    private $key;

    public function __construct(EntityManager $entityManager, $passwordEncoder, $serverKey)
    {
        $this->em = $entityManager;
        $this->encoder = $passwordEncoder;
        $this->key = $serverKey;
    }

    /**
     * @Route("/authenticate", name="authentication")
     * @Method("POST")
     */
    public function authenticateAction(Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');

        try {
            $user = $this->em->getRepository('AppBundle:User')->loadUserByUsername($username);
            $valid = $this->encoder->isPasswordValid($user, $password);
        } catch (UsernameNotFoundException $e) {
            $valid = false;
        }

        if (!$valid) {
            $data = array(
                'message' => 'Invalid username or password'
            );

            return new JsonResponse($data, 401);
        }

        $token = array(
            'username' => $username
        );

        $jwt = JWT::encode($token, $this->key);

        $data = array(
            'token' => $jwt
        );

        return new JsonResponse($data);
    }
}
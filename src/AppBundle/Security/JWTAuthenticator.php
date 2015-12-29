<?php

namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityManager;
use Firebase\JWT\JWT;

class JWTAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $key;

    public function __construct(EntityManager $em, $serverKey)
    {
        $this->em = $em;
        $this->key = $serverKey;
    }

    public function getCredentials(Request $request)
    {
        if (!$token = $request->headers->get('X-AUTH-TOKEN')) {
            return null;
        }

        return array(
            'JWT' => $token,
        );
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $jwt = $credentials['JWT'];

        try {
            $decoded = (array)JWT::decode($jwt, $this->key, array('HS256'));
        } catch (\Exception $e) {
            throw new CustomUserMessageAuthenticationException('Invalid token');
        }

        $username = $decoded['username'];

        $repository = $this->em->getRepository('AppBundle:User');

        try {
            $user = $repository->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            throw new CustomUserMessageAuthenticationException('Invalid token');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        );

        return new JsonResponse($data, 403);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            'message' => 'No token provided',
        );

        return new JsonResponse($data, 401);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
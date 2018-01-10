<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class CorsResponseListener
{
    private $allowOrigin;

    public function __construct($allowOrigin)
    {
        $this->allowOrigin = $allowOrigin;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if ($this->allowOrigin) {
            $response = $event->getResponse();

            $response->headers->set('Access-Control-Allow-Origin', $this->allowOrigin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type,X-AUTH-TOKEN,x-auth-token');
        }
    }
}
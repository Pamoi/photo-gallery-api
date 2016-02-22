<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class CorsResponseListener
{
    // From http://www.hydrant.co.uk/tech/cors-pre-flight-requests-and-headers-symfony-httpkernel-component
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type,X-AUTH-TOKEN,x-auth-token');
    }
}
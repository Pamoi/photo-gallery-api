<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class CorsRequestListener
{
    // From http://www.hydrant.co.uk/tech/cors-pre-flight-requests-and-headers-symfony-httpkernel-component
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $method  = $request->getRealMethod();

        if ('OPTIONS' == $method) {
            $response = new Response();
            $event->setResponse($response);
        }
    }
}

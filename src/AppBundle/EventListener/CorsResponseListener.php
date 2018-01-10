<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class CorsResponseListener
{
    private $allowOrigin;
    private $allowedOrigins;

    public function __construct($allowOrigin)
    {
        $this->allowOrigin = $allowOrigin;
        $this->allowedOrigins = array_map(function ($o) { return trim($o); }, explode(",", $this->allowOrigin));
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();

        if ($this->allowOrigin === "*") {
            $response->headers->set('Access-Control-Allow-Origin', $this->allowOrigin);
        } else {
            $origin = $event->getRequest()->headers->get("Origin");

            if (in_array($origin, $this->allowedOrigins)) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
            }
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type,X-AUTH-TOKEN,x-auth-token');
    }
}
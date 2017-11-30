<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class JsonBodyRequestListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->getContentType() === "json") {
            $bodyData = json_decode($request->getContent(), true);

            foreach ($bodyData as $key => $value) {
                $request->request->set($key, $value);
            }
        }
    }
}
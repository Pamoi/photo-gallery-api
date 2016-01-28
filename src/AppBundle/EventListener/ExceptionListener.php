<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $response = new JsonResponse();

        if ($exception instanceof NotFoundHttpException) {
            $response->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
            $response->setData(array(
                'message' => 'Resource not found.'
            ));
        } else if ($exception instanceof AccessDeniedHttpException) {
            $response->setStatusCode(JsonResponse::HTTP_FORBIDDEN);
            $response->setData(array(
                'message' => $exception->getMessage()
            ));
        } else {
            $response->setData(array(
                'message' => 'An internal server error occurred. Sorry for the inconvenience.'
            ));
            $response->setStatusCode(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }
}
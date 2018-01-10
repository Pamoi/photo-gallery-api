<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Psr\Log\LoggerInterface;

class ExceptionListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $response = new JsonResponse();

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
            $response->setData(array(
                'message' => $exception->getMessage()
            ));
        } else {
            $this->logger->error($exception->getTraceAsString());

            $response->setData(array(
                'message' => 'An internal server error occurred. Sorry for the inconvenience.'
            ));
            $response->setStatusCode(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }
}
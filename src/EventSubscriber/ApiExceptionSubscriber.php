<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class ApiExceptionSubscriber
 * @package App\EventSubscriber
 */
class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if (!$exception instanceof HttpException && !$exception instanceof InvalidArgumentException) {
            return;
        }

        $response = new JsonResponse(
            [
                'title' => 'Server error',
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace()
            ],
            $exception instanceof HttpException ? $exception->getStatusCode() : 500
        );
        $response->headers->set('Content-Type', 'application/problem+json');
        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }
}
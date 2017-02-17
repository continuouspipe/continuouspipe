<?php

namespace AppBundle\EventListener;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

/**
 * Catch unhandled exceptions and log them
 */
class ExceptionListener implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $controller;

    public function __construct(string $controller, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->controller = $controller;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', -64],
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof NotFoundHttpException) {
            $event->stopPropagation();
            $this->logException($exception, LogLevel::DEBUG);
            $this->createResponseFromExceptionAndEvent($exception, $event);
        } elseif ($exception instanceof AccessDeniedHttpException) {
            $event->stopPropagation();
            $this->logException($exception, LogLevel::INFO);
            $this->createResponseFromExceptionAndEvent($exception, $event);
        }
    }

    private function logException(\Exception $exception, $level)
    {
        $message = sprintf(
            'Uncaught PHP Exception %s: "%s" at %s line %s',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        $this->logger->log($level, $message, ['exception' => $exception]);
    }

    private function createResponseFromExceptionAndEvent(\Exception $exception, GetResponseForExceptionEvent $event)
    {
        $request = $this->duplicateRequest($exception, $event->getRequest());
        $response = $event->getKernel()->handle($request, HttpKernelInterface::SUB_REQUEST, false);
        $event->setResponse($response);
    }

    protected function duplicateRequest(\Exception $exception, Request $request)
    {
        $attributes = array(
            '_controller' => $this->controller,
            'exception' => FlattenException::create($exception),
            'logger' => $this->logger instanceof DebugLoggerInterface ? $this->logger : null,
            'format' => $request->getRequestFormat(),
        );
        $request = $request->duplicate(null, null, $attributes);
        $request->setMethod('GET');

        return $request;
    }
}

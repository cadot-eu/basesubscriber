<?php

namespace App\EventSubscriber\base;

use App\Service\base\FileHelper;
use App\Service\base\FileUploader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileExceptionSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private Environment $environment
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    //renvoie une image du logo si l'image demandée n'existe pas
    public function onKernelException(ExceptionEvent $event): void
    {
        // if ($event->getThrowable() instanceof NotFoundHttpException) {
        //     if (($ext = FileUploader::fileExtension($event->getRequest()->getRequestUri())) != null) {
        //         $event->allowCustomResponseCode();
        //         $response = new BinaryFileResponse('/app/public/build/404.' . $ext, 200, [], true, 'inline');
        //         $event->setResponse($response);
        //     }
        // }
    }
}

<?php

namespace App\EventSubscriber\base;

use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class FailedMessageSubscriber implements EventSubscriberInterface
{
    private $logger, $mailer;

    public function __construct(LoggerInterface $logger, MailerInterface $mailer)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            WorkerMessageFailedEvent::class => 'onMessageFailed',
        ];
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event)
    {
        $failedMessage = $event->getEnvelope()->getMessage();

        // Log the failed message
        $this->logger->error('Message failed: ' . get_class($failedMessage));
        $email = (new Email())
            ->from('siteimmo@cadot.eu')
            ->to('michael@cadot.eu')
            ->subject('Erreur lors du traitement')
            ->html('Message failed: ' . get_class($failedMessage) . '<br>' . $event->getThrowable()->getTraceAsString());
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new \Exception($e);
        }
    }
}

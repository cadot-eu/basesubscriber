<?php

namespace App\EventSubscriber\base;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class KnpSubscriber implements EventSubscriberInterface
{
    private $knparray = ['sort', 'direction', 'page', 'filterValue', 'filterField'];

    public function __construct()
    {
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }
        if ($request->isMethod('GET')) {
            foreach ($this->knparray as $knp) {
                $field = 'knp_' . $request->attributes->get('_route') . '_' . $knp;
                if ($val = $request->query->get($knp)) {
                    $request->getSession()->set($field, $val);
                } else {
                    if ($exval = $request->getSession()->get($field, false)) {
                        $request->query->add([$knp => $exval]);
                    }
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}

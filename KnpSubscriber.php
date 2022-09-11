<?php

namespace App\EventSubscriber\base;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class KnpSubscriber implements EventSubscriberInterface
{
    private $knparray = ['sort', 'direction', 'page', 'filterValue', 'filterField'];
    private $request;

    public function onKernelRequest(RequestEvent $event)
    {
        //récupération des request
        $this->request = $event->getRequest();
        //$request->getSession()->clear();
        if (!$this->request->hasPreviousSession()) {
            return;
        }

        if ($this->request->isMethod('GET')) {
            //reset des pages si on a une nouvelle filterValue
            if ($this->request->getSession()->get($this->GetName('filterValue'), false) != $this->request->query->get('filterValue', false) && $this->request->query->get('filterValue', false) != false) {
                $this->request->getSession()->set($this->GetName('page'), 1);
                $this->request->query->add([$this->GetName('_page') => 1]);
            }
            //on liste les champs
            foreach ($this->knparray as $knp) {
                $field = $this->GetName($knp);
                //si le get n'est pas false, on enregistre dans la session
                if ($val = $this->request->query->get($knp, false)) {
                    $this->request->getSession()->set($field, $val);
                } else {
                    //si le get n'est pas false
                    //suppression des champs
                    //si le get est "vide" on supprime le champ de la session
                    if ($this->request->query->get($knp, 'vide') == '') {
                        $this->request->getSession()->remove($field);
                    }
                    //si le get n'est ni "vide" ni false on ajoute au request
                    if ($exval = $this->request->getSession()->get($field, false)) {
                        $this->request->query->add([$knp => $exval]);
                    }
                }
            }
        }
    }
    private function GetName($field)
    {
        return 'knp_' . $this->request->attributes->get('_route') . '_' . $field;
    }
    public static function getSubscribedEvents()
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}

<?php

namespace App\EventSubscriber\base;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use App\Service\base\ToolsHelper;

class TwigGlobalSubscriber implements EventSubscriberInterface
{
    private $twig;

    private $em;

    public function __construct(Environment $twig, EntityManagerInterface $em)
    {
        $this->twig = $twig;
        $this->em = $em;
    }

    public function injectGlobalVariables()
    {
        $this->twig->addGlobal('TBparametres', ToolsHelper::params($this->em));
        $this->twig->addGlobal('categories', $this->em->getRepository('App\Entity\Categorie')->findBy(array('deletedAt' => null), array('nom' => 'ASC')));
    }

    static public function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'injectGlobalVariables',
        ];
    }
}

<?php

namespace App\EventSubscriber\base;

use App\Service\base\ArticleHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use App\Service\base\ToolsHelper;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use App\Entity\Parametres;
use Psr\Log\LoggerInterface;

class TwigGlobalSubscriber implements EventSubscriberInterface
{
    private $twig;

    private $em;

    protected $cachemanager;

    protected $filtermanager;

    protected $logger;

    public function __construct(Environment $twig, EntityManagerInterface $em, CacheManager $cachemanager, FilterManager $filtermanager, LoggerInterface $logger)
    {
        $this->twig = $twig;
        $this->em = $em;
        $this->cachemanager = $cachemanager;
        $this->filtermanager = $filtermanager;
        $this->logger = $logger;
    }

    public function injectGlobalVariables()
    {
        $this->twig->addGlobal('TBparametres', $this->params($this->em));
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'injectGlobalVariables',
        ];
    }
    /**
     * It returns an array of all the parameters in the database
     *
     * @param EntityManagerInterface em The entity manager
     *
     * @return An array of all the parameters in the database.
     */
    public function params(EntityManagerInterface $em): array
    {
        $tab = [];
        foreach ($this->em->getRepository(Parametres::class)->findAll() as $parametre) {
            $tab[$parametre->getSlug()] = $parametre->getValeur();
        }

        return $tab;
    }
}

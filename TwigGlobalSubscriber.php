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

class TwigGlobalSubscriber implements EventSubscriberInterface
{
    private $twig;

    private $em;

    protected $cachemanager;

    protected $filtermanager;

    public function __construct(Environment $twig, EntityManagerInterface $em, CacheManager $cachemanager, FilterManager $filtermanager)
    {
        $this->twig = $twig;
        $this->em = $em;
        $this->cachemanager = $cachemanager;
        $this->filtermanager = $filtermanager;
    }

    public function injectGlobalVariables()
    {
        $this->twig->addGlobal('TBparametres', $this->params($this->em));
        $this->twig->addGlobal('categories', $this->em->getRepository('App\Entity\Categorie')->findBy(array('deletedAt' => null), array('nom' => 'ASC')));
    }

    static public function getSubscribedEvents()
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
            $tab[$parametre->getSlug()] =  //on ajoute les srcset aux images
                ArticleHelper::imgToSrcset($parametre->getValeur(), $this->cachemanager, $this->filtermanager);
        }

        return $tab;
    }
}

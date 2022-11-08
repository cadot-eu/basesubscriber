<?php

namespace App\EventSubscriber\base;

use App\Entity\Chatmessage;
use App\Repository\ChatmessageRepository;
use App\Service\base\IpHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use App\Service\base\ToolsHelper;
use Symfony\Component\Security\Core\Security;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use App\Repository\ChatRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ChatSubscriber implements EventSubscriberInterface
{
    private $twig;

    private $messages, $token;

    public function __construct(EntityManagerInterface $em, Security $security, ChatRepository $chatRepository, Environment $twig, RequestStack $requestStack)

    {
        $this->twig = $twig;
        //creation d'un token unique pour l'utilisateur
        //$session = new Session(new NativeSessionStorage(), new AttributeBag());
        $session = $requestStack->getSession();
        $this->token = hash('ripemd160', IpHelper::getUserIP());
        $session->set('chattoken', $this->token);
        //on récupère les anciens messages de cet utilisateurs
        $this->messages = $chatRepository->findBy(['user' => $this->token], ['id' => 'DESC']);
    }

    public function injectGlobalVariables()
    {
        $this->twig->addGlobal('ChatMessages', $this->messages);
        $this->twig->addGlobal('ChatToken', $this->token);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'injectGlobalVariables',
        ];
    }
}

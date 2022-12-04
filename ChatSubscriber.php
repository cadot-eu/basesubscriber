<?php

namespace App\EventSubscriber\base;

use App\Repository\ChatRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class ChatSubscriber implements EventSubscriberInterface
{
	private $twig;

	private $messages;
	private $token;

	public function __construct(
		ChatRepository $chatRepository,
		Environment $twig,
		RequestStack $requestStack
	) {
		$this->twig = $twig;
		//creation d'un token unique pour l'utilisateur
		$session = $requestStack->getSession();
		if (!$session->get('chattoken')) {
			$this->token = hash('sha256', uniqid());
			//si on a pas de token dans la session
			$session->set('chattoken', $this->token);
		} else {
			$this->token = $session->get('chattoken');
		}
		//on récupère les anciens messages de cet utilisateurs
		$this->messages = $chatRepository->findBy(
			['user' => $this->token],
			['id' => 'DESC']
		);
	}

	public function injectGlobalVariables()
	{
		$this->twig->addGlobal('ChatMessages', $this->messages);
		$this->twig->addGlobal('ChatToken', $this->token);
		$this->twig->addGlobal(
			'template_box_reponse',
			file_get_contents('/app/templates/chat_template_boxreponse.html.twig')
		);
		$this->twig->addGlobal(
			'template_box_question',
			file_get_contents('/app/templates/chat_template_boxquestion.html.twig')
		);
	}

	public static function getSubscribedEvents()
	{
		return [
			KernelEvents::CONTROLLER => 'injectGlobalVariables',
		];
	}
}

<?php

namespace App\EventSubscriber\base;

use Flasher\Symfony\Template\TwigTemplateEngine;
use phpDocumentor\Reflection\PseudoTypes\True_;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MaintenanceSubscriber implements EventSubscriberInterface
{
    protected $params;

    protected $twig, $token;
    protected $urls = ['admin', 'connexion','login'];

    public function __construct(Environment $twig, TokenStorageInterface $tokenStorageInterface)
    {
        $this->twig = $twig;
        $this->token = $tokenStorageInterface;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        //on autorise les accès à certaines urls et si un user est connecté
        if (!$this->testUser()) {
            if (!$this->testUrls($event->getRequest()->getRequestUri())) {
                if (isset($_ENV['maintenance']) && $_ENV['maintenance'] == 'true' || isset($_ENV['MAINTENANCE']) && $_ENV['MAINTENANCE'] == '1') {
                    if (file_exists('/app/templates/maintenance.html.twig')) {
                        $response = $this->twig->render('maintenance.html.twig');
                    } else {
                        $response = $this->getpage();
                    }
                    $event->setResponse(new Response($response, 503));
                }
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }

    private function testUser()
    {
        if (!$this->token->getToken()) {
            return false;
        }
        return (strpos(implode(',', $this->token->getToken()->getUser()->getRoles()), 'ADMIN') !== false);
    }
    private function testUrls($uri)
    {

        foreach ($this->urls as $url) {
            if (substr($uri, 0, strlen("/$url")) == "/$url") {
                return true;
            }
        }
        return false;
    }



    private function getpage()
    {
        return <<<'EOT'
        <!doctype html>
<html>
  <head>
    <title>Site Maintenance</title>
    <meta charset="utf-8"/>
    <meta name="robots" content="noindex"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
      body { text-align: center; padding: 20px; font: 20px Helvetica, sans-serif; color: #333; }
      
      @media (min-width: 768px){
        body{ padding-top: 150px; }
      }
      h1 { font-size: 50px; }
      h1>span { font-size: 20px;  }
      article { display: block; text-align: left; max-width: 80%; margin: 0 auto; }
      a { color: #dc8100; text-decoration: none; }
      a:hover { color: #333; text-decoration: none; }
    </style>
  </head>
  <body>
    <article>
        <h1>We&rsquo;ll be back soon! <span><i>Nous revenons bientôt!</i></span></h1>
        <div>
            <p>Sorry for the inconvenience but we&rsquo;re performing some maintenance at the moment. We&rsquo;ll be back online shortly!</p>
            <i>Désolé pour cet inconvénient mais nous sommes en maintenance sur ce site. Nous faisons tout pour qu'il soit en ligne rapidement.</i>
            <p style="text-align:right;">&mdash; The Team </p>
            <p style="text-align:right;">&mdash; <i>L'équipe</i></p>
        </div>
    </article>
  </body>
</html>
EOT;
    }
}

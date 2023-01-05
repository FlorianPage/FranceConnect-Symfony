<?php

namespace KleeGroup\FranceConnectBundle\Controller;

use KleeGroup\FranceConnectBundle\Manager\ContextServiceInterface;
use KleeGroup\FranceConnectBundle\Manager\Exception\SecurityException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FranceConnectController
 *
 * @package KleeGroup\FranceConnectBundle\Controller
 * @Route("/france-connect")
 */
class FranceConnectController extends AbstractController
{
    /** @var LoggerInterface */
    private $logger;

    /** @var ContextServiceInterface */
    private $contextService;

    public function __construct(LoggerInterface $logger, ContextServiceInterface $contextService)
    {
        $this->logger = $logger;
        $this->contextService = $contextService;
    }
    
    /**
     * @Route("/login_fc", methods="GET")
     * @return RedirectResponse
     */
    public function loginAction( )
    {
        $this->logger->debug('Generating a URL to get the authorization code.');
        $url = $this->contextService->generateAuthorizationURL();
        
        return $this->redirect($url);
    }
    
    /**
     * @Route("/callback", methods="GET")
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function checkAction(Request $request)
    {
        $this->logger->debug('Callback intercept.');
        $getParams = $request->query->all();
        // * On catch les securityException ici pour déconnecter la session FC en cas d'exception de ce type
        try {
            $this->contextService->getUserInfo($getParams);
        } catch (SecurityException $e) {
            $url = $this->contextService->generateLogoutURL($e->getCode());
        
            return $this->redirect($url);
        }

        switch ($this->getParameter('france_connect.result_type')) {
            case 'route' :
                $redirection = $this->redirectToRoute($this->getParameter('france_connect.result_value'));
                break;
            default :
                $redirection = $this->redirect($this->getParameter('france_connect.result_value'));
                break;
        }

        return $redirection;
    }
    
    /**
     * @Route("/logout_fc/{codeErreur}")
     * @return RedirectResponse
     */
    public function logoutAction(?int $codeErreur = null)
    {
        $this->logger->debug('Get Logout URL.');
        $url = $this->contextService->generateLogoutURL($codeErreur);
        
        return $this->redirect($url);
    }
    
    
}

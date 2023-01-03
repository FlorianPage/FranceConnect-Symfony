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
        try {
            $this->contextService->getUserInfo($getParams);
        } catch (SecurityException $e) {
            $this->logger->error('Exception = ' . $e);
            $url = $this->contextService->generateLogoutURL(3);
        
            return $this->redirect($url);
            // $this->logoutAction(3);
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
        $this->logger->debug('CODE ERREUR = ' . $codeErreur);
        $url = $this->contextService->generateLogoutURL($codeErreur);
        $this->logger->debug('URL = ' . $url);
        
        return $this->redirect($url);
    }
    
    
}

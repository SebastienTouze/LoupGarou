<?php

/*
 * Ce fichier Overwrite le controler de FOSUserBundle
 * Il permet de préparer un formulaire supplémentaire pour l'enregistrement avec le formulaire de login
 * 
 * Créé par Sébastien Touzé le 24 août 2012
 */

namespace LG\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

use FOS\UserBundle\Controller\SecurityController as BaseSecurityController;

class SecurityController extends BaseSecurityController
{
    public function loginAction(Request $request)
    {
    
    //*******          Gestion du login          *******
//        $request = $this->container->get('request');
        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $session = $request->getSession();
        /* @var $session \Symfony\Component\HttpFoundation\Session */

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        if ($error) {
            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            $error = $error->getMessage();
        }
        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);

        $csrfToken = $this->container->has('form.csrf_provider')
            ? $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate')
            : null;

            return $this->container->get('templating')->renderResponse('FOSUserBundle:Security:login.html.'.$this->container->getParameter('fos_user.template.engine'), array(
            'last_username' => $lastUsername,
            'error'         => $error,
            'csrf_token' => $csrfToken,
/*            'form' => $form->createView(),
            'theme' => $this->container->getParameter('fos_user.template.theme'),*/
        ));
    }

    public function checkAction()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    public function logoutAction()
    {
    //TODO activer le logout
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }
}

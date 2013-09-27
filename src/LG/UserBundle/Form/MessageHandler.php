<?php

/*
 * src/LG/UserBundle/Form/MessageHandler.php
 *
 * Handler pour l'enregistrement des messages utilisateurs
 * 
 * Créé par Sébastien Touzé le 18 août 2012
 */

namespace LG\UserBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

use LG\UserBundle\Entity\Message;

class MessageHandler
{
    protected $form;
    protected $request;
    protected $em;

    public function __construct(Form $form, Request $request, EntityManager $em, $user)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->em      = $em;
        $this->user = $user;
    }

    public function process()
    {
        if( $this->request->getMethod() == 'POST' )
        {
            $this->form->bindRequest($this->request);

            if( $this->form->isValid() )
            {
                $this->onSuccess($this->form->getData());

                return true;
            }
        }

        return false;
    }

    public function onSuccess(Message $message)
    {
        $message->setDate(new \DateTime());
        $message->setEmetteur($this->user);
        
        $this->em->persist($message);
        $this->em->flush();
    }
}

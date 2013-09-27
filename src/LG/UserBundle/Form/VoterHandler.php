<?php

/*
 * src/LG/UserBundle/Form/VoterVillageoisHandler.php
 *
 * Handler pour voter contre une autre personne (vote des villageois)
 * 
 * Créé par Sébastien Touzé le 3 août 2012
 */

namespace LG\UserBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

use LG\UserBundle\Entity\User;
use LG\UserBundle\Entity\Vote;

class VoterHandler
{
    protected $form;
    protected $request;
    protected $em;

    public function __construct(Form $form, Request $request, EntityManager $em, $userVotant)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->em      = $em;
        $this->userVotant = $userVotant;
        $parametres = $em->getRepository('LGUserBundle:Parametres')->find(1);
        $this->jourCourant = $parametres->getTemps();
    }

    public function process()
    {
        if( $this->request->getMethod() == 'POST' )
        {
            $arrayFormulaire = $this->request->get('form');
            
            if(!isset($arrayFormulaire['type']))
                {return false;}
            
            switch ($arrayFormulaire['type']) 
            {
                case 1: //Villageois
                {
                    $userVoteContre = $this->em->getRepository('LGUserBundle:User')->find($arrayFormulaire['votePour']);
                    
                    $aDejaVote = $this->em->getRepository('LGUserBundle:Vote')->findOneBy(array('votant' => $this->userVotant->getId(), 'type' => $arrayFormulaire['type'], 'jour' => $this->jourCourant));
                    
                    if($aDejaVote)
                    {
                        $vote = $aDejaVote;
                    }
                    else
                    {
                        $vote = new Vote; 
                        $vote->setVotant($this->userVotant);
                        $vote->setType($arrayFormulaire['type']);
                        $vote->setJour($this->jourCourant);
                    }
                        
                    $vote->setDateVote(new \Datetime());
                    $vote->setVotePour($userVoteContre);
                    $this->em->persist($vote);
                    $this->em->flush();
                        
                    break;
                }
                
                case 2: //Loups garous
                {
                    $userVotePour = $this->em->getRepository('LGUserBundle:User')->find($arrayFormulaire['votePour']);
                    
                    $aDejaVote = $this->em->getRepository('LGUserBundle:Vote')->findOneBy(array('votant' => $this->userVotant->getId(), 'type' => $arrayFormulaire['type'], 'jour' => $this->jourCourant));
                    
                    if($aDejaVote)
                    {
                        $vote = $aDejaVote;
                    }
                    else
                    {
                        $vote = new Vote; 
                        $vote->setVotant($this->userVotant);
                        $vote->setType($arrayFormulaire['type']);
                        $vote->setJour($this->jourCourant);
                    }
                    
                    $vote->setDateVote(new \Datetime());
                    $vote->setVotePour($userVotePour);
                    
                    $this->em->persist($vote);
                    $this->em->flush();
                    
                    break;
                }
                
                case 3: //Maire
                {
                    $userVotePour = $this->em->getRepository('LGUserBundle:User')->find($arrayFormulaire['votePour']);
                    $aDejaVote = $this->em->getRepository('LGUserBundle:Vote')->findOneBy(array('votant' => $this->userVotant->getId(), 'type' => $arrayFormulaire['type'], 'jour' => $this->jourCourant));
                    
                    if($aDejaVote)
                    {
                        $vote = $aDejaVote;
                    }
                    else
                    {
                        $vote = new Vote; 
                        $vote->setVotant($this->userVotant);
                        $vote->setType($arrayFormulaire['type']);
                        $vote->setJour($this->jourCourant);
                    }

                    $vote->setDateVote(new \Datetime());
                    $vote->setVotePour($userVotePour);
                    
                    $this->em->persist($vote);
                    $this->em->flush();
                    
                    break;
                }
                default: 
                {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    public function onSuccess()
    {}
}

<?php

namespace LG\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use LG\UserBundle\Form\VoterHandler;
use LG\UserBundle\Form\MessageHandler;
use LG\UserBundle\Entity\Vote;
use LG\UserBundle\Entity\Message;
use LG\UserBundle\Entity\User;

class DefaultController extends Controller
{
    
    public function indexAction()
    {
        //Récupération de l'utilisateur en cours, des repositories et initialisation des valeurs
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        $dbParametres = $em->getRepository('LGUserBundle:Parametres')->find(1);
        $users = $em->getRepository('LGUserBundle:User')->findAll();
        $votes = $em->getRepository('LGUserBundle:Vote')->findBy(array(), array('dateVote' => 'DESC'));
        $votesUserAujourdhui = $em->getRepository('LGUserBundle:Vote')->findBy(array('votant' => $user->getId(), 'jour' => $dbParametres->getJour() ));

        $votesEffectueesAujourdhui = array(1 => false, 2 => false, 3 => false);
        
        $joueursVivants = array();
        $joueursMorts = array();
        $listJoueursVote = array();

        //Changement de jour/nuit, et autres actions de fin de vote
        $dbParametres = $this->changerJourNuit($dbParametres, $em);
        
        $em->flush();

        foreach($votesUserAujourdhui as $vote)
            {$votesEffectueesAujourdhui[$vote->getType()] = $vote->getVotePour();}
        
        
        // Récupération des joueurs et génération des listes
        foreach( $users as $joueur )
        {
            if($joueur->getVivant() == true)
            {
                $joueursVivants[$joueur->getId()] = $joueur; 
                if ($joueur != $user)
                {
                    $listJoueursVote[$joueur->getId()] = $joueur->getUsername();
                    if($joueur->getRole()->getId() != 2)
                        {$listJoueursVoteLG[$joueur->getId()]= $joueur->getUsername();}
                }
            }
            else
                {$joueursMorts[$joueur->getDateMort()->format('d/m/Y').'-'.$joueur->getTypeMort()] = $joueur;}
        }
        
        
        
        
        //Déterminer si l'on est le jour ou la nuit et gestion des paramètres
        $jour = $dbParametres->getJour();
        
        $parametre['jour'] = $jour == 1 ? "jour" : "nuit";
        $parametre['heureNuit'] = $dbParametres->getheureNuit();
        $parametre['heureJour'] = $dbParametres->getheureJour();
        $maire = 'aucun';
        if($testMaire = $em->getRepository('LGUserBundle:User')->findOneBy(array('maire' => true)) )
            $maire = $testMaire;
        
        
        
        
        //Formulaire pour le vote des vilageois
        $voteContre = new Vote;
        $formVoteVillage = $this->createFormBuilder($voteContre)
                        ->add('votePour', 'choice', array('choices' => $listJoueursVote,
            'multiple' => false, ))
                        ->add('type', 'hidden', array('data' => 1, ))
                        ->getForm();
        
        
        //Formulaire pour le vote des loups
        $voteLG = new Vote;
        $formVoteLoups = $this->createFormBuilder($voteLG)
                        ->add('votePour', 'choice', array('choices' => $listJoueursVoteLG,
            'multiple' => false, ))
                        ->add('type', 'hidden', array('data' => '2'))
                        ->getForm();
        
        //Formulaire pour le vote d'élection du maire
        $voteM = new Vote;
        $formVoteMaire = $this->createFormBuilder($voteM)
                        ->add('votePour', 'choice', array('choices' => $joueursVivants,
            'multiple' => false, ))
                        ->add('type', 'hidden', array('data' => '3'))
                        ->getForm();
        
        //Traitement des formulaires s'il l'un d'entre eux a été remplis
        $formInfoHandler = new VoterHandler($formVoteVillage, $this->get('request'), $em, $user);
        if($formInfoHandler->process())
        {
            return $this->render('LGUserBundle:Default:confirmation.html.twig', array('parametres' => $parametre));
        }
        else 
        {
            $formInfoHandler = new VoterHandler($formVoteLoups, $this->get('request'), $em, $user);
            if($formInfoHandler->process())
            {
                return $this->render('LGUserBundle:Default:confirmation.html.twig', array('parametres' => $parametre));
            }
            else
            {
                $formInfoHandler = new VoterHandler($formVoteMaire, $this->get('request'), $em, $user);
                if($formInfoHandler->process())
                {
                    return $this->render('LGUserBundle:Default:confirmation.html.twig', array('parametres' => $parametre));
                }
            }
        }
        
        
        
        
        //Envoyer un message : formulaire et gestion du formulaire
        $message = new Message;
        $formMessage = $this->createFormBuilder($message)
                        ->add('message', 'textarea')
                        ->getForm();
        $formMessageHandler = new MessageHandler($formMessage, $this->get('request'), $em, $user);
        $formMessageHandler->process();
        //Récupération des messages : 
        $messages = $em->getRepository('LGUserBundle:Message')->findBy(array(), array('date' => 'DESC'));
        

        
        //Récupération des votes par ordre anté-chronologique
        $votesVillageois = array();
        $votesMaire = array();
        $votesLoups = array();
        
        $jour = '';
        
        foreach($votes as $vote)
        {
            if($jour != $vote->getDateVote()->format('d/m/Y'))
            {
                $jour = $vote->getDateVote()->format('d/m/Y');
                $votesVillageois[] = array('votant' => '*', 'pour' => $jour, );
                if (array_key_exists($jour.'-2', $joueursMorts)) {$votesVillageois[] = array('votant' => 'x', 'pour' => $joueursMorts[$jour.'-2']);}
                if (array_key_exists($jour.'-1', $joueursMorts)) {$votesVillageois[] = array('votant' => '+', 'pour' => $joueursMorts[$jour.'-1']);}
                $votesLoups[] = array('votant' => '*','pour' => $jour, );
                $votesMaire[] = array('votant' => '*','pour' => $jour, );
            }
            switch ($vote->getType())
            {
            case 1:
            {
                $votesVillageois[] = array('votant' => $vote->getVotant(),
                                            'pour' => $vote->getVotePour(), );
                break;
            }
            case 2:
            {
                $votesLoups[] = array('votant' => $vote->getVotant(),
                                        'pour' => $vote->getVotePour(), );
                break;
            }
            case 3:
            {
                $votesMaire[] = array('votant' => $vote->getVotant(),
                                        'pour' => $vote->getVotePour(), );
                break;
            }
            }
        }
        
        //Synthèse des votes en cours dans le village
        $arraySyntheseVotesVillage = $em->getRepository('LGUserBundle:Vote')->findPersoneElue(1, $dbParametres->getJour());
        $arraySyntheseVotesMaire = $em->getRepository('LGUserBundle:Vote')->findPersoneElue(3, $dbParametres->getJour());
        
        $userRepository = $em->getRepository('LGUserBundle:User');
        foreach($arraySyntheseVotesMaire as $vote) 
        {
            $vote = $userRepository->find($vote['id']);
        }
        foreach($arraySyntheseVotesVillage as $vote) 
        {
            $vote = $userRepository->find($vote['id']);
        }
        
        return $this->render('LGUserBundle:Default:index.html.twig', 
                            array('user' => $user, 'joueursMorts' => $joueursMorts, 'joueursVivants' => $joueursVivants,
                             'parametres' => $parametre, 'formJour' => $formVoteVillage->createView(),
                             'formNuit' => $formVoteLoups->createView(), 'formMaire' => $formVoteMaire->createView(), 
                             'votesVillageois' => $votesVillageois, 'votesMaire' => $votesMaire, 'votesLoups' => $votesLoups, 
                             'votesEffectueesAujourdhui' => $votesEffectueesAujourdhui, 
                             'formMessage' => $formMessage->createView(), 'messages' => $messages, 'maire' => $maire, 
                             'votesParPersonneVillage' => $arraySyntheseVotesVillage, 
                             'votesParPersonneMaire' => $arraySyntheseVotesMaire, ));
    }
    
    
    private function changerJourNuit($dbParametres, $em) {
        //Alternance Jour/Nuit
        $heureCourante = intval(date("H"));
        if( $dbParametres->getJour() == 0 && ($heureCourante > $dbParametres->getHeureJour() && $heureCourante < $dbParametres->getHeureNuit() ))
        {
            
            $arrayDevore = $em->getRepository('LGUserBundle:Vote')->findPersoneElue(3, $dbParametres->getJour());
            
            $idDevore = $this->validerVote($arrayDevore);
            
            if($idDevore)
            {
                $mort = $em->getRepository('LGUserBundle:User')->find($idDevore);
                $mort->setVivant(false);
                $mort->setMaire(false);
            
                $em->persist($mort);
                $em->flush();
            }
            else
                {$dbParametres->setErreur(true);}
            
            
            //Passage Nuit->Jour
            $dbParametres->setJour(1);
            
        }
        elseif($dbParametres->getJour() == 1 && ($heureCourante >= $dbParametres->getHeureNuit() || $heureCourante <= $dbParametres->getHeureJour() ))
        {
            //Élection du maire et condamnation
            $arrayCondamne = $em->getRepository('LGUserBundle:Vote')->findPersoneElue(1, $dbParametres->getJour());
            $arrayMaire = $em->getRepository('LGUserBundle:Vote')->findPersoneElue(2, $dbParametres->getJour());
            
            $idCondamne = $this->validerVote($arrayCondamne);
            $idMaire = $this->validerVote($arrayMaire, true);
            
            if($idMaire)
            {
                $maire = $em->getRepository('LGUserBundle:User')->find($maire);
                $maire->setMaire(true);
                $em->persist($maire);
            }
            else
                {$dbParametres->setErreur(true);}
            
            if($idCondamne)
            {
                $mort = $em->getRepository('LGUserBundle:User')->find($idCondamne);
                $mort->setVivant(false);
                $mort->setMaire(false);
                $em->persist($mort);
            }
            else
                {$dbParametres->setErreur(true);}
            
            $em->flush();
            
            //Passage Nuit->Jour
            $dbParametres->setJour(0);
            $dbParametres->jourSuivant();
        }
        
        return $dbParametres;
    }
    
    

    private function validerVote($arrayPersonnes, $voteFacultatif = false) {
    
        $oldNbVotes =  0;
        $nbEgaux = 0;
        
        foreach($arrayPersonnes as $villageois)
        {
            if($villageois['nbVotes'] < $oldNbVotes)
                break;
            elseif($villageois['nbVotes'] >$oldNbVotes)
                $oldNbVotes = $villageois['nbVotes'];
            else 
                $nbEgaux++;
        }
        
        if($nbEgaux <1 && $voteFacultatif)
        {
            throw new \Exception('Erreur : pas de votes ! Merci de signaler ce bug à Sébastien Touzé : sebastien.touze@ecl2012.ec-lyon.fr');
            return  false;
        }
        elseif($nbEgaux >1)
        {
            //TODO : envois d'un mail
            return false;
        }
        elseif($nbEgaux == 1)
        {
            $personne = $arrayPersonnes[$nbEgaux];
            $idPersonne = $personne['id'];
            return $idPersonne;
        }
    
    }
    
}

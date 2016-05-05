<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Form\VoterHandler;
use AppBundle\Form\MessageHandler;
use AppBundle\Entity\Vote;
use AppBundle\Entity\Message;
use AppBundle\Entity\User;

class DefaultController extends Controller
{
    
    public function indexAction($visibility)
    {
        //Récupération de l'utilisateur en cours, des repositories et initialisation des valeurs
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        $dbParameters = $em->getRepository('AppBundle:Parametres')
          ->createQueryBuilder('p')->getQuery()
          ->setMaxResults(1)->getOneOrNullResult();
        $users = $em->getRepository('AppBundle:User')->findAll();
        $votes = $em->getRepository('AppBundle:Vote')->findBy(array(), array('dateVote' => 'DESC'));
        
        if($visibility == "private" && is_string($user))
          return $this->redirect($this->generateUrl('AppBundle_homepage', array('visibility' => 'public')));
        
        if($visibility == "private") 
            $votesUserToday = $em->getRepository('AppBundle:Vote')->findBy(array('votant' => $user->getId(), 'jour' => $dbParameters->getJour() ));

        $votesCastedToday = array(1 => false, 2 => false, 3 => false);
        
        $alivePlayers = array();
        $deadPlayers = array();
        $listvotesPlayers = array();
        $listvotesPlayersWW = array();

        //Changement de jour/nuit, et autres actions de fin de vote
        $dbParameters = $this->dayNightSwicher($dbParameters, $em);
        $em->flush();

        if($visibility == "private") 
            foreach($votesUserToday as $vote)
                {$votesCastedToday[$vote->getType()] = $vote->getVotePour();}
        
        
        // Récupération des joueurs et génération des listes
        foreach( $users as $player )
        {
            if($player->getVivant() == true)
            {
                $alivePlayers[$player->getId()] = $player; 
                if ($player != $user)
                {
                    $listvotesPlayers[$player->getId()] = $player->getUsername();
                    if($player->getRole()->getId() != 2)
                        {$listvotesPlayersWW[$player->getId()]= $player->getUsername();}
                }
            }
            else
                {$deadPlayers[$player->getDateMort()->format('d/m/Y').'-'.$player->getTypeMort()] = $player;}
        }
        
        
        //Déterminer si l'on est le jour ou la nuit et gestion des paramètres
        $dayNight = $dbParameters->getJour();
        
        $parameter['jour'] = $dayNight == 1 ? "jour" : "nuit";
        $parameter['heureNuit'] = $dbParameters->getheureNuit();
        $parameter['heureJour'] = $dbParameters->getheureJour();
        $mayor = 'aucun';
        if($testMayor = $em->getRepository('AppBundle:User')->findOneBy(array('maire' => true)) )
            $mayor = $testMayor;
        
        
        
        if($visibility == "private")
        {
            //Formulaire pour le vote des vilageois
            $voteAgainst = new Vote;
            $formVillageVote = $this->createFormBuilder($voteAgainst)
                            ->add('votePour', 'choice', array('choices' => $listvotesPlayers,
                'multiple' => false, ))
                            ->add('type', 'hidden', array('data' => 1, ))
                            ->getForm();
            
            
            //Formulaire pour le vote des loups
            $voteWW = new Vote;
            $formVoteWolfs = $this->createFormBuilder($voteWW)
                            ->add('votePour', 'choice', array('choices' => $listvotesPlayersWW,
                'multiple' => false, ))
                            ->add('type', 'hidden', array('data' => '2'))
                            ->getForm();
            
            //Formulaire pour le vote d'élection du maire
            $voteM = new Vote;
            $formMayorVote = $this->createFormBuilder($voteM)
                            ->add('votePour', 'choice', array('choices' => $alivePlayers,
                'multiple' => false, ))
                            ->add('type', 'hidden', array('data' => '3'))
                            ->getForm();
            
            //Traitement des formulaires s'il l'un d'entre eux a été remplis
            $formInfoHandler = new VoterHandler($formVillageVote, $this->get('request'), $em, $user);
            if($formInfoHandler->process())
            {
                return $this->render('AppBundle:Default:confirmation.html.twig', array('parameters' => $parameter));
            }
            else 
            {
                $formInfoHandler = new VoterHandler($formVoteWolfs, $this->get('request'), $em, $user);
                if($formInfoHandler->process())
                {
                    return $this->render('AppBundle:Default:confirmation.html.twig', array('parameters' => $parameter));
                }
                else
                {
                    $formInfoHandler = new VoterHandler($formMayorVote, $this->get('request'), $em, $user);
                    if($formInfoHandler->process())
                    {
                        return $this->render('AppBundle:Default:confirmation.html.twig', array('parameters' => $parameter));
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
        }
        //Récupération des messages : 
        $messages = $em->getRepository('AppBundle:Message')->findBy(array(), array('date' => 'DESC'));

        
        //Récupération des votes par ordre anté-chronologique
        $villagersVotes = array();
        $mayorVotes = array();
        $wWVotes = array();
        
        $votesDay = '';
        
        foreach($votes as $vote)
        {
            if($votesDay != $vote->getDateVote()->format('d/m/Y'))
            {
                $votesDay = $vote->getDateVote()->format('d/m/Y');
                $villagersVotes[] = array('votant' => 'date', 'pour' => $votesDay, );
                if (array_key_exists($votesDay.'-2', $deadPlayers)) {$villagersVotes[] = array('votant' => 'wWDead', 'pour' => $deadPlayers[$votesDay.'-2']);}
                if (array_key_exists($votesDay.'-1', $deadPlayers)) {$villagersVotes[] = array('votant' => 'burnedDead', 'pour' => $deadPlayers[$votesDay.'-1']);}
                $wWVotes[] = array('votant' => 'date','pour' => $votesDay, );
                $mayorVotes[] = array('votant' => 'date','pour' => $votesDay, );
            }
            switch ($vote->getType())
            {
            case 1:
            {
                $villagersVotes[] = array('votant' => $vote->getVotant(),
                                            'pour' => $vote->getVotePour(), );
                break;
            }
            case 2:
            {
                $wWVotes[] = array('votant' => $vote->getVotant(),
                                        'pour' => $vote->getVotePour(), );
                break;
            }
            case 3:
            {
                $mayorVotes[] = array('votant' => $vote->getVotant(),
                                        'pour' => $vote->getVotePour(), );
                break;
            }
            }
        }
        
        //Synthèse des votes en cours dans le village
        $arraySynthesisVillageVotes = $em->getRepository('AppBundle:Vote')->findPersoneElue(1, $dbParameters->getJour());
        $arraySynthesisMayorVotes = $em->getRepository('AppBundle:Vote')->findPersoneElue(3, $dbParameters->getJour());
        
        $userRepository = $em->getRepository('AppBundle:User');
        foreach($arraySynthesisMayorVotes as $vote) 
        {
            $vote = $userRepository->find($vote['id']);
        }
        foreach($arraySynthesisVillageVotes as $vote) 
        {
            $vote = $userRepository->find($vote['id']);
        }
        
        if($visibility == "private") 
            return $this->render('AppBundle:Default:index.html.twig', 
                                array('user' => $user, 'deadPlayers' => $deadPlayers, 'alivePlayers' => $alivePlayers,
                                 'parameters' => $parameter, 'formJour' => $formVillageVote->createView(),
                                 'formNuit' => $formVoteWolfs->createView(), 'formMaire' => $formMayorVote->createView(), 
                                 'villagersVotes' => $villagersVotes, 'mayorVotes' => $mayorVotes, 'wWVotes' => $wWVotes, 
                                 'votesCastedToday' => $votesCastedToday, 
                                 'formMessage' => $formMessage->createView(), 'messages' => $messages, 'maire' => $mayor, 
                                 'votesParPersonneVillage' => $arraySynthesisVillageVotes, 
                                 'votesParPersonneMaire' => $arraySynthesisMayorVotes, ));
        else
        return $this->render('AppBundle:Unconnected:index.html.twig', 
                                array('deadPlayers' => $deadPlayers, 'alivePlayers' => $alivePlayers,
                                 'parameters' => $parameter, 
                                 'villagersVotes' => $villagersVotes, 'mayorVotes' => $mayorVotes,  
                                 'votesCastedToday' => $votesCastedToday, 'messages' => $messages, 'maire' => $mayor, 
                                 'votesParPersonneVillage' => $arraySynthesisVillageVotes, 
                                 'votesParPersonneMaire' => $arraySynthesisMayorVotes, ));
    }
    
    
    private function dayNightSwicher($dbParameters, $em) {
        //Alternance Jour/Nuit
        $currentTime = intval(date("H"));
        if( $dbParameters->getJour() == 0 && ($currentTime > $dbParameters->getHeureJour() && $currentTime < $dbParameters->getHeureNuit() ))
        {
        
            $nbVoters = $em->getRepository('AppBundle:Vote')->findNumberVoter(3, $dbParameters->getJour());
            if($nbVoters > 0)
            {
                $arrayDevoured = $em->getRepository('AppBundle:Vote')->findPersoneElue(3, $dbParameters->getJour());
                
                $idDevoured = $this->validerVote($arrayDevoured);
                
                if($idDevoured)
                {
                    $dead = $em->getRepository('AppBundle:User')->find($idDevoured);
                    $dead->setVivant(false);
                    $dead->setMaire(false);
                
                    $em->persist($dead);
                    $em->flush();
                }
                else
                    {$dbParameters->setErreur(true);}
            }
            
            //Passage Nuit->Jour
            $dbParameters->setJour(1);
            
        }
        elseif($dbParameters->getJour() == 1 && ($currentTime >= $dbParameters->getHeureNuit() || $currentTime <= $dbParameters->getHeureJour() ))
        {
            //Élection du maire et condamnation
            $arrayCondamned = $em->getRepository('AppBundle:Vote')->findPersoneElue(1, $dbParameters->getJour());
            $arrayMayor = $em->getRepository('AppBundle:Vote')->findPersoneElue(2, $dbParameters->getJour());
            
            $idCondamned = $this->validerVote($arrayCondamned);
            $idMayor = $this->validerVote($arrayMayor, true);
            
            if($idMayor)
            {
                $mayor = $em->getRepository('AppBundle:User')->find($idMayor);
                $mayor->setMaire(true);
                $em->persist($mayor);
            }
            else
                {$dbParameters->setErreur(true);}
            
            if($idCondamned)
            {
                $dead = $em->getRepository('AppBundle:User')->find($idCondamned);
                $dead->setVivant(false);
                $dead->setMaire(false);
                $em->persist($dead);
            }
            else
                {$dbParameters->setErreur(true);}
            
            $em->flush();
            
            //Passage Nuit->Jour
            $dbParameters->setJour(0);
            $dbParameters->jourSuivant();
        }
        
        return $dbParameters;
    }
    
    

    private function validerVote($arrayPeople, $facultativeVote = false) {
    
        $oldNbVotes =  0;
        $nbEquality = 0;
        
        foreach($arrayPeople as $villagers)
        {
            if($villagers['nbVotes'] < $oldNbVotes)
                break;
            elseif($villagers['id'] != 0)
                $nbEquality++;
        }

        if($nbEquality <1 && !$facultativeVote)
        {
            throw new \Exception('Erreur 01 : pas de votes à valider ! 
            Si le jeu est au premier jour et qu\'aucun vote n\'a été effectué ce message est normal, merci de mettre à l\'heure les paramètres (passer le paramètre jour/nuit à sa valeur actuelle). 
            Dans tout autre cas, merci de signaler ce bug à Sébastien Touzé : <a href="mailto:sebastien.touze@ecl2012.ec-lyon.fr"sebastien.touze@ecl2012.ec-lyon.fr</a>');
        }
        elseif($nbEquality >1)
        {
            //TODO : envois d'un mail
            return false;
        }
        elseif($nbEquality == 1)
        {
            $person = $arrayPeople[$nbEquality-1];
            $idPerson = $person['id'];
            return $idPerson;
        }
    }
    
}

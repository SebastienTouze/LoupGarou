<?php

namespace LG\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use LG\UserBundle\Form\VoterHandler;
use LG\UserBundle\Form\MessageHandler;
use LG\UserBundle\Entity\Vote;
use LG\UserBundle\Entity\Message;
use LG\UserBundle\Entity\User;

class UnconnectedController extends Controller
{
    
    public function indexAction()
    {
        //Récupération de l'utilisateur en cours, des repositories et initialisation des valeurs
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        $dbParametres = $em->getRepository('LGUserBundle:Parametres')->find(1);
        $users = $em->getRepository('LGUserBundle:User')->findAll();
        $votes = $em->getRepository('LGUserBundle:Vote')->findBy(array(), array('dateVote' => 'DESC'));

        $votesEffectueesAujourdhui = array(1 => false, 2 => false, 3 => false);
        
        $joueursVivants = array();
        $joueursMorts = array();
        $listJoueursVote = array();

        //Changement de jour/nuit, et autres actions de fin de vote
        $dbParametres = $this->changerJourNuit($dbParametres, $em);
        
        $em->flush();
        
        
        // Récupération des joueurs et génération des listes
        foreach( $users as $joueur )
        {
            if($joueur->getVivant() == true)
                {
                    $joueursVivants[$joueur->getId()] = $joueur; 
                    $listJoueursVote[$joueur->getId()] = $joueur->getUsername();
                    if($joueur->getRole()->getId() != 2)
                        {$listJoueursVoteLG[$joueur->getId()]= $joueur->getUsername();}
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
                $votesVillageois[] = array('votant' => '*','pour' => $jour, );
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
        for($i =0 ; $i < count($arraySyntheseVotesMaire) ; $i++) 
        {
            $arrayVote = $arraySyntheseVotesMaire[$i];
            $arrayVote['id'] = $userRepository->find($arrayVote['id']);
            $arraySyntheseVotesMaire[$i] = $arrayVote;
        }
        for($i =0 ; $i < count($arraySyntheseVotesVillage) ; $i++) 
        {
            $arrayVote = $arraySyntheseVotesVillage[$i];
            $arrayVote['id'] = $userRepository->find($arrayVote['id']);
            $arraySyntheseVotesVillage[$i] = $arrayVote;
        }
        
        //Récupération des messages : 
        $messages = $em->getRepository('LGUserBundle:Message')->findBy(array(), array('date' => 'DESC'));
        
        return $this->render('LGUserBundle:Unconnected:index.html.twig', 
                            array('joueursMorts' => $joueursMorts, 'joueursVivants' => $joueursVivants,
                             'parametres' => $parametre, 'votesVillageois' => $votesVillageois, 
                             'votesMaire' => $votesMaire, 'votesLoups' => $votesLoups, 
                             'votesEffectueesAujourdhui' => $votesEffectueesAujourdhui, 
                             'messages' => $messages, 'maire' => $maire, 
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
            elseif($villageois['nbVotes'] > $oldNbVotes)
            {
                $oldNbVotes = $villageois['nbVotes'];
                $nbEgaux++;
            }
            else 
                $nbEgaux++;
        }

        if($nbEgaux <1 && !$voteFacultatif)
        {
            throw new \Exception('Erreur : pas de votes ! Merci de signaler ce bug à Sébastien Touzé : sebastien.touze@ecl2012.ec-lyon.fr');
            return  false;
        }
        elseif($nbEgaux <1 && $voteFacultatif)
        {
            return false;
        }
        elseif($nbEgaux >1)
        {
            //TODO : envois d'un mail
            return false;
        }
        elseif($nbEgaux == 1)
        {
            $personne = $arrayPersonnes[$nbEgaux-1];
            $idPersonne = $personne['id'];

            return $idPersonne;
        }
    
    }
    
}

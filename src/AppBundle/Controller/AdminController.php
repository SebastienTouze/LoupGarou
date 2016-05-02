<?php

/*******************************************************************************
 * AdminController class
 * 
 * This Controller is used for all Admin tasks:
 *  - Initialize the database for a new game (initializeAction)
 * 
 * Created by Sébastien Touzé on 2nd May 2016
 ******************************************************************************/

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Vote;
use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use AppBundle\Entity\Role;
use AppBundle\Entity\Parametres;

class AdminController extends Controller
{
  /*
   * InitializeAction
   * 
   * This initialize the database with appropriate values so the App can start. 
   * This function suppose that the database is already created and configured 
   * in the parameters.yml file. It also suppose that the console command 
   * "doctrine:schema:create -f" have beeen run and that the DB schema is up to date
   * 
   * What it does: 
   *  - delete all old data on all tables
   *  - parameters: game on day, time (day counter) to 0, start/end of day times
   *  - roles: create an "Admin" role in the database
   *  - users: create an admin users with password=admin and considered as alive
   * 
   * This method should be updated on evry new Entity creation to guarantee 
   * correct cleaning of old data from one game to the other. 
   * 
  */
  public function initializeAction()
    {
      $em = $this->getDoctrine()->getManager();
      
      /***   Delete data in tables   ***/
      $parametersRepo = $em->getRepository('AppBundle:Parametres');
      $queryParam = $parametersRepo->createQueryBuilder('p')
        ->delete()
        ->getQuery()
        ->execute();
        
      $voteRepo = $em->getRepository('AppBundle:Vote');
      $queryVote = $voteRepo->createQueryBuilder('v')
        ->delete()
        ->getQuery()
        ->execute();
      
      $msgRepo = $em->getRepository('AppBundle:Message');
      $queryMsg = $msgRepo->createQueryBuilder('m')
        ->delete()
        ->getQuery()
        ->execute();
      
      $userRepo = $em->getRepository('AppBundle:User');
      $queryUser = $userRepo->createQueryBuilder('u')
        ->delete()
        ->getQuery()
        ->execute();
        
      $roleRepo = $em->getRepository('AppBundle:Role');
      $queryRole = $roleRepo->createQueryBuilder('r')
        ->delete()
        ->getQuery()
        ->execute();
      
      /***   Initialize the parameters   ***/
      $initialParams = new Parametres;
      $initialParams->setJour(1);
      $initialParams->setHeureJour("10h");
      $initialParams->setHeureNuit("21h");
      $initialParams->setTemps(0);
      $initialParams->setErreur(false);
      
      $em->persist($initialParams);
      
      /***   Initialize the roles   ***/
      $adminRole = new Role;
      $adminRole->setNom("Admin");
      $adminRole->setDescription("Application Admin");
      $adminRole->setImage("admin.png");
      $adminRole->setPouvoir1(false);
      //$adminRole->setPouvoir1Desc("");
      $adminRole->setPouvoir2(false);
      
      $em->persist($adminRole);
      $em->flush(); //Commiting here the modifications as we need the Role for the User creation
      $em->refresh($adminRole); //Refresh the rol so that we can use the database id just created
      
      /***   Initialize the user   ***/
      $userManager = $this->container->get('fos_user.user_manager');
      
      $adminUser = $userManager->createUser();
      $em->persist($adminUser);
      
      $adminUser->setUsername('admin');
      $adminUser->setEmail('admin@localhost');
      $adminUser->setPlainPassword('admin');
      $adminUser->setEnabled(true);
      $adminUser->setRole($adminRole);
      $adminUser->setVivant(true);
      $userManager->updateUser($adminUser, false);
      $em->flush();
      
      return $this->redirect($this->generateUrl('AppBundle_homepage'));
      
    }
}

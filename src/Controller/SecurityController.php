<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{

    /**
     * @Route("/register", name="register")
     *
     */
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher)
    {

        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            $mdp= $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($mdp);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Félicitation, votre inscription s\'est bien déroulée. Connectez vous à présent');
            return $this->redirectToRoute('login');



        endif;


        return $this->render('security/register.html.twig', [
            'form'=>$form->createView()
        ]);
    }

    /**
    *@Route("/login", name="login")
    *
    */
    public function login(){

       return $this->render('security/login.html.twig', [
       ]);
    }

    /**
    *@Route("/logout", name="logout")
    *
    */
    public function logout(){



    }


}

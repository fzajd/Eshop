<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
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

            $mdp = $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($mdp);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Félicitation, votre inscription s\'est bien déroulée. Connectez vous à présent');
            return $this->redirectToRoute('login');


        endif;


        return $this->render('security/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/login", name="login")
     *
     */
    public function login()
    {

        return $this->render('security/login.html.twig', [
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     *
     */
    public function logout()
    {


    }


    /**
     * @Route("/resetForm", name="resetForm")
     * @Route("/resetToken", name="resetToken")
     */
    public function resetForm(UserRepository $repository, Request $request, EntityManagerInterface $manager, MailerInterface $mailer)
    {

        if (!empty($_POST)):

            $email = $request->request->get('email');
            $user = $repository->findOneBy(['email' => $email]);

            if (!$user):

                $this->addFlash('danger', 'Aucun compte à cette adresse mail');
                return $this->redirectToRoute('resetForm');

            else:
                $id = $user->getId();

                $token = uniqid();
                $user->setToken($token);
                $manager->persist($user);
                $manager->flush();

                $email = (new TemplatedEmail())
                    ->from('dorancosalle78@gmail.com')
                    ->to($email)
                    ->subject('Demande de Réinitialisation de mot de passe')
                    ->htmlTemplate('home/email_template.html.twig');
                $cid = $email->embedFromPath('logo.png', 'logo');

                $email->context([
                    'message' => 'Vous venez de faire une demande de réinitialisation de mot de passe, cliquez ci-dessous pour y procéder',
                    'nom' => '',
                    'prenom' => '',
                    'subject' => 'Demande de Réinitialisation de mot de passe',
                    'from' => 'dorancosalle78@gmail.com',
                    'cid' => $cid,
                    'liens' => 'http://127.0.0.1:8000/resetPassword/' . $token . '/' . $id,
                    'objectif' => 'Réinitialiser'

                ]);

                $mailer->send($email);

                $this->addFlash('success', 'Un Email de récupération viens de vous être envoyé');
                return $this->redirectToRoute('home');


            endif;


        endif;


        return $this->render('security/resetForm.html.twig', [
        ]);
    }

    /**
     * @Route("/resetPassword/{token}/{id}", name="resetPassword")
     * @Route("/finalReset", name="finalReset")
     */
    public function resetPassword(UserRepository $repository, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher, $token = null, $id = null)
    {

        if ($token && $id):
            $user = $repository->findOneBy(['token' => $token, 'id' => $id]);

            if ($user):

                return $this->render('security/resetPassword.html.twig', [
                    'id' => $id
                ]);

            else:
                $this->addFlash('danger', 'Une erreur s\'est produite, veuillez réitérer une demande de réinitialisation');
                return $this->redirectToRoute('resetForm');
            endif;

        endif;

        if (!empty($_POST)):

            $id = $request->request->get('id');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirmPassword');

            if ($password !== $confirmPassword):
                    $this->addFlash('danger', 'Les mots de passe ne correspondent pas');
                    return $this->redirectToRoute('finalReset',[
                        'id'=>$id
                    ]);
            else:

                //dd($id);
                $user=$repository->find($id);

            $mdp=$hasher->hashPassword($user, $password);
            $user->setPassword($mdp);
            $user->setToken(null);
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Mot de passe réinitialisé, connectez vous à présent');
                return $this->render('security/login.html.twig');



            endif;


        endif;


    }


}

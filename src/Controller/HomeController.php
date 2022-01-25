<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\CategoryType;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\Panier\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(ProductRepository $repository): Response
    {
        // Ici nous allons récupérer l'intégralité des produit enregistrés en BDD
        // requete de select * FROM product.
        // Pour les requêtes de SELECT il nous faut injecter en dépandance le repository de product
        // ProductRepository et utiliser sa méthode findAll() présente d'origine
        $products = $repository->findAll();


        return $this->render('home/home.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * @Route("/emailForm", name="emailForm")
     *
     */
    public function emailForm()
    {


        return $this->render('home/email_form.html.twig', [
        ]);
    }

    /**
     * @Route("/emailSend", name="emailSend")
     *
     */
    public function emailSend(Request $request, MailerInterface $mailer)
    {

        if (!empty($_POST)):

            $message = $request->request->get('message');
            $nom = $request->request->get('surname');
            $prenom = $request->request->get('name');
            $motif = $request->request->get('need');
            $from = $request->request->get('email');

            $email = (new TemplatedEmail())
                ->from($from)
                ->to('dorancosalle78@gmail.com')
                ->subject($motif)
                ->htmlTemplate('home/email_template.html.twig');
            $cid = $email->embedFromPath('logo.png', 'logo');

            $email->context([
                'message' => $message,
                'nom' => $nom,
                'prenom' => $prenom,
                'subject' => $motif,
                'from' => $from,
                'cid' => $cid,
                'liens' => 'http://127.0.0.1:8000',
                'objectif' => 'Accéder au site'

            ]);

            $mailer->send($email);


            return $this->redirectToRoute('home', [

            ]);
        endif;


    }


    /**
     * @Route("/addCart/{id}/{param}", name="addCart")
     *
     */
    public function addCart(PanierService $panierService, $id, $param)
    {

        $panierService->add($id);

        //dd($panierService->fullCart());

        if ($param == 'home'):
            return $this->redirectToRoute('home');
        else:
            return $this->redirectToRoute('cart');
        endif;


    }


    /**
    *@Route("/removeCart/{id}", name="removeCart")
    *
    */
    public function removeCart(PanierService $panierService,$id){
       $panierService->remove($id);

       return $this->redirectToRoute('cart');
    }

    /**
     *@Route("/deleteCart/{id}", name="deleteCart")
     *
     */
    public function deleteCart(PanierService $panierService, $id){
        $panierService->delete($id);

        return $this->redirectToRoute('cart');
    }


    /**
     * @Route("/cart", name="cart")
     *
     */
    public function cart(PanierService $panierService)
    {

        $items = $panierService->fullCart();
        $total = $panierService->Total();

        return $this->render('home/cart.html.twig', [
            'items' => $items,
            'total' => $total,
            'titre' => 'Mon panier'

        ]);
    }

    /**
    *@Route("/destroyCart", name="destroyCart")
    *
    */
    public function destroyCart(Request $request, PanierService $panierService){

       //$request->cookies->set('panierDestroy', $panierService->fullCart());

        $panierService->destroy();

       return $this->redirectToRoute('home');

    }


}

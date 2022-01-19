<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(): Response
    {
        return $this->render('home/home.html.twig', [

        ]);
    }

    /**
     * @Route("/addProduct", name="addProduct")
     */
    public function addProduct(Request $request)
    {
        // ici nous allons créer un formulaire via le packager form de symfony, au préalable nous avons renseigné à twig d'utiliser bootstrap5 dans config/package/twig.yaml, en copiant form_themes: ['bootstrap_5_layout.html.twig'] sous default_path


        // ici on instancie un nouvel objet product, vide à présent, que le formulaire de symfony remplira automatiquement à la soumission du formulaire
        $product= new Product();

        // Nous avons créé une classe ProductType qui permet de générer le formulaire d'ajout de produit, il faut dans le controller importer cette classe et relier le formulaire à notre instanciation d'entité product
        $form=$this->createForm(ProductType::class, $product );
        // on va chercher dans l'objet handlerequest qui permet de récupérer chaques données saisies des champs de formulaire. Il s'assure de la coordination entre ProductType et $product afin de générer les bons setteurs pour chaques propriétés de l'entité;
        // Les données de formulaire transitant en POST il nous appeler la classe REQUEST (de http\foundation) qui permet de véhiculer les informations des superglobales ($_GET, $_POST, $_COOKIE....)
        $form->handleRequest($request);


        return $this->render('home/addProduct.html.twig', [
            'form'=>$form->createView(),
            'titre'=>'Ajout de produit'

        ]);
    }




}

<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
    public function addProduct(Request $request, EntityManagerInterface $manager)
    {
        // ici nous allons créer un formulaire via le packager form de symfony, au préalable nous avons renseigné à twig d'utiliser bootstrap5 dans config/package/twig.yaml, en copiant form_themes: ['bootstrap_5_layout.html.twig'] sous default_path


        // ici on instancie un nouvel objet product, vide à présent, que le formulaire de symfony remplira automatiquement à la soumission du formulaire
        $product = new Product();

        // Nous avons créé une classe ProductType qui permet de générer le formulaire d'ajout de produit, il faut dans le controller importer cette classe et relier le formulaire à notre instanciation d'entité product
        $form = $this->createForm(ProductType::class, $product);
        // on va chercher dans l'objet handlerequest qui permet de récupérer chaques données saisies des champs de formulaire. Il s'assure de la coordination entre ProductType et $product afin de générer les bons setteurs pour chaques propriétés de l'entité;
        // Les données de formulaire transitant en POST il nous appeler la classe REQUEST (de http\foundation) qui permet de véhiculer les informations des superglobales ($_GET, $_POST, $_COOKIE....)
        $form->handleRequest($request);

        // ici on va informer par la condition if que si le bouton submit a été préssé et que les données de formulaires sont conforme à notre entité et à nos contrainte, il peut faire intervenir doctrine (notre ORM) et son manager pour préparer puis executer la requête

        if ($form->isSubmitted() && $form->isValid()):

            // on récupère ici toutes les données de notre input type file ayant name=>'picture'
            $file = $form->get('picture')->getData();

            // ici on place une condition pour vérifier qu'une photo a bien été uploadée
            if ($file):

                $fileName=date('YmdHis').'-'.uniqid().'-'.$file->getClientOriginalName();

              // envoie dans public/upload

                try {
                    $file->move($this->getParameter('upload_directory'), $fileName);
                    // la méthode move() attend 2 paramètres et permet de déplacer le fichier uploadé des fichiers temporaires du server vers un emplacement défini
                    // param1: l'emplacement défini, paramétré au préalable dans config/services.yaml
                    //   upload_directory : '%kernel.project_dir%/public/upload'
                    // param2 : le nom du fichier à déplacer

                }
                catch (FileException $exception){
                    $this->redirectToRoute('addProduct', [
                        'erreur'=>$exception
                    ]);
                }

                // l'objet $product n'étant setté sur l'information picture (picture étant un input type file et les données attendues en BDD étant de type string=>le nom du fichier)
                $product->setPicture($fileName);

                // on demande au manager de Doctrine de préparer la requête
                $manager->persist($product);

                // on execute la ou les requêtes
                $manager->flush();






            endif;


        endif;


        return $this->render('home/addProduct.html.twig', [
            'form' => $form->createView(),
            'titre' => 'Ajout de produit'

        ]);
    }


}

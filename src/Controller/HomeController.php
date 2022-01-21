<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\CategoryType;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
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



}

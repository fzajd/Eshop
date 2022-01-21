<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\SubCategory;
use App\Form\CategoryType;
use App\Form\ProductType;
use App\Form\SubCategoryType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{

    /**
     * @Route("/addProduct", name="addProduct")
     */
    public function addProduct(Request $request, EntityManagerInterface $manager)
    {
        // ici nous allons créer un formulaire via le packager form de symfony, au préalable nous avons renseigné à twig d'utiliser bootstrap5 dans config/package/twig.yaml, en copiant form_themes: ['bootstrap_5_layout.html.twig'] sous default_path


        // ici on instancie un nouvel objet product, vide à présent, que le formulaire de symfony remplira automatiquement à la soumission du formulaire
        $product = new Product();

        // Nous avons créé une classe ProductType qui permet de générer le formulaire d'ajout de produit, il faut dans le controller importer cette classe et relier le formulaire à notre instanciation d'entité product
        $form = $this->createForm(ProductType::class, $product, ['add' => true]);
        // on va chercher dans l'objet handlerequest qui permet de récupérer chaques données saisies des champs de formulaire. Il s'assure de la coordination entre ProductType et $product afin de générer les bons setteurs pour chaques propriétés de l'entité;
        // Les données de formulaire transitant en POST il nous appeler la classe REQUEST (de http\foundation) qui permet de véhiculer les informations des superglobales ($_GET, $_POST, $_COOKIE....)
        $form->handleRequest($request);

        // ici on va informer par la condition if que si le bouton submit a été préssé et que les données de formulaires sont conforme à notre entité et à nos contrainte, il peut faire intervenir doctrine (notre ORM) et son manager pour préparer puis executer la requête

        if ($form->isSubmitted() && $form->isValid()):

            // on récupère ici toutes les données de notre input type file ayant name=>'picture'
            $file = $form->get('picture')->getData();

            // ici on place une condition pour vérifier qu'une photo a bien été uploadée
            if ($file):

                $fileName = date('YmdHis') . '-' . uniqid() . '-' . $file->getClientOriginalName();

                // envoie dans public/upload

                try {
                    $file->move($this->getParameter('upload_directory'), $fileName);
                    // la méthode move() attend 2 paramètres et permet de déplacer le fichier uploadé des fichiers temporaires du server vers un emplacement défini
                    // param1: l'emplacement défini, paramétré au préalable dans config/services.yaml
                    //   upload_directory : '%kernel.project_dir%/public/upload'
                    // param2 : le nom du fichier à déplacer

                } catch (FileException $exception) {
                    $this->redirectToRoute('addProduct', [
                        'erreur' => $exception
                    ]);
                }

                // l'objet $product n'étant setté sur l'information picture (picture étant un input type file et les données attendues en BDD étant de type string=>le nom du fichier)
                $product->setPicture($fileName);

                // on demande au manager de Doctrine de préparer la requête
                $manager->persist($product);

                // on execute la ou les requêtes
                $manager->flush();

                $this->addFlash('success', 'Le produit a bien été enregistré');


                return $this->redirectToRoute('home');


            endif;


        endif;


        return $this->render('admin/addProduct.html.twig', [
            'form' => $form->createView(),
            'titre' => 'Ajout de produit'

        ]);
    }


    /**
     * @Route("/listProduct", name="listProduct")
     */
    public function listProduct(ProductRepository $repository)
    {
        $products = $repository->findAll();


        return $this->render('admin/listProduct.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * @Route("/editProduct/{id}", name="editProduct")
     *
     */
    public function editProduct(Request $request, EntityManagerInterface $manager, Product $product)
    {

        // la différence entre l'ajout et la modification:
        // -en ajout=> $product est instancié (new product()) et vide par conséquent
        // -en modification=> $product est rempli de ses données présentes en BDD
        // lorsque l'on passe un id en parametre d'une route, si l'entité correspondante à cette id
        // est injectée en dépendance, symfony rempli par lui même l'objet $product

        $form = $this->createForm(ProductType::class, $product, ['edit' => true]);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()):

            $file = $form->get('editPicture')->getData();

            if ($file):

                $fileName = date('YmdHis') . '-' . uniqid() . '-' . $file->getClientOriginalName();

                try {
                    $file->move($this->getParameter('upload_directory'), $fileName);
                    unlink($this->getParameter('upload_directory') . '/' . $product->getPicture());


                } catch (FileException $exception) {
                    $this->redirectToRoute('editProduct', [
                        'erreur' => $exception
                    ]);
                }


                $product->setPicture($fileName);


            endif;

            $manager->persist($product);


            $manager->flush();

            $this->addFlash('success', 'Le produit a bien été modifié');


            return $this->redirectToRoute('listProduct');

        endif;


        return $this->render('admin/editProduct.html.twig', [
            'form' => $form->createView(),
            'titre' => 'Modification de produit',
            'product' => $product

        ]);

    }

    /**
     * @Route("/deleteProduct/{id}", name="deleteProduct")
     *
     */
    public function deleteProduct(EntityManagerInterface $manager, Product $product)
    {

        unlink($this->getParameter('upload_directory') . '/' . $product->getPicture());
        $manager->remove($product);
        $manager->flush();

        $this->addFlash('success', 'Le produit a bien été supprimé');
        return $this->redirectToRoute('listProduct');
    }

    /**
     * @Route("/category", name="category")
     * @Route("/editCategory/{id}", name="editCategory")
     *
     */
    public function category(Request $request, EntityManagerInterface $manager, CategoryRepository $repository, $id = null)
    {
        $categories=$repository->findAll();


        if (!empty($id)):

            $category = $repository->find($id);

        else:
            $category = new Category();
        endif;

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            $manager->persist($category);
            $manager->flush();

            if (!empty($id)):
                $this->addFlash('success', 'Catégorie modifiée');
            else:
                $this->addFlash('success', 'Catégorie ajoutée');
            endif;

            return $this->redirectToRoute('category');

        endif;


        return $this->render('admin/category.html.twig', [
            'form'=>$form->createView(),
            'categories'=>$categories,
            'titre'=>'Gestion catégories'


        ]);
    }

    /**
     *@Route("/deleteCategory/{id}", name="deleteCategory")
     *
     */
    public function deleteCategory(Category $category, EntityManagerInterface $manager){
        $manager->remove($category);
        $manager->flush();
        $this->addFlash('success', 'Catégorie supprimée');

        return $this->redirectToRoute('category');
    }



    /**
     * @Route("/subCategory", name="subCategory")
     * @Route("/editSubCategory/{id}", name="editSubCategory")
     *
     */
    public function subCategory(Request $request, EntityManagerInterface $manager, SubCategoryRepository $repository, $id = null)
    {
        $subCategories=$repository->findAll();


        if (!empty($id)):

            $subCategory = $repository->find($id);

        else:
            $subCategory = new SubCategory();
        endif;

        $form = $this->createForm(SubCategoryType::class,  $subCategory);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            $manager->persist($subCategory);
            $manager->flush();

            if (!empty($id)):
                $this->addFlash('success', 'Sous-Catégorie modifiée');
            else:
                $this->addFlash('success', 'Sous-Catégorie ajoutée');
            endif;

            return $this->redirectToRoute('subCategory');

        endif;


        return $this->render('admin/subCategory.html.twig', [
            'form'=>$form->createView(),
            'subCategories'=> $subCategories,
            'titre'=>'Gestion sous-catégories'


        ]);
    }

    /**
     *@Route("/deleteSubCategory/{id}", name="deleteSubCategory")
     *
     */
    public function deleteSubCategory(SubCategory $subCategory, EntityManagerInterface $manager){
        $manager->remove($subCategory);
        $manager->flush();
        $this->addFlash('success', 'Sous-Catégorie supprimée');

        return $this->redirectToRoute('SubCategory');
    }









}

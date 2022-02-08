<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Color;
use App\Entity\Delivery;
use App\Entity\Detail;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Promo;
use App\Entity\Size;
use App\Entity\Stock;
use App\Entity\SubCategory;
use App\Entity\Suppliers;
use App\Form\CategoryType;
use App\Form\ColorType;
use App\Form\ProductType;
use App\Form\SizeType;
use App\Form\SubCategoryType;
use App\Form\SuppliersType;
use App\Repository\CategoryRepository;
use App\Repository\ColorRepository;
use App\Repository\DeliveryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\PromoRepository;
use App\Repository\SizeRepository;
use App\Repository\StockRepository;
use App\Repository\SubCategoryRepository;
use App\Repository\SuppliersRepository;
use App\Service\Panier\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @IsGranted("ROLE_ADMIN")
 *
 */
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

                $this->addFlash('success', 'Le produit a bien été enregistré renseignez à présent les stocks');
                foreach ($product->getColors() as $color):
                    foreach ($product->getSizes() as $size):
                        $stock = new Stock();
                        $stock->setProduct($product)->setSize($size)->setColor($color);
                        $manager->persist($stock);

                    endforeach;
                endforeach;
                $manager->flush();
                return $this->redirectToRoute('listStock');


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
    public function editProduct(Request $request, EntityManagerInterface $manager, Product $product, StockRepository $stockRepository)
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

            foreach ($product->getColors() as $color):
                foreach ($product->getSizes() as $size):
                    $find=$stockRepository->findOneBy(['product'=>$product,'size'=>$size,'color'=>$color]);
                if (!$find):
                    $stock = new Stock();
                    $stock->setProduct($product)->setSize($size)->setColor($color);
                    $manager->persist($stock);
                endif;
                endforeach;
            endforeach;



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

        //unlink($this->getParameter('upload_directory') . '/' . $product->getPicture());
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
        $categories = $repository->findAll();


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
            'form' => $form->createView(),
            'categories' => $categories,
            'titre' => 'Gestion des sous-catégories'


        ]);
    }

    /**
     * @Route("/deleteCategory/{id}", name="deleteCategory")
     *
     */
    public function deleteCategory(Category $category, EntityManagerInterface $manager)
    {
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
        $subCategories = $repository->findAll();


        if (!empty($id)):

            $subCategory = $repository->find($id);

        else:
            $subCategory = new SubCategory();
        endif;

        $form = $this->createForm(SubCategoryType::class, $subCategory);

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
            'form' => $form->createView(),
            'subCategories' => $subCategories,
            'titre' => 'Gestion catégories'


        ]);
    }

    /**
     * @Route("/deleteSubCategory/{id}", name="deleteSubCategory")
     *
     */
    public function deleteSubCategory(SubCategory $subCategory, EntityManagerInterface $manager)
    {
        $manager->remove($subCategory);
        $manager->flush();
        $this->addFlash('success', 'Sous-Catégorie supprimée');

        return $this->redirectToRoute('subCategory');
    }



    /**
     * @Route("/listOrder", name="listOrder")
     *
     */
    public function listOrder(OrderRepository $repository)
    {

        $orders = $repository->findAll();

        return $this->render('admin/listOrder.html.twig', [
            'orders' => $orders
        ]);
    }

    /**
     * @Route("/detailOrder/{id}", name="detailOrder")
     *
     */
    public function detailOrder(OrderRepository $orderRepository, DeliveryRepository $deliveryRepository, Request $request, EntityManagerInterface $manager, $id)
    {

        $order = $orderRepository->find($id);

        if (!empty($_POST)):
            // dd($_POST);
            $delivery = $order->getDelivery();
            $predictedDate = $request->request->get('predictedDate');
            $status = $request->request->get('status');
            $delivery->setPredictedDate(new \DateTime($predictedDate));
            $delivery->setStatus($status);
            $manager->persist($delivery);
            $manager->flush();
            $this->addFlash('success', 'Livraison mise à jour');
            return $this->render('admin/detailOrder.html.twig', [
                'order' => $order
            ]);


        endif;


        return $this->render('admin/detailOrder.html.twig', [
            'order' => $order
        ]);
    }

    /**
     * @Route("/listPromo", name="listPromo")
     *
     */
    public function listPromo(PromoRepository $repository)
    {
        $promos = $repository->findBy([], ['startDate' => 'DESC']);

        //dd($promos);

        return $this->render('admin/listPromo.html.twig', [
            'promos' => $promos
        ]);
    }

    /**
     * @Route("/addPromo/{param}", name="addPromo")
     *
     */
    public function addPromo(CategoryRepository $subCategoryRepository, SubCategoryRepository $categoryRepository, Request $request, EntityManagerInterface $manager, ProductRepository $productRepository, $param)
    {

        $categories = $categoryRepository->findAll();
        $subCategories = $subCategoryRepository->findAll();

        if (!empty($_POST)):

            $code = $request->request->get('code');
            $startDate = $request->request->get('startDate');
            $endDate = $request->request->get('endDate');
            $type = $request->request->get('type');
            $value = $request->request->get('value');

            $promo = new Promo();
            $promo->setCode($code);
            $promo->setType($type);
            $promo->setValue($value);

            //dd($_POST);
            if (empty($startDate)):
                $promo->setStartDate(null);
            else:
                $promo->setStartDate(new \DateTime($startDate));
            endif;

            if (empty($endDate)):
                $promo->setEndDate(null);
            else:
                $promo->setEndDate(new \DateTime($endDate));
            endif;

            if ($param == 'section'):
                $products = $productRepository->findBy(['gender' => $request->request->get('section')]);

                $promo->setSection($request->request->get('section'));

                foreach ($products as $product):
                    $product->setPromo($promo);
                    $manager->persist($product);

                endforeach;


            endif;
            if ($param == 'category'):
                $category = $categoryRepository->find($request->request->get('category'));
                $promo->setSubCategory($category);

            endif;
            if ($param == 'subCategory'):
                $subCategory = $subCategoryRepository->find($request->request->get('subCategory'));
                $promo->setCategory($subCategory);

            endif;
            $manager->persist($promo);
            $manager->flush();

            $this->addFlash('success', 'Code promo ajouté');
            return $this->redirectToRoute('listPromo');

        endif;

        return $this->render('admin/addPromo.html.twig', [
            'param' => $param,
            'categories' => $categories,
            'subCategories' => $subCategories
        ]);
    }

    /**
     * @Route("/editPromo/{id}/{param}", name="editPromo")
     *
     *
     */
    public function editPromo(CategoryRepository $subCategoryRepository, SubCategoryRepository $categoryRepository, Request $request, EntityManagerInterface $manager, ProductRepository $productRepository, PromoRepository $promoRepository, $id, $param)
    {

        $categories = $categoryRepository->findAll();
        $subCategories = $subCategoryRepository->findAll();

        $promo = $promoRepository->find($id);


        if (!empty($_POST)):
            $code = $request->request->get('code');
            $startDate = $request->request->get('startDate');
            $endDate = $request->request->get('endDate');
            $type = $request->request->get('type');
            $value = $request->request->get('value');


            $promo->setCode($code);
            $promo->setType($type);
            $promo->setValue($value);

            //dd($_POST);
            if (empty($startDate)):
                $promo->setStartDate(null);
            else:
                $promo->setStartDate(new \DateTime($startDate));
            endif;

            if (empty($endDate)):
                $promo->setEndDate(null);
            else:
                $promo->setEndDate(new \DateTime($endDate));
            endif;

            if ($param == 'section'):
                $products = $productRepository->findBy(['gender' => $request->request->get('section')]);
                $promo->setCategory(null);
                $promo->setSubCategory(null);
                $promo->setSection($request->request->get('section'));

                foreach ($products as $product):
                    $product->setPromo($promo);
                    $manager->persist($product);

                endforeach;


            endif;
            if ($param == 'category'):
                $promo->setCategory(null);
                $promo->setSection(null);
                $category = $categoryRepository->find($request->request->get('category'));
                $promo->setSubCategory($category);

            endif;
            if ($param == 'subCategory'):
                $promo->setSubCategory(null);
                $promo->setSection(null);
                $subCategory = $subCategoryRepository->find($request->request->get('subCategory'));
                $promo->setCategory($subCategory);

            endif;
            $manager->persist($promo);
            $manager->flush();

            $this->addFlash('success', 'Code promo modifié');
            return $this->redirectToRoute('listPromo');


        endif;


        return $this->render('admin/editPromo.html.twig', [
            'promo' => $promo,
            'param' => $param,
            'categories' => $categories,
            'subCategories' => $subCategories
        ]);
    }


    /**
     * @Route("/deletePromo/{id}", name="deletePromo")
     *
     */
    public function deletePromo(PromoRepository $promoRepository, ProductRepository $productRepository, EntityManagerInterface $manager, $id)
    {

        $promo = $promoRepository->find($id);


        if ($promo->getSection() !== null):

            $products = $productRepository->findBy(['gender' => $promo->getSection()]);

            foreach ($products as $product):
                $product->setPromo(null);
                $manager->persist($product);
            endforeach;
        endif;


        $manager->remove($promo);
        $manager->flush();
        $this->addFlash('success', 'Code promo supprimé');

        return $this->redirectToRoute('listPromo');
    }



    /**
     * @Route("/stock/{id}", name="stock")
     *
     */
    public function stock(Request $request,Stock $stock,EntityManagerInterface $manager, $id)
    {


        if (!empty($_POST)):

            $value = $request->request->get('value');



            $stock->setQuantity($value);

            $manager->persist($stock);
            $manager->flush();
            $this->addFlash('success', 'stock déclaré');
            return $this->redirectToRoute('listStock');


        endif;


        return $this->render('admin/stock.html.twig', [
            'stock'=>$stock
        ]);
    }

    /**
     * @Route("/listStock", name="listStock")
     *
     */
    public function listStock(StockRepository $stockRepository)
    {

        $stocks = $stockRepository->findBy([], ['product' => 'ASC']);

        return $this->render('admin/listStock.html.twig', [
            'stocks' => $stocks
        ]);
    }

    /**
     * @Route("/searchRef", name="searchRef")
     *
     */
    public function searchRef(Request $request, StockRepository $stockRepository)
    {

        $search = $request->request->get('search');
        $stocks = $stockRepository->findBySearch($search);


        return $this->render('admin/listStock.html.twig', [
            'stocks' => $stocks
        ]);
    }

    /**
     * @Route("/listSuppliers", name="listSuppliers")
     * @Route("/editSuppliers/{id}", name="editSuppliers")
     */
    public function addSuppliers(Request $request, EntityManagerInterface $manager, SuppliersRepository $repository, $id = null)
    {

        $suppliers = $repository->findAll();

        if (!empty($id)) {
            $supplier = $repository->find($id);
        } else {
            $supplier = new Suppliers();
        }

        $form = $this->createForm(SuppliersType::class, $supplier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($supplier);
            $manager->flush();

            if (!empty($id)) {
                $this->addFlash('success', 'Fournisseur modifié');
            } else {
                $this->addFlash('success', 'Fournisseur ajouté');
            }

            return $this->redirectToRoute('listSuppliers');

        }

        return $this->render('admin/listSuppliers.html.twig', [
            'form' => $form->createView(),
            'suppliers' => $suppliers,
            'titre' => 'Gestion des fournisseurs'
        ]);
    }

    /**
     * @Route("/deleteSuppliers/{id}", name="deleteSuppliers")
     *
     */
    public function deleteSuppliers(Suppliers $suppliers, SuppliersRepository $repository, EntityManagerInterface $manager, $id)

    {
        $suppliers = $repository->find($id);

        $manager->remove($suppliers);
        $manager->flush();

        $this->addFlash('success', 'Le fournisseur a bien été supprimé');

        return $this->redirectToRoute('listSuppliers');
    }


    /**
     * @Route("/listSize", name="listSize")
     * @Route("/editSize/{id}", name="editSize")
     */
    public function listSize(SizeRepository $repository, EntityManagerInterface $manager, Request $request, $id = null)
    {

        $sizes = $repository->findAll();

        if (!empty($id)) {
            $size = $repository->find($id);
        } else {
            $size = new Size();
        }


        $form = $this->createForm(SizeType::class, $size);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($size);
            $manager->flush();

            if (!empty($id)) {
                $this->addFlash('success', 'Taille modifiée');
            } else {
                $this->addFlash('success', 'Taille ajoutée');
            }


            return $this->redirectToRoute('listSize');
        }


        return $this->render('admin/listSize.html.twig', [
            'form' => $form->createView(),
            'sizes' => $sizes,
            'titre' => 'Gestion des tailles'
        ]);
    }

    /**
     * @Route("/deleteSize/{id}", name="deleteSize")
     */
    public function deleteSize(Size $size, EntityManagerInterface $manager)
    {
        $manager->remove($size);
        $manager->flush();
        $this->addFlash('success', 'Taille supprimée');


        return $this->redirectToRoute('listSize');
    }

    /**
     * @Route("/color", name="color")
     * @Route("/editColor/{id}",name="editColor")
     */
    public function color(Request $request, EntityManagerInterface $manager, ColorRepository $repository, $id = null)

    {
        $colors = $repository->findAll();

        if (!empty($id)):
            $color = $repository->find($id);
        else:
            $color = new Color();
        endif;

        $form = $this->createForm(ColorType::class, $color);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            //dd($color);

            $manager->persist($color);
            $manager->flush();

            if (!empty($id)):
                $this->addFlash('success', 'couleur modifiée');
            else:
                $this->addFlash('success', 'couleur ajoutée');
            endif;

            return $this->redirectToRoute('color');

        endif;

        //dd($form);
        return $this->render('admin/color.html.twig', [
            'form' => $form->createView(),
            'colors' => $colors,
            'title' => 'Gestion des couleurs'

        ]);
    }

    /**
     * @Route("/deleteColor/{id}" ,name="deleteColor")
     */
    public function deleteColor(Color $color, EntityManagerInterface $manager)
    {
        $manager->remove($color);
        $manager->flush();
        $this->addFlash(
            'success',
            'couleur supprimée'
        );

        return $this->redirectToRoute('color');
    }


}

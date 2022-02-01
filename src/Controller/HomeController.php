<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\CategoryType;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\PromoRepository;
use App\Repository\SubCategoryRepository;
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
     * @Route("/removeCart/{id}", name="removeCart")
     *
     */
    public function removeCart(PanierService $panierService, $id)
    {
        $panierService->remove($id);

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/deleteCart/{id}", name="deleteCart")
     *
     */
    public function deleteCart(PanierService $panierService, $id)
    {
        $panierService->delete($id);

        return $this->redirectToRoute('cart');
    }


    /**
     * @Route("/cart", name="cart")
     *
     */
    public function cart(PanierService $panierService)
    {

        $affiche=true;
        $items = $panierService->fullCart();
        $total = $panierService->Total();

        return $this->render('home/cart.html.twig', [
            'items' => $items,
            'total' => $total,
            'titre' => 'Mon panier',
            'affiche'=>$affiche

        ]);
    }

    /**
     * @Route("/destroyCart", name="destroyCart")
     *
     */
    public function destroyCart(Request $request, PanierService $panierService)
    {

        //$request->cookies->set('panierDestroy', $panierService->fullCart());

        $panierService->destroy();

        return $this->redirectToRoute('home');

    }


    /**
     * @Route("/filterProduct/{param}", name="filterProduct")
     * @Route("/filterValidate", name="filterValidate")
     */
    public function filterProduct(ProductRepository $productRepository, Request $request, SubCategoryRepository $categoryRepository, CategoryRepository $subcategoryRepository, $param = null)
    {


        $categories = $categoryRepository->findAll();
        $affichage = 'categorie';
        $products = $productRepository->findBy(['gender' => $param], ['price' => 'ASC']);
        //dd($request->request);

        $sousCategories = "";
        $prixmax = 0;

        if (!empty($_POST)):
            $param = $request->request->get('section');
            $prixmax = $request->request->get('prixmax');
            // etape 1 txt
            if (isset($_POST['cat']) && $_POST['cat'] !== 'all'):

                $categories = $categoryRepository->find($request->request->get('cat'));
                //dd($cat);
                $affichage = 'sousCategorie';
                $sousCategories = $subcategoryRepository->findBy(['subCategory' => $categories]);
                //dd($sousCategories);
            endif;
            // etape 2 txt
            if (isset($_POST['subCat']) && $_POST['subCat'] !== 'all'):
                $subCat = $subcategoryRepository->find($request->request->get('subCat'));

                $affichage = 'sousCategorie';
                $sousCategories = $subcategoryRepository->findBy(['subCategory' => $subCat->getSubCategory()]);
            endif;

            //dd($_POST);


            // user n'a rien saisi
            if (isset($_POST['cat']) && $_POST['cat'] == 'all' && $_POST['prixmax'] == '0'):

                $products = $productRepository->findBy(['gender' => $param], ['price' => 'ASC']);

            endif;

            // user a séléctionnez la catégorie mais pas de prix
            if (isset($_POST['cat']) && $_POST['cat'] !== 'all' && $_POST['prixmax'] == '0'):
                //dd($cat);

                $products = $productRepository->findByCategory($param, $categories);

            endif;

            // user a selectionné le prix mais pas la catégorie
            if (isset($_POST['cat']) && $_POST['cat'] == 'all' && $_POST['prixmax'] !== '0'):

                $products = $productRepository->findByPrice($prixmax, $param);

            endif;

            //user a selectionné le prix et la categorie
            if (isset($_POST['cat']) && $_POST['cat'] !== 'all' && $_POST['prixmax'] !== '0'):
                //dd($prixmax);
                $products = $productRepository->findByCategoryPrice($param, $categories, $prixmax);
                //dd($products);
            endif;

            // user a séléctionnez la souscatégorie mais pas de prix
            if (isset($_POST['subCat']) && $_POST['subCat'] !== 'all' && $_POST['prixmax'] == '0'):
                $products = $productRepository->findBy(['category' => $subCat]);
            endif;
            // user a selectionné le prix mais pas la souscatégorie
            if (isset($_POST['subCat']) && $_POST['subCat'] == 'all' && $_POST['prixmax'] !== '0'):
                $products = $productRepository->findByPrice($prixmax, $param);
            endif;
            // user a selectionné le prix et la souscategorie
            if (isset($_POST['subCat']) && $_POST['subCat'] !== 'all' && $_POST['prixmax'] !== '0'):
                $products = $productRepository->findByPriceSubCategory($prixmax, $param, $subCat);
            endif;

            return $this->render('home/filterProduct.html.twig', [
                'products' => $products,
                'categories' => $categories,
                'affichage' => $affichage,
                'param' => $param,
                'sousCategories' => $sousCategories,
                'prixmax' => $prixmax
            ]);

        endif;


        return $this->render('home/filterProduct.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'affichage' => $affichage,
            'param' => $param,
            'sousCategories' => $sousCategories,
            'prixmax' => $prixmax
        ]);
    }

    /**
     * @Route("/verifPromo", name="verifPromo")
     *
     */
    public function verifPromo(Request $request, PromoRepository $promoRepository, PanierService $panierService)
    {

        $affiche=true;
        if (!empty($_POST)):
            $code = $request->request->get('code');
            $promo = $promoRepository->findOneBy(['code' => $code]);

            if ($promo):
                $start = $promo->getStartDate();
                $end = $promo->getEndDate();
                //   dd($start < new \DateTime());
                if ($start <= new \DateTime() && $end >= new \DateTime()):
                    $remise = 0;
                    $panier = $panierService->fullCart();


                    if ($promo->getSection() !== null):
                        if ($promo->getType() == 0):
                            foreach ($panier as $item):
                                if ($promo->getSection() == $item['product']->getGender()):
                                    $remise += $item['quantity']*$item['product']->getPrice() * $promo->getValue() / 100;
                                endif;
                            endforeach;
                        else:
                            $remise = $promo->getValue();
                        endif;
                    endif;
                    if ($promo->getCategory() !== null):
                        if ($promo->getType() == 0):
                            foreach ($panier as $item):
                                if ($promo->getCategory() == $item['product']->getCategory()):
                                    $remise += $item['quantity']*$item['product']->getPrice() * $promo->getValue() / 100;
                                endif;
                            endforeach;
                        else:
                            $remise = $promo->getValue();
                        endif;
                    endif;
                    if ($promo->getSubCategory() !== null):
                        if ($promo->getType() == 0):
                            foreach ($panier as $item):
                                if ($promo->getSubCategory() == $item['product']->getCategory()->getSubCategory()):
                                    $remise += $item['quantity']*$item['product']->getPrice() * $promo->getValue() / 100;
                                endif;
                            endforeach;
                        else:
                            $remise = $promo->getValue();
                        endif;
                    endif;
                    if ($remise!==0):
                    $affiche=false;
                    $this->addFlash('success', 'votre remise est appliquée');
                        endif;
                        $total=$panierService->Total();
                        $totalRemise=$total-$remise;
                    return $this->render('home/cart.html.twig', [
                        'remise'=>$remise,
                        'affiche'=>$affiche,
                        'items'=>$panier,
                        'total'=>$total,
                        'totalRemise'=>$totalRemise

                    ]);


                else:
                    $this->addFlash('danger', 'Code promo invalide');
                    return $this->redirectToRoute('cart');
                endif;


            else:
                $this->addFlash('danger', 'Code promo invalide');
                return $this->redirectToRoute('cart');
            endif;

        endif;


        return $this->redirectToRoute('cart');
    }


}

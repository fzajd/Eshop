<?php

namespace App\Service\Panier;


use App\Repository\ColorRepository;
use App\Repository\ProductRepository;
use App\Repository\SizeRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PanierService
{

    public $session;
    public $productRepository;
    public $sizeRepository;
    public $colorRepository;

    public function __construct(SessionInterface $session, ProductRepository $productRepository, ColorRepository $colorRepository, SizeRepository $sizeRepository)
    {
        $this->session = $session;
        $this->productRepository = $productRepository;
        $this->sizeRepository = $sizeRepository;
        $this->colorRepository = $colorRepository;

    }

    public function add(int $key)
    {
        $panier = $this->session->get('panier', []);

            $panier[$key]['quantity'] += 1;
            $this->session->set('panier', $panier);

            //dd($panier);

        $this->session->set('panier', $panier);

    }

    public function remove(int $key)
    {

        $panier = $this->session->get('panier', []);

        if ($panier[$key]['quantity'] == 1):
            unset($panier[$key]);
        else:
            $panier[$key]['quantity']--;
        endif;

        $this->session->set('panier', $panier);


    }

    public function delete(int $key)
    {
        $panier = $this->session->get('panier', []);

        unset($panier[$key]);
        //ou $this->>session->remove($panier[$id])

        $this->session->set('panier', $panier);
    }

    public function fullCart()
    {
        $panier = $this->session->get('panier', []);


        return $panier;

    }

    public function Total()
    {
        $panier = $this->fullCart();
        $total = 0;

        foreach ($panier as $key => $value):
            $total += $value['product']->getPrice() * $value['quantity'];

        endforeach;

        return $total;

    }

    public function destroy()
    {
        $this->session->remove('panier');


    }

    ///////////////////////////////////////   PRE-PANIER    ///////////////////////////////////////////////

    public function addSize(int $id, int $size)
    {

        $temp = $this->session->get('temp', []);


        $temp[$id]['size'] = $size;

        $this->session->set('temp', $temp);


    }

    public function addColor(int $id, int $color)
    {

        $temp = $this->session->get('temp', []);

        // $temp[$id]['color']=[];
        $temp[$id]['color'] = $color;
        //dd($temp);
        $this->session->set('temp', $temp);


    }

    public function eraseTemp()
    {
        $this->session->remove('temp');

    }


    public function addTempCart()
    {
        $temp = $this->session->get('temp', []);
        $panier = $this->session->get('panier', []);


        // $this->eraseTemp();
        // $this->destroy();
        // die('coucou');

        $cle = -1;

        if (!empty($panier)):
            if (!empty($temp) && isset($temp[key($temp)]['color']) && isset($temp[key($temp)]['size'])):
            foreach ($panier as $key => $item):

                if ($item['product']->getId() == key($temp) && $item['color']->getId() == $temp[key($temp)]['color'] && $item['size']->getId() == $temp[key($temp)]['size']):
                    $cle = $key;

                endif;
            endforeach;

            if ($cle !== -1):
                $panier[$key]['quantity'] += 1;
                $this->session->set('panier', $panier);
                $this->eraseTemp();
            //dd($panier);
            else:

                if (!empty($temp) && isset($temp[key($temp)]['color']) && isset($temp[key($temp)]['size'])):

                    $detail = ['product' => $this->productRepository->find(key($temp)), 'color' => $this->colorRepository->find($temp[key($temp)]['color']), 'size' => $this->sizeRepository->find($temp[key($temp)]['size']), 'quantity' => 1];
                    $panier[] = $detail;
                    $this->session->set('panier', $panier);
                    $this->eraseTemp();
                else:
                    //dd('coucou');
                    if (empty($temp)):
                        $message= 'Vous devez selectionnez la taille et la couleur de l\'article';
                        endif;
                    if (!isset($temp[key($temp)]['color'])):
                        $message= 'Vous devez selectionnez  la couleur de l\'article';
                    endif;
                    if (!isset($temp[key($temp)]['size'])):
                        $message= 'Vous devez selectionnez la taille  de l\'article';
                    endif;
                    return $message;

                endif;

            endif;

            else:
                if (empty($temp)):
                    $message= 'Vous devez selectionnez la taille et la couleur de l\'article';
                endif;
                if (!isset($temp[key($temp)]['color'])):
                    $message= 'Vous devez selectionnez  la couleur de l\'article';
                endif;
                if (!isset($temp[key($temp)]['size'])):
                    $message= 'Vous devez selectionnez la taille  de l\'article';
                endif;
                return $message;

            endif;


        else:


            if (!empty($temp) && isset($temp[key($temp)]['color']) && isset($temp[key($temp)]['size'])):

                $detail = ['product' => $this->productRepository->find(key($temp)), 'color' => $this->colorRepository->find($temp[key($temp)]['color']), 'size' => $this->sizeRepository->find($temp[key($temp)]['size']), 'quantity' => 1];
                $panier[] = $detail;
                $this->session->set('panier', $panier);
                $this->eraseTemp();
            else:
                if (empty($temp)):
                    $message= 'Vous devez selectionnez la taille et la couleur de l\'article';
                endif;
                if (!isset($temp[key($temp)]['color'])):
                    $message= 'Vous devez selectionnez  la couleur de l\'article';
                endif;
                if (!isset($temp[key($temp)]['size'])):
                    $message= 'Vous devez selectionnez la taille  de l\'article';
                endif;
                return $message;

            endif;

        endif;
        //dd($panier);

    }


}
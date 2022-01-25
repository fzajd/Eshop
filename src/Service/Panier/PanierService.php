<?php

namespace App\Service\Panier;


use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PanierService
{

    public $session;
    public $productRepository;

    public function __construct(SessionInterface $session, ProductRepository $productRepository)
    {
        $this->session = $session;
        $this->productRepository = $productRepository;

    }

    public function add(int $id)
    {
        $panier = $this->session->get('panier', []);

        if (empty($panier[$id])):
            $panier[$id] = 1;
        else:
            $panier[$id]++;
        endif;

        $this->session->set('panier', $panier);

    }

    public function remove(int $id)
    {

        $panier = $this->session->get('panier', []);

        if ($panier[$id] == 1):
            unset($panier[$id]);
        else:
            $panier[$id]--;
        endif;

        $this->session->set('panier', $panier);


    }

    public function delete(int $id)
    {
        $panier = $this->session->get('panier', []);

        unset($panier[$id]);
        //ou $this->>session->remove($panier[$id])

        $this->session->set('panier', $panier);
    }

    public function fullCart()
    {
        $panier = $this->session->get('panier', []);

        $panierDetail = [];

        foreach ($panier as $id => $quantity):

            $panierDetail[] = [
                'product' => $this->productRepository->find($id),
                'quantity' => $quantity
            ];

        endforeach;
        return $panierDetail;

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


}
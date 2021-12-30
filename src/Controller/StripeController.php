<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Classe\Cart;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    /**
     * @Route("/commande/create-session", name="stripe_create_session")
     */
    public function index(Cart $cart)
    {
        $products_for_stripe = [];
        $YOUR_DOMAIN = 'http://127.0.0.1:8000';

        foreach ($cart->getFull() as $product) {
            $products_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product['product']->getPrice(),
                    'product_data' => [
                        'name' => $product['product']->getName(),
                        'images' => [$YOUR_DOMAIN . "/uploads/" . $product['product']->getIllustration()],
                    ],
                ],
                'quantity' => $product['quantity'],
            ];
        }

        Stripe::setApiKey('sk_test_51KCQwzGL2Kvpsr1r07VcHuT5KtQTpEK1XiyDh8q6BqRIH01INuQgJ6MiZoYBsQPJao13kvcJrmORQZgAVx1nZPKc00HGF5Vdd9');

        $checkout_session = Session::create([
            'line_items' => [
                $products_for_stripe
            ],
            'payment_method_types' => [
                'card',
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/success.html',
            'cancel_url' => $YOUR_DOMAIN . '/cancel.html',
        ]);

        return $this->redirect($checkout_session->url);
    }
}

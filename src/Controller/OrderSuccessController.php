<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_success")
     */
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneBystripeSessionId($stripeSessionId);

        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }
        if ($order->getState() == 0) {
            //vider la session "cart"
            $cart->remove();
            // Modifier le status State de notre commande en mettant 1
            $order->setState(1);
            $this->entityManager->flush();
            // Envoyer un email à notre client pour lui confirmer sa commande
            $mail = new Mail();
            $content = "Bonjour " . $order->getUser()->getFirstname() . "<br/><br/>Merci pour votre commande.<br/><br/> Lorem ipsum dolor, sit amet consectetur adipisicing elit. Consectetur quae ipsa modi corrupti ipsam id laborum! Qui laboriosam commodi quo voluptates dolore voluptatibus. Quibusdam dolorem, sint quidem error tempora nihil";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre Commande - La Boutique Française est bien validée.', $content);
        }
        // Afficher les quelques informations de la commande de l'utilisateur

        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}

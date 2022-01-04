<?php

namespace App\Controller;

use DateTime;
use App\Classe\Mail;
use App\Entity\User;
use App\Entity\ResetPassword;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/mot-de-passe-oublie", name="reset_password")
     */
    public function index(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if ($request->get('email')) {
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));

            if ($user) {
                // 1 : Enregistrer en base la demande de reset_password avec user,token, createdAt
                $resetPassword = new ResetPassword();
                $resetPassword->setUser($user)
                    ->setToken(uniqid())
                    ->setCreatedAt(new \DateTime());

                $this->entityManager->persist($resetPassword);
                $this->entityManager->flush();

                //2: Envoyer un email à l'utilisateur avec un lien lui permettant de mettre à jour son mot de passe
                $url = $this->generateUrl('update_password', [
                    'token' => $resetPassword->getToken()
                ]);
                $content =
                    "Bonjour " . $user->getFirstName() . ",<br><br> Afin de réinitialiser votre mot de passe, merci de bien vouloir cliquer sur le lien suivant :<br><br>";
                $content .= "<a href='" . $url . "'>Réinitialiser votre mot de passe</a> <br>Ce lien est valide <strong>30 minutes.</strong>";


                $mail = new Mail();
                $mail->send($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName(), 'Réinialiser votre mot de passe sur La Boutique Française', $content);
                $this->addFlash("notice", "Vous allez recevoir dans quelques secondes un mail avec la procédure pour réinitialiser votre mot de passe");
            } else {
                $this->addFlash("notice", "Cette adresse email est inconnue.");
            }
        }

        return $this->render('reset_password/index.html.twig');
    }
    /**
     * @Route("/modifier-mot-de-passe/{token}", name="update_password")
     */
    public function update($token, Request $request, UserPasswordEncoderInterface $encoder)
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);

        if (!$reset_password) {
            return $this->redirectToRoute('reset_password');
        }
        //Vérifier si le createdAt = now - 3h
        $now = new \DateTime();

        if ($now > $reset_password->getCreatedAt()->modify('+ 3 hour')) {
            $this->addFlash("notice", "Votre demande de mot de passe a expiré. Merci de la renouveler");
            return $this->redirectToRoute('reset_password');
        }


        //Encodage des mots de passe
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newPassword = $form->get('new_password')->getData();

            $reset_password->getUser()->setPassword(
                $encoder->encodePassword(
                    $reset_password->getUser(),
                    $newPassword
                )
            );
            //Flush en base de donnée
            $this->entityManager->flush();
            //Redirection de l'utilistaeur vers la page de connexion
            $this->addFlash('notice', 'Votre mot de passe a bien été mis à jour !');
            return $this->redirectToRoute('app_login');
        }
        //rendre une vue avec mot de passe et confirmez votre mot de passe
        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView()
        ]);
    }
}

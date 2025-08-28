<?php
namespace App\Controller;

use App\Entity\Pret;
use App\Form\PretType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PretController extends AbstractController
{
    #[Route('/pret/nouveau', name: 'pret_nouveau')]
    public function nouveauPret(Request $request, EntityManagerInterface $em)
    {
        $pret = new Pret();
        $pret->setDateDemande(new \DateTime());
        $pret->setUser($this->getUser());
        $pret->setStatut('En attente'); 
        $form = $this->createForm(PretType::class, $pret);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($pret);
            $em->flush();
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('pret/nouveau.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

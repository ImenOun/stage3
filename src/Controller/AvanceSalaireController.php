<?php
namespace App\Controller;
use App\Entity\User;
use App\Entity\AvanceSalaire;
use App\Form\AvanceSalaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AvanceSalaireController extends AbstractController
{
    #[Route('/avance/nouveau', name: 'avance_nouveau')]
    public function nouveau(Request $request, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        $avance = new AvanceSalaire();
        $avance->setDateDemande(new \DateTime());
        $avance->setUser($this->getUser());
        $avance->setStatut('En attente'); 
        $form = $this->createForm(AvanceSalaireType::class, $avance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($avance);
            $em->flush();
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('avance/nouveau.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
    private function reinitialiserAvances(EntityManagerInterface $em, User $user)
    {
        $today = new \DateTime();

        foreach ($user->getDemandes() as $avance) {
            if ($avance instanceof AvanceSalaire && $avance->getStatut() === 'Validée') {
                $moisPrelevement = $avance->getMoisPrelevement();
                if ($moisPrelevement instanceof \DateTimeInterface) {
                    // Comparer uniquement mois et année
                    if ($moisPrelevement->format('Y-m') <= $today->format('Y-m')) {
                        $user->setSalaireNet($user->getSalaireNetInitial());
                        $avance->setStatut('Clôturée');
                        $em->persist($user);
                        $em->persist($avance);
                    }
                }
            }
        }

        $em->flush();
    }
}

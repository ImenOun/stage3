<?php
// src/Controller/ProfileController.php

namespace App\Controller;

use App\Entity\AvanceSalaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]    
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $today = new \DateTime();   // date réelle
        //$now = (new \DateTime())->modify('+1 month');  // date simulée pour le test

        // --- Traitement pour réinitialiser salaire après une avance ---
        foreach ($user->getDemandes() as $avance) {
            if ($avance instanceof AvanceSalaire && $avance->getStatut() === 'Validée') {
                $moisPrelevement = $avance->getMoisPrelevement();

                if ($moisPrelevement instanceof \DateTimeInterface) {
                    // Comparer uniquement le mois et l'année
                    if ($moisPrelevement <= $today) {
                        // Réinitialiser salaire net
                        $user->setSalaireNet($user->getSalaireNetInitial());
                        $avance->setStatut("Clôturée");
                    }
                }
            }
        }
        $em->flush();

        // --- Statistiques uniquement pour RH ---
        $stats = null;
        foreach ($user->getPermissions() as $permission) {
            if ($permission->getName() === 'Valider Demande') {
                // calculer les stats globales
                $totalDemandes = $em->getRepository(\App\Entity\Demande::class)->count([]);
                $attente = $em->getRepository(\App\Entity\Demande::class)->count(['statut' => 'En attente']);
                $totalAvances = $em->getRepository(\App\Entity\AvanceSalaire::class)
                ->createQueryBuilder('a')
                ->select('COALESCE(SUM(a.montant), 0)')
                ->where('a.statut = :statut')
                ->setParameter('statut', 'Validée')
                ->getQuery()
                ->getSingleScalarResult();

       
                $totalPrets = $em->getRepository(\App\Entity\Pret::class)
                ->createQueryBuilder('p')
                ->select('COALESCE(SUM(p.montant), 0)')
                ->where('p.statut = :statut')
                ->setParameter('statut', 'Validé')
                ->getQuery()
                ->getSingleScalarResult();
                $attestations = $em->getRepository(\App\Entity\Demande::class)
                                   ->createQueryBuilder('d')
                                   ->select('COUNT(d.idDem)')
                                   ->where('d.type IN (:types)')
                                   ->setParameter('types', ['Attestation de Travail', 'Attestation de Salaire'])
                                   ->getQuery()
                                   ->getSingleScalarResult();

                $stats = [
                    'totalDemandes' => $totalDemandes,
                    'attente' => $attente,
                    'totalAvances' => $totalAvances ?? 0,
                    'totalPrets' => $totalPrets ?? 0,
                    'attestations' => $attestations,
                ];
            }
        }

        // --- Stats mensuelles pour les courbes
$conn = $em->getConnection();
$sql = "
    SELECT 
        COALESCE(p.mois, a.mois) as mois,
        COALESCE(p.nb, 0) as nb_prets,
        COALESCE(a.nb, 0) as nb_avances
    FROM (
        SELECT DATE_FORMAT(date_demande, '%Y-%m') as mois, COUNT(*) as nb
        FROM pret
        GROUP BY mois
    ) p
    LEFT JOIN (
        SELECT DATE_FORMAT(date_demande, '%Y-%m') as mois, COUNT(*) as nb
        FROM avance_salaire
        GROUP BY mois
    ) a ON p.mois = a.mois
    UNION
    SELECT 
        COALESCE(p.mois, a.mois) as mois,
        COALESCE(p.nb, 0) as nb_prets,
        COALESCE(a.nb, 0) as nb_avances
    FROM (
        SELECT DATE_FORMAT(date_demande, '%Y-%m') as mois, COUNT(*) as nb
        FROM pret
        GROUP BY mois
    ) p
    RIGHT JOIN (
        SELECT DATE_FORMAT(date_demande, '%Y-%m') as mois, COUNT(*) as nb
        FROM avance_salaire
        GROUP BY mois
    ) a ON p.mois = a.mois
    ORDER BY mois ASC
";
$monthlyStats = $conn->fetchAllAssociative($sql);

// On force tous les mois même ceux sans données
$allMonths = [];
$currentMonth = new \DateTime('2025-01-01');
$endMonth = new \DateTime();
while ($currentMonth <= $endMonth) {
    $allMonths[] = $currentMonth->format('Y-m');
    $currentMonth->modify('+1 month');
}

// Construire tableaux pour Twig
$pretsMensuelles = [];
$avancesMensuelles = [];
foreach ($allMonths as $month) {
    $found = false;
    foreach ($monthlyStats as $stat) {
        if ($stat['mois'] === $month) {
            $pretsMensuelles[] = ['mois' => $month, 'nb' => $stat['nb_prets']];
            $avancesMensuelles[] = ['mois' => $month, 'nb' => $stat['nb_avances']];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $pretsMensuelles[] = ['mois' => $month, 'nb' => 0];
        $avancesMensuelles[] = ['mois' => $month, 'nb' => 0];
    }
}

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'stats' => $stats,
            'pretsMensuelles' => $pretsMensuelles,
            'avancesMensuelles' => $avancesMensuelles,
        ]);
    }
}
<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Entity\HistoriqueDemande;
use App\Entity\Pret;
use App\Entity\AvanceSalaire;
use App\Form\DemandeType;
use App\Repository\DemandeRepository;
use App\Repository\PretRepository;
use App\Repository\AvanceSalaireRepository;
use App\Repository\HistoriqueDemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\SecurityBundle\Security;  
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpWord\TemplateProcessor;
use Dompdf\Dompdf;
use Dompdf\Options;

use Symfony\Component\HttpFoundation\RedirectResponse;


#[Route('/demande')]
final class DemandeController extends AbstractController
{

   #[Route(name: 'app_demande_index', methods: ['GET'])]
    public function index(DemandeRepository $demandeRepository,PretRepository $pretRepository,AvanceSalaireRepository $avanceSalaireRepository,PaginatorInterface $paginator,Request $request): Response 
    {
        $demandesQuery = $demandeRepository->createQueryBuilder('d')
        ->where('d.type IN (:types)')
        ->setParameter('types', ['Attestation de Travail', 'Attestation de Salaire'])
        ->orderBy('d.date_soumission', 'DESC')
        ->getQuery();

    

   $paginatedDemandes = $paginator->paginate(
    $demandesQuery,
    $request->query->getInt('page', 1),
    7
);



    return $this->render('demande/index.html.twig', [
        'demandes' => $paginatedDemandes,
        
    ]);
    }

    #[Route('/liste_pret',name: 'app_listePret', methods: ['GET'])]
    public function listePret(PretRepository $pretRepository,PaginatorInterface $paginator,Request $request): Response 
    {
       

    $pretsQuery = $pretRepository->createQueryBuilder('p')
        ->orderBy('p.date_demande', 'DESC')
        ->getQuery();
    


$paginatedPrets = $paginator->paginate(
    $pretsQuery,
   $request->query->getInt('page', 1), 
    7
);


    return $this->render('demande/listePret.html.twig', [
        'prets' => $paginatedPrets,
        
    ]);
    }

 #[Route('/liste_avance',name: 'app_listeAvance', methods: ['GET'])]
    public function listeAvance(AvanceSalaireRepository $avanceSalaireRepository,PaginatorInterface $paginator,Request $request): Response 
    {
       

    $avanceSalaireQuery = $avanceSalaireRepository->createQueryBuilder('a')
        ->orderBy('a.date_demande', 'DESC')
        ->getQuery();

 
$paginatedAvances = $paginator->paginate(
    $avanceSalaireQuery,
    $request->query->getInt('page', 1),
    6
);

    return $this->render('demande/listeAvance.html.twig', [
        'avances' => $paginatedAvances,
        
    ]);
    }

    
    #[Route('/mes-demandes', name: 'app_mes_demandes', methods: ['GET'])]
public function mesDemandes(Request $request, 
    DemandeRepository $demandeRepository,
    PretRepository $pretRepository,
    AvanceSalaireRepository $avanceSalaireRepository,
    Security $security,
    PaginatorInterface $paginator): Response
{
    $user = $security->getUser();

    // Paginer les demandes
    $demandesQuery = $demandeRepository->createQueryBuilder('d')
        ->where('d.user = :user')
        ->setParameter('user', $user);

    $demandes = $paginator->paginate(
        $demandesQuery,
        $request->query->getInt('page', 1),
        5 // nombre d'éléments par page
    );

    // Paginer les prêts
    $pretsQuery = $pretRepository->createQueryBuilder('p')
        ->where('p.user = :user')
        ->setParameter('user', $user);

    $prets = $paginator->paginate(
        $pretsQuery,
        $request->query->getInt('page', 1),
        5
    );

    // Paginer les avances
    $avancesQuery = $avanceSalaireRepository->createQueryBuilder('a')
        ->where('a.user = :user')
        ->setParameter('user', $user);

    $avances = $paginator->paginate(
        $avancesQuery,
        $request->query->getInt('page', 1),
        5
    );

    return $this->render('demande/mesdemandes.html.twig', [
        'demandes' => $demandes,
        'prets' => $prets,
        'avances' => $avances,
    ]);
}

    #[Route('/new', name: 'app_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, Security $security): Response
        {
            $demande = new Demande();

            $form = $this->createForm(DemandeType::class, $demande);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user = $security->getUser();
                $demande->setUser($user);
                $demande->setStatut('En attente');
                $demande->setDateSoumission(new \DateTime());

                $em->persist($demande);
                $em->flush();

                return $this->redirectToRoute('app_mes_demandes');
            }

            return $this->render('demande/new.html.twig', [
                'form' => $form->createView(),
            ]);
        }
    
    #[Route('/{idDem}', name: 'app_demande_delete', methods: ['POST'])]
    public function delete(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$demande->getIdDem(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($demande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_mes_demandes', [], Response::HTTP_SEE_OTHER);
    }
   #[Route('/{idDem}', name: 'app_demande_show', requirements: ['idDem' => '\d+'])]
    public function show(int $idDem, DemandeRepository $demandeRepository): Response
    {
        $demande = $demandeRepository->find($idDem);

        if (!$demande) {
            throw $this->createNotFoundException('Demande non trouvée.');
        }

        return $this->render('demande/show.html.twig', [
            'demande' => $demande,
        ]);
    }


    #[Route('/{idDem}/edit', name: 'app_demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_demande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('demande/edit.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

      
   
   #[Route('/admin/demande/{idDem}/valider', name: 'admin_demande_valider')]
    public function validerDemande(Demande $demande, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user->getPermissions()->exists(fn($key, $perm) => $perm->getName() === 'Valider Demande')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        $demande->setStatut('Validée');

        $historique = new HistoriqueDemande();
        $historique->setDemande($demande);
        $historique->setUser($user);
        $historique->setAction('Validée ✅');
        $historique->setDate_action(new \DateTimeImmutable());
        $historique->setCommentaire('Demande validée ');

        $entityManager->persist($historique);
        $entityManager->flush();

        return $this->redirectToRoute('app_demande_index');
    }


   #[Route('/admin/demande/{idDem}/rejeter', name: 'admin_demande_rejeter')]
    public function rejeterDemande(Demande $demande, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user->getPermissions()->exists(fn($key, $perm) => $perm->getName() === 'Valider Demande')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        $demande->setStatut('Rejetée');

        $historique = new HistoriqueDemande();
        $historique->setDemande($demande);
        $historique->setUser($user);
        $historique->setAction('Rejetée ❌');
        $historique->setDate_action(new \DateTimeImmutable());
        $historique->setCommentaire('Demande rejetée ');

        $entityManager->persist($historique);
        $entityManager->flush();

        return $this->redirectToRoute('app_demande_index');
    }
     
    #[Route('/admin/demandes/historique', name: 'demandes_historique')]
    public function historiqueToutesLesDemandes(HistoriqueDemandeRepository $repo): Response
    {
        $historiques = $repo->findBy([], ['date_action' => 'DESC']);
        return $this->render('demande/historiqueDemande.html.twig', [
            'historiques' => $historiques,
        ]);
    }

    
    // Valider un prêt
#[Route('/admin/pret/{id}/valider', name: 'admin_pret_valider')]
public function validerPret(int $id, EntityManagerInterface $em): RedirectResponse
{
    $pret = $em->getRepository(Pret::class)->find($id);

    if (!$pret) {
        throw $this->createNotFoundException('Prêt non trouvé');
    }

    $pret->setStatut('Validé');
    $em->flush();

    return $this->redirectToRoute('app_listePret');
}

// Rejeter un prêt
#[Route('/admin/pret/{id}/rejeter', name: 'admin_pret_rejeter')]
public function rejeterPret(int $id, EntityManagerInterface $em): RedirectResponse
{
    $pret = $em->getRepository(Pret::class)->find($id);

    if (!$pret) {
        throw $this->createNotFoundException('Prêt non trouvé');
    }

    $pret->setStatut('Rejeté');
    $em->flush();

    return $this->redirectToRoute('app_listePret');
}



  // Valider un avance
#[Route('/admin/avance/{id}/valider', name: 'admin_avance_valider')]
public function validerAvance(int $id, EntityManagerInterface $em): RedirectResponse
{
    $avance = $em->getRepository(AvanceSalaire::class)->find($id);

    if (!$avance) {
        throw $this->createNotFoundException('Avance non trouvée');
    }

    $user = $avance->getUser();
    $montant = $avance->getMontant();

    if ($user) {
        // Sauvegarder le salaire initial si ce n’est pas déjà fait
        if ($user->getSalaireNetInitial() === null) {
            $user->setSalaireNetInitial($user->getSalaireNet());
        }

        // Déduire l’avance du salaire net
        $user->setSalaireNet($user->getSalaireNet() - $montant);

        // Définir la date du prélèvement (mois prochain)
        $moisProchain = (new \DateTime())->modify('first day of next month');
        $avance->setMoisPrelevement($moisProchain);
    }

    $avance->setStatut('Validée');
    $em->flush();

    $this->addFlash('success', 'Avance validée et déduite du salaire net.');

    return $this->redirectToRoute('app_listeAvance');
}



// Rejeter un avance
#[Route('/admin/avance/{id}/rejeter', name: 'admin_avance_rejeter')]
public function rejeterAvance(int $id, EntityManagerInterface $em): RedirectResponse
{
    $avance = $em->getRepository(AvanceSalaire::class)->find($id);

    if (!$avance) {
        throw $this->createNotFoundException('Avance non trouvé');
    }

    $avance->setStatut('Rejeté');
    $em->flush();

    return $this->redirectToRoute('app_listeAvance');
}



  

#[Route('/admin/demande/{idDem}/pdf', name: 'admin_demande_pdf')]
public function genererPDF(Demande $demande): Response
{
    if ($demande->getType() === 'Attestation de Travail') {
        $templatePath = $this->getParameter('kernel.project_dir') . '/public/templates_pdf/attestation_travail.docx';
    } elseif ($demande->getType() === 'Attestation de Salaire') {
        $templatePath = $this->getParameter('kernel.project_dir') . '/public/templates_pdf/attestation_salaire.docx';
    } else {
        throw $this->createNotFoundException("Type de demande inconnu");
    }

    // Charger le modèle Word
    $templateProcessor = new TemplateProcessor($templatePath);

    // Remplacer les variables du .docx
    $templateProcessor->setValue('nom', $demande->getUser()->getNom());
   
    $templateProcessor->setValue('date',  (new \DateTime())->format('d/m/Y'));
$templateProcessor->setValue('salaireBrute', $demande->getUser()->getSalaireBrute());
$templateProcessor->setValue('salaireNet', $demande->getUser()->getSalaireNet());
    // Sauvegarder en .docx temporaire
    $tempDocx = tempnam(sys_get_temp_dir(), 'attestation') . '.docx';
    $templateProcessor->saveAs($tempDocx);

    // Convertir en HTML pour PDF
    $phpWord = \PhpOffice\PhpWord\IOFactory::load($tempDocx);
    $htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
    ob_start();
    $htmlWriter->save('php://output');
    $htmlContent = ob_get_clean();

    // Générer le PDF
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($htmlContent);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return new Response(
        $dompdf->output(),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="attestation.pdf"',
        ]
    );
}

}

<?php

namespace App\Controller\Backoffice;

use App\Entity\Incident;
use App\Form\AssignIncidentType;
use App\Form\IncidentType;
use App\Repository\IncidentRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use function PHPUnit\Framework\throwException;

#[Route('/backoffice/incidents')]
class IncidentController extends AbstractController
{
    #[Route('/', name: 'app_backoffice_incident_index', methods: ['GET'])]
    public function index(IncidentRepository $incidentRepository): Response
    {

        $incidents = $this->isGranted('ROLE_ADMIN') ? $incidentRepository->findAll() : $this->getUser()->getIncidents();

        //$incident = $this->isGranted('ROLE_ADMIN') ? $incidentRepository->findAll() : $incidentRepository->findBy(['followedBy'=>$this->getUser()]);
        return $this->render('backoffice/incident/index.html.twig', [
            'incidents' => $incidents,
        ]);
    }
    #[Route('/{id}/assign', name:'app_backoffice_assign', methods: ['GET', 'POST'])]
    public function assign(Request $request, IncidentRepository $incidentRepository, Incident $incident, UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(AssignIncidentType::class, $incident, ['tech'=>$userRepository->findTech()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $incidentRepository->save($incident, true);

            // $this->addFlash() is equivalent to $request->getSession()->getFlashBag()->add()
            $this->addFlash(
                'success',
                "Your incident has been successfully assigned to ".$incident->getFollowedBy()->getEmail()
            );

            return $this->redirectToRoute('app_backoffice_incident_show', ['id'=>$incident->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('app/report.html.twig', [
            'incident' => $incident,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backoffice_incident_show', methods: ['GET'])]
    public function show(Incident $incident): Response
    {
        $this->checkPermission($incident);
        return $this->render('backoffice/incident/show.html.twig', [
            'incident' => $incident,
        ]);
    }

    #[Route('/{id}/process', name: 'app_backoffice_incident_process', methods: ['GET'])]
    public function markAsProcessed(Incident $incident, IncidentRepository $incidentRepository,StatusRepository $statusRepository): Response
    {
        $this->checkPermission($incident);
        if(!$incident->getProcessedAt()){
            $incident->setProcessedAt(new DateTimeImmutable('now',new \DateTimeZone('Europe/Paris')));
            $incident->setStatus($statusRepository->findOneBy(["normalized"=>"PROCESSING"]));
            $incidentRepository->save($incident,true);
        }else{
            $this->addFlash(
                'error',
                "Incident ".$incident->getReference(). "already processed."
            );
        }

        return $this->redirectToRoute('app_backoffice_incident_show', [
            'id' => $incident->getId()
        ]);
    }

    #[Route('/{id}/resolve', name: 'app_backoffice_incident_resolve', methods: ['GET'])]
    public function markAsResolved(Incident $incident, IncidentRepository $incidentRepository, StatusRepository $statusRepository): Response
    {
        $this->checkPermission($incident);
        if($incident->getProcessedAt() && !$incident->getResolveAt() && !$incident->getRejectedAt()){
            $incident->setResolveAt(new DateTimeImmutable('now',new \DateTimeZone('Europe/Paris')));
            $incident->setStatus($statusRepository->findOneBy(["normalized"=>"RESOLVED"]));
            $incidentRepository->save($incident,true);
        }else{
            $this->addFlash(
                'error',
                "Incident ".$incident->getReference(). "is not processed or is already resolved or is already rejected."
            );
        }



        return $this->redirectToRoute('app_backoffice_incident_show', [
            'id' => $incident->getId()
        ]);
    }


    #[Route('/{id}/reject', name: 'app_backoffice_incident_reject', methods: ['GET'])]
    public function markAsRejected(Incident $incident, IncidentRepository $incidentRepository, StatusRepository $statusRepository): Response
    {
        $this->checkPermission($incident);
        if(!$incident->getResolveAt() && !$incident->getRejectedAt()){
            $incident->setRejectedAt(new DateTimeImmutable('now',new \DateTimeZone('Europe/Paris')));
            $incident->setStatus($statusRepository->findOneBy(["normalized"=>"REJECTED"]));
            $incidentRepository->save($incident,true);
        }else{
            $this->addFlash(
                'error',
                "Incident ".$incident->getReference(). " is already resolved or is already rejected."
            );
        }



        return $this->redirectToRoute('app_backoffice_incident_show', [
            'id' => $incident->getId()
        ]);
    }

    private function checkPermission(Incident $incident)
    {
        if ($this->isGranted('ROLE_TECH') && $incident->getFollowedBy() !== $this->getUser())
        {
            throw $this->createNotFoundException("Incident not found");
        }

    }

}

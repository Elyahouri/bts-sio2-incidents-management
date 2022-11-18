<?php

namespace App\Controller\Backoffice;

use App\Entity\Incident;
use App\Form\IncidentType;
use App\Repository\IncidentRepository;
use App\Repository\StatusRepository;
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
        return $this->render('backoffice/incident/index.html.twig', [
            'incidents' => $incidentRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_backoffice_incident_show', methods: ['GET'])]
    public function show(Incident $incident): Response
    {

        return $this->render('backoffice/incident/show.html.twig', [
            'incident' => $incident,
        ]);
    }

    #[Route('/{id}/process', name: 'app_backoffice_incident_process', methods: ['GET'])]
    public function markAsProcessed(Incident $incident, IncidentRepository $incidentRepository,StatusRepository $statusRepository): Response
    {
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

}

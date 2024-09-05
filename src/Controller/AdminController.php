<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Repository\CalendarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AdminController extends AbstractController
{
    #[Route('/admin/event/status/{id}', name: 'admin_event_status', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function changeEventStatus(Calendar $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        $status = $request->request->get('status');
        $event->setStatus($status, $this->getUser());

        $entityManager->flush();

        return $this->redirectToRoute('admin_event_list');
    }

    #[Route('/admin/events', name: 'admin_event_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function listEvents(CalendarRepository $calendarRepository): Response
    {
        $events = $calendarRepository->findAll();

        return $this->render('admin/event_list.html.twig', [
            'events' => $events,
        ]);
    }
}

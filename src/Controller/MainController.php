<?php

namespace App\Controller;

use App\Repository\CalendarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main')]

    public function index(CalendarRepository $calendar): Response
    {
        $events = $calendar->findAll();
        $rdvs = [];
        foreach ($events as $event) {
            $rdvs[] = [
                'id' => $event->getId(),
                'start' => $event->getStart()->format('Y-m-d H:i:s'),
                'end' => $event->getEnd()->format('Y-m-d H:i:s'),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'backgroundcolor' => $event->getBackgroundColor(),
                'bordercolor' => $event->getBorderColor(),
                'textcolor' => $event->getTextColor(),
                'allDay' => $event->isAllDay(),
            ];
        }
        $data = json_encode($rdvs);
        return $this->render('main/index.html.twig', compact('data'));
    }
}

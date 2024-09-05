<?php

namespace App\Controller;

use App\Repository\CalendarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index(CalendarRepository $calendarRepository): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur a le rôle 'ROLE_ADMIN'
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $events = $calendarRepository->findAll(); // Tous les événements pour les admins
        } else {
            $events = $calendarRepository->findBy(['user' => $user]); // Événements de l'utilisateur
        }

        $rdvs = [];
        foreach ($events as $event) {
            $status = $event->getStatus() ?: 'en cours'; // Utiliser 'en cours' comme valeur par défaut si le statut est nul
            $statusColor = $this->getEventColor($status); // Couleur de fond
            $borderColor = $this->getBorderColor($status); // Couleur de bordure

            $rdvs[] = [
                'id' => $event->getId(),
                'start' => $event->getStart()->format('c'), // Format ISO 8601
                'end' => $event->getEnd() ? $event->getEnd()->format('c') : null,
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'backgroundColor' => $statusColor, // Utiliser couleur de fond
                'borderColor' => $borderColor, // Utiliser couleur de bordure
                'textColor' => '#fff', // Couleur du texte
                'allDay' => $event->isAllDay()
            ];
        }

        $data = json_encode($rdvs);
        return $this->render('main/index.html.twig', [
            'data' => $data
        ]);
    }

    private function getEventColor(string $status): string
    {
        switch ($status) {
            case 'accepté':
                return '#28a745'; // Vert
            case 'refusé':
                return '#dc3545'; // Rouge
            case 'en cours':
                return '#ffc107'; // Jaune
            default:
                return '#000'; // Couleur par défaut
        }
    }

    private function getBorderColor(string $status): string
    {
        switch ($status) {
            case 'accepté':
                return '#1e7e34'; // Vert foncé pour la bordure
            case 'refusé':
                return '#c82333'; // Rouge foncé pour la bordure
            case 'en cours':
                return '#e0a800'; // Jaune foncé pour la bordure
            default:
                return '#000'; // Couleur par défaut pour la bordure
        }
    }
}

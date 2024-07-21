<?php

namespace App\Controller;

use App\Entity\Calendar;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }

    #[Route('/api/{id}/edit', name: 'api_event_edit', methods: ["PUT"])]
    public function majEvent(?Calendar $calendar, Request $request, EntityManagerInterface $em): Response
    {
        $donnees = json_decode($request->getContent());

        if (
            isset($donnees->title) && !empty($donnees->title) &&
            isset($donnees->start) && !empty($donnees->start) &&
            isset($donnees->description) && !empty($donnees->description) &&
            isset($donnees->backgroundcolor) && !empty($donnees->backgroundcolor) &&
            isset($donnees->textcolor) && !empty($donnees->textcolor)
        ) {
            $code = 200;

            if (!$calendar) {
                $calendar = new Calendar();
                $code = 201;
            }

            $calendar->setTitle($donnees->title);
            $calendar->setDescription($donnees->description);
            $calendar->setStart(new DateTime($donnees->start));

            if (isset($donnees->allDay) && $donnees->allDay) {
                $calendar->setAllDay(true);
                if (isset($donnees->end) && !empty($donnees->end)) {
                    $calendar->setEnd(new DateTime($donnees->end));
                }
            } else {
                $calendar->setAllDay(false);
            }

            $calendar->setBackgroundColor($donnees->backgroundcolor);
            $calendar->setBorderColor($donnees->bordercolor ?? $donnees->backgroundcolor);
            $calendar->setTextColor($donnees->textcolor);

            $em->persist($calendar);
            $em->flush();

            return new Response('Ok', $code);
        } else {
            return new Response('Données incomplètes', 404);
        }
    }
}

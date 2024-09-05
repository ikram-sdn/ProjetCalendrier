<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Form\CalendarType;
use App\Repository\CalendarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/calendar')]
class CalendarController extends AbstractController
{
    #[Route('/', name: 'app_calendar_index', methods: ['GET'])]
    public function index(Request $request, CalendarRepository $calendarRepository): Response
    {
        $user = $this->getUser();
        $status = $request->query->get('status'); // Retrieve the status filter parameter

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            // Admins can filter by status
            if ($status) {
                $calendars = $calendarRepository->findBy(['status' => $status]);
            } else {
                $calendars = $calendarRepository->findAll(); // Retrieve all events for admins
            }
        } else {
            // Regular users can filter their own events, optionally by status
            if ($status) {
                $calendars = $calendarRepository->findBy(['user' => $user, 'status' => $status]);
            } else {
                $calendars = $calendarRepository->findBy(['user' => $user]); // Retrieve only the user's events
            }
        }

        return $this->render('calendar/index.html.twig', [
            'calendars' => $calendars,
        ]);
    }


    #[Route('/new', name: 'app_calendar_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $calendar = new Calendar();
        $calendar->setStatus('en cours'); // Par défaut pour tout utilisateur

        $user = $this->getUser();
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());

        // Créer le formulaire avec l'option is_admin
        $form = $this->createForm(CalendarType::class, $calendar, [
            'is_admin' => $isAdmin // Passer l'information au formulaire
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $start = $calendar->getStart();
            $end = $calendar->getEnd();

            if ($start && $end && $start >= $end) {
                $this->addFlash('error', 'La date de fin doit être supérieure à la date de début.');
            } else {
                $calendar->setUser($user); // Assigner l'utilisateur à l'événement
                $entityManager->persist($calendar);
                $entityManager->flush();

                return $this->redirectToRoute('app_calendar_index', [], Response::HTTP_SEE_OTHER);
            }
        } elseif ($form->isSubmitted()) {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('calendar/new.html.twig', [
            'calendar' => $calendar,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_calendar_show', methods: ['GET'])]
    public function show(Calendar $calendar): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur a le rôle 'ROLE_ADMIN' ou si l'utilisateur est le propriétaire de l'événement
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && $calendar->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cet événement.');
        }

        return $this->render('calendar/show.html.twig', [
            'calendar' => $calendar,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_calendar_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Calendar $calendar, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur a le droit d'éditer cet événement
        if ($calendar->getUser() !== $user && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas éditer cet événement.');
        }

        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());

        // Créer le formulaire avec les options is_admin et edit_mode
        $form = $this->createForm(CalendarType::class, $calendar, [
            'is_admin' => $isAdmin,
            'edit_mode' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($isAdmin) {
                // Traiter le statut et commentaire pour l'admin
                $status = $form->get('status')->getData();
                $commentaire = $form->get('commentaire')->getData();

                // Assurer que seul un statut valide est défini
                if (in_array($status, ['accepté', 'refusé', 'en cours'])) {
                    $calendar->setStatus($status);

                    // Ajouter le commentaire seulement si refusé ou accepté
                    if ($status === 'refusé' || $status === 'accepté') {
                        $calendar->setCommentaire($commentaire);
                    }
                }
            }

            // Enregistrer les changements dans la base de données
            $entityManager->flush();

            return $this->redirectToRoute('app_calendar_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('calendar/edit.html.twig', [
            'calendar' => $calendar,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_calendar_delete', methods: ['POST'])]
    public function delete(Request $request, Calendar $calendar, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $calendar->getId(), $request->request->get('_token'))) {
            $user = $this->getUser();

            // Vérifier que l'utilisateur a le droit de supprimer cet événement
            if (!in_array('ROLE_ADMIN', $user->getRoles()) && $calendar->getUser() !== $user) {
                throw $this->createAccessDeniedException('Vous n\'avez pas le droit de supprimer cet événement.');
            }

            $entityManager->remove($calendar);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_calendar_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/status', name: 'calendar_change_status', methods: ['POST'])]
    public function changeStatus(Request $request, Calendar $calendar, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérifier que l'utilisateur peut modifier cet événement
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && $calendar->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cet événement.');
        }

        $status = $request->request->get('status');

        if (in_array($status, ['accepté', 'refusé', 'en cours'])) {
            // Vérifier que le statut est valide pour modification
            if ($calendar->getStatus() === 'en cours' || in_array('ROLE_ADMIN', $user->getRoles())) {
                $calendar->setStatus($status);
                $entityManager->flush();

                $this->addFlash('success', 'Le statut de l\'événement a été mis à jour.');
            } else {
                $this->addFlash('error', 'Le statut ne peut pas être modifié.');
            }
        } else {
            $this->addFlash('error', 'Statut invalide.');
        }

        return $this->redirectToRoute('app_calendar_show', ['id' => $calendar->getId()]);
    }
}

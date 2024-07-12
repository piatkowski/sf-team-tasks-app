<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/team')]
class TeamController extends AbstractController
{
    #[Route('/', name: 'app_team_index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository): Response
    {
        return $this->render('team/index.html.twig', [
            'teams' => $teamRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_team_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $team->addMember($team->getLeader());
                $entityManager->persist($team);
                $entityManager->flush();
                return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
            } catch(UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'This user is already a leader. Choose different one.');
            }
        }

        return $this->render('team/new.html.twig', [
            'team' => $team,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_team_show', methods: ['GET'])]
    public function show(Team $team): Response
    {
        return $this->render('team/show.html.twig', [
            'team' => $team,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_team_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        $old_leader = $team->getLeader();

        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $team->removeMember($old_leader);
                $team->addMember($team->getLeader());
                $entityManager->flush();

                return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
            } catch(UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'This user is already a leader. Choose different one.');
            }
        }

        return $this->render('team/edit.html.twig', [
            'team' => $team,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_team_delete', methods: ['POST'])]
    public function delete(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$team->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($team);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/add_member', name: 'app_team_add_member', methods: ['POST'])]
    public function addMember(Request $request, Team $team, UserRepository $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('add_member'.$team->getId(), $request->getPayload()->getString('_token'))) {
            $new_member = $user->findOneBy([
                'id' => $request->get('user_id'),
            ]);
            if ($new_member) {
                $team->addMember($new_member);
                $entityManager->persist($team);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('app_team_show', ['id' => $team->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/remove_member', name: 'app_team_remove_member', methods: ['POST'])]
    public function removeMember(Request $request, Team $team, UserRepository $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('remove_member'.$team->getId(), $request->getPayload()->getString('_token'))) {
            $member = $user->findOneBy([
                'id' => $request->get('user_id'),
            ]);
            if ($member && $team->getLeader()->getId() !== $member->getId()) {
                $team->removeMember($member);
                $entityManager->persist($team);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('app_team_show', ['id' => $team->getId()], Response::HTTP_SEE_OTHER);
    }
}

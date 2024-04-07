<?php

namespace App\Controller;

use App\Entity\Issue;
use App\Form\IssueType;
use App\Repository\IssueRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    private IssueRepository $issueRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(IssueRepository $issueRepository, EntityManagerInterface $entityManager)
    {
        $this->issueRepository = $issueRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'Home')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig', [
                'issues' => $this->issueRepository->findAll(),
            ]
        );
    }

    #[Route('/issues', name: 'Issues')]
    public function issues(): Response
    {
        return $this->render(
            'issue/index.twig',
            ['issues' => $this->issueRepository->findAll()]
        );
    }

    #[Route('/issue/create', name: 'Create')]
    public function create(Request $request): Response
    {
        $issue = new Issue();

        $form = $this->createForm(IssueType::class, $issue);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($issue);
            $this->entityManager->flush();

            $this->addFlash('notify', "Issue Inserted Successfully");

            return $this->redirectToRoute("Issues");
        }

        return $this->render(
            'issue/create.twig',
            ['form' => $form->createView(),]
        );
    }

    #[Route('/issue/edit/{id}', name: 'Edit')]
    public function edit(Request $request, int $id): Response
    {
        $issue = $this->issueRepository->find($id);

        $form = $this->createForm(IssueType::class, $issue);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $issue->setUpdatedAt(new DateTimeImmutable());

            $this->entityManager->persist($issue);
            $this->entityManager->flush();

            $this->addFlash('notify', "Issue Updated Successfully");

            return $this->redirectToRoute("Issues");
        }
        if ($issue) {
            return $this->render('issue/edit.twig', [
                    'form' => $form->createView(),
                    'id' => $issue->getId(),
                ]
            );
        } else {
            throw $this->createNotFoundException("Issue not found!");
        }
    }

    #[Route('/issue/delete/{id}', name: 'Delete')]
    public function delete(int $id): Response
    {
        $issue = $this->issueRepository->find($id);

        if ($issue) {
            $this->entityManager->remove($issue);
            $this->entityManager->flush();

            $this->addFlash('notify', "Issue Deleted Successfully");

            return $this->redirectToRoute('Issues');
        } else {
            throw $this->createNotFoundException("Issue not found!");
        }
    }

}
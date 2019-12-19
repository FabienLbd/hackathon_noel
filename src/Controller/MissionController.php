<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\User;
use App\Form\MissionType;
use App\Repository\MissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mission")
 */
class MissionController extends AbstractController
{
    /**
     * @Route("/", name="mission_index", methods={"GET"})
     * @param MissionRepository $missionRepository
     * @return Response
     */
    public function index(MissionRepository $missionRepository): Response
    {
        return $this->render('mission/index.html.twig', [
            'missions' => $missionRepository->findAll(),
        ]);
    }

    /**
     * @Route("/enroll/{mission}/{user}", name="mission_enroll", methods={"GET"})
     * @param Mission $mission
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function enroll(Mission $mission, User $user, EntityManagerInterface $entityManager) : Response
    {
        $user->addMission($mission);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->redirectToRoute('mission_index');
    }

    /**
     * @Route("/new", name="mission_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $mission = new Mission();
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($mission);
            $entityManager->flush();

            return $this->redirectToRoute('mission_index');
        }

        return $this->render('mission/new.html.twig', [
            'mission' => $mission,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="mission_show", methods={"GET"})
     */
    public function show(Mission $mission): Response
    {
        $planet = $mission->getPlanet();
        $users = $mission->getUsers();
        return $this->render('mission/show.html.twig', [
            'mission' => $mission,
            'planet'  => $planet,
            'users'   => $users,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="mission_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Mission $mission): Response
    {
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('mission_index');
        }

        return $this->render('mission/edit.html.twig', [
            'mission' => $mission,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="mission_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Mission $mission): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mission->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($mission);
            $entityManager->flush();
        }

        return $this->redirectToRoute('mission_index');
    }
}

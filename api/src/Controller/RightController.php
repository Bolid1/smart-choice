<?php

namespace App\Controller;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use App\DataPersister\RightDataPersister;
use App\Security\RightExtension;
use App\Entity\Right;
use App\Form\RightType;
use App\Repository\RightRepository;
use App\Security\RightVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rights")
 * @IsGranted("ROLE_USER")
 */
class RightController extends AbstractController
{
    /**
     * @Route("/", name="rights_list", methods={"GET"})
     *
     * @param RightRepository $repository
     * @param RightExtension $extension
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(RightRepository $repository, RightExtension $extension): Response
    {
        $extension->applyToCollection(
            $queryBuilder = $repository->createQueryBuilder('company'),
            new QueryNameGenerator(),
            Right::class,
            'get'
        );

        return $this->render(
            'right/list.html.twig',
            [
                'rights' => $queryBuilder->getQuery()->execute(),
            ]
        );
    }

    /**
     * @Route("/new", name="rights_new", methods={"GET","POST"})
     * @IsGranted(RightVoter::PRE_CREATE)
     *
     * @param Request $request
     * @param RightDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request, RightDataPersister $persister): Response
    {
        $right = new Right();
        $form = $this->createForm(RightType::class, $right);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->isGranted(RightVoter::CREATE, $right)) {
            $persister->persist($right);

            return $this->redirectToRoute('rights_list');
        }

        return $this->render(
            'right/new.html.twig',
            [
                'right' => $right,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{company}/{user}", name="rights_show", methods={"GET"})
     * @IsGranted(RightVoter::VIEW, subject="right")
     *
     * @param Right $right
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Right $right): Response
    {
        return $this->render(
            'right/show.html.twig',
            [
                'right' => $right,
            ]
        );
    }

    /**
     * @Route("/{company}/{user}/edit", name="rights_edit", methods={"GET","POST"})
     * @IsGranted(RightVoter::EDIT, subject="right")
     *
     * @param Request $request
     * @param Right $right
     * @param RightDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Right $right, RightDataPersister $persister): Response
    {
        $form = $this->createForm(RightType::class, $right);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $persister->persist($right);

            return $this->redirectToRoute('rights_list');
        }

        return $this->render(
            'right/edit.html.twig',
            [
                'right' => $right,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{company}/{user}", name="rights_delete", methods={"DELETE"})
     * @IsGranted(RightVoter::DELETE, subject="right")
     *
     * @param Request $request
     * @param Right $right
     * @param RightDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Right $right, RightDataPersister $persister): Response
    {
        $user = $right->getUser();
        $company = $right->getCompany();

        if ($user && $company) {
            $csrfKey = \implode('', ['delete', $user->getId(), $company->getId()]);

            if ($this->isCsrfTokenValid($csrfKey, $request->request->get('_token'))) {
                $persister->remove($right);
            }
        }

        return $this->redirectToRoute('rights_list');
    }
}

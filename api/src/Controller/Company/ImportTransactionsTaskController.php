<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\Entity\Company;
use App\Entity\ImportTransactionsTask;
use App\Form\ImportTransactionsTaskType;
use App\Repository\ImportTransactionsTaskRepository;
use App\Security\CompanyVoter;
use App\Security\ImportTransactionsTaskVoter;
use App\ValueObject\Pagination;
use DateTimeImmutable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/company/{company}/import/transaction")
 * @IsGranted("ROLE_USER")
 * @IsGranted(CompanyVoter::VIEW, subject="company")
 */
class ImportTransactionsTaskController extends AbstractController
{
    /**
     * @Route("s/{page}", name="company_import_transactions_tasks", methods={"GET"}, requirements={"page"="\d+"})
     *
     * @param \App\Entity\Company $company
     * @param \App\Repository\ImportTransactionsTaskRepository $repository
     * @param int $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(Company $company, ImportTransactionsTaskRepository $repository, int $page = 1): Response
    {
        $total = $repository->count(\compact('company'));
        $pagination = new Pagination($page, $total);

        return $this->render(
            'import_transactions_task/list.html.twig',
            [
                'company' => $company,
                'import_transactions_tasks' => $repository->findBy(
                    \compact('company'),
                    ['createdAt' => 'desc'],
                    $pagination->getLimit(),
                    $pagination->getOffset()
                ),
                'pagination' => $pagination,
            ]
        );
    }

    /**
     * @Route("/new", name="import_transactions_task_new", methods={"GET","POST"})
     * @IsGranted(ImportTransactionsTaskVoter::PRE_CREATE, subject="company")
     *
     * @param \App\Entity\Company $company
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Company $company, Request $request): Response
    {
        $importTransactionsTask = new ImportTransactionsTask();
        $importTransactionsTask->company = $company;
        /* @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $importTransactionsTask->user = $this->getUser();

        $form = $this->createForm(ImportTransactionsTaskType::class, $importTransactionsTask);
        $form->handleRequest($request);

        if (
            $form->isSubmitted() && $form->isValid()
            && $this->isGranted(
                ImportTransactionsTaskVoter::CREATE,
                $importTransactionsTask
            )
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($importTransactionsTask);
            $entityManager->flush();

            return $this->redirectToRoute('company_import_transactions_tasks', ['company' => $company->getId()]);
        }

        return $this->render(
            'import_transactions_task/new.html.twig',
            [
                'company' => $company,
                'import_transactions_task' => $importTransactionsTask,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="import_transactions_task_start", methods={"PATCH"})
     * @IsGranted(ImportTransactionsTaskVoter::EDIT, subject="importTransactionsTask")
     *
     * @param \App\Entity\Company $company
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\ImportTransactionsTask $importTransactionsTask
     * @param \Symfony\Component\Messenger\MessageBusInterface $bus
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function start(Company $company, Request $request, ImportTransactionsTask $importTransactionsTask, MessageBusInterface $bus): Response
    {
        if ($this->isCsrfTokenValid('start'.$importTransactionsTask->getId(), $request->request->get('_token'))) {
            $importTransactionsTask->setScheduledTime(new DateTimeImmutable());
            $this->getDoctrine()->getManager()->flush();
            $bus->dispatch(new \App\Message\ImportTransactionsTask($importTransactionsTask->getId()));
        }

        return $this->redirectToRoute('company_import_transactions_tasks', ['company' => $company->getId()]);
    }

    /**
     * @Route("/{id}", name="import_transactions_task_delete", methods={"DELETE"})
     * @IsGranted(ImportTransactionsTaskVoter::DELETE, subject="importTransactionsTask")
     *
     * @param \App\Entity\Company $company
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\ImportTransactionsTask $importTransactionsTask
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Company $company, Request $request, ImportTransactionsTask $importTransactionsTask): Response
    {
        if ($this->isCsrfTokenValid('delete'.$importTransactionsTask->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($importTransactionsTask);
            $entityManager->flush();
        }

        return $this->redirectToRoute('company_import_transactions_tasks', ['company' => $company->getId()]);
    }
}

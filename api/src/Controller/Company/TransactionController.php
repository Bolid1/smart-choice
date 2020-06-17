<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\DataPersister\TransactionDataPersister;
use App\Entity\Company;
use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Repository\TransactionRepository;
use App\Security\CompanyVoter;
use App\Security\TransactionVoter;
use App\ValueObject\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/company/{company}/transaction")
 * @IsGranted("ROLE_USER")
 * @IsGranted(CompanyVoter::VIEW, subject="company")
 */
class TransactionController extends AbstractController
{
    /**
     * @Route("s/{page}", name="company_transactions", methods={"GET"}, requirements={"page"="\d+"})
     *
     * @param company $company
     * @param TransactionRepository $transactionRepository
     * @param int $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Company $company, TransactionRepository $transactionRepository, int $page = 1): Response
    {
        $total = $transactionRepository->count(\compact('company'));
        $pagination = new Pagination($page, $total);

        return $this->render(
            'transaction/list.html.twig',
            [
                'company' => $company,
                'transactions' => $transactionRepository->findBy(
                    \compact('company'),
                    ['date' => 'desc'],
                    $pagination->getLimit(),
                    $pagination->getOffset()
                ),
                'pagination' => $pagination,
                'page' => $page,
                'total_pages' => $pagination->getPages(),
            ]
        );
    }

    /**
     * @Route("/new", name="transaction_new", methods={"GET","POST"})
     *
     * @param Company $company
     * @param Request $request
     * @param TransactionDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Company $company, Request $request, TransactionDataPersister $persister): Response
    {
        $transaction = (new Transaction())->setCompany($company);
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        $amount = \abs($transaction->getAmount());
        if (TransactionType::TYPE_EXPENSE === $form->get('type')->getData()) {
            $transaction->setAmount(-$amount);
        } else {
            $transaction->setAmount($amount);
        }

        if ($form->isSubmitted() && $form->isValid() && $this->isGranted(TransactionVoter::CREATE, $transaction)) {
            $persister->persist($transaction);

            return $this->redirectToRoute('company_dashboard', ['company' => $company->getId()]);
        }

        return $this->render(
            'transaction/new.html.twig',
            [
                'company' => $company,
                'transaction' => $transaction,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="transaction_edit", methods={"GET","POST"})
     * @IsGranted(TransactionVoter::EDIT, subject="transaction")
     *
     * @param company $company
     * @param request $request
     * @param transaction $transaction
     * @param transactionDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(
        Company $company,
        Request $request,
        Transaction $transaction,
        TransactionDataPersister $persister
    ): Response {
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $persister->persist($transaction);

            return $this->redirectToRoute('company_dashboard', ['company' => $company->getId()]);
        }

        return $this->render(
            'transaction/edit.html.twig',
            [
                'company' => $company,
                'transaction' => $transaction,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="transaction_delete", methods={"DELETE"})
     * @IsGranted(TransactionVoter::DELETE, subject="transaction")
     *
     * @param company $company
     * @param request $request
     * @param transaction $transaction
     * @param transactionDataPersister $persister
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(
        Company $company,
        Request $request,
        Transaction $transaction,
        TransactionDataPersister $persister
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$transaction->getId(), $request->request->get('_token'))) {
            $persister->remove($transaction);
        }

        $referer = $request->headers->get('referer');
        $transactionsList = $this->generateUrl('company_transactions', ['company' => $company->getId()]);

        return ($referer && false !== \strpos($referer, $transactionsList))
            ? $this->redirect($referer)
            : $this->redirectToRoute('company_transactions', ['company' => $company->getId()]);
    }
}

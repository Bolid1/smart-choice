<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\Entity\Company;
use App\Entity\Transaction;
use App\Entity\TransactionCategory;
use App\Form\TransactionCategoryType;
use App\Security\TransactionVoter;
use App\Security\Voter\TransactionCategoryVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/company/{company}/transaction/{transaction}/category")
 * @IsGranted("ROLE_USER")
 * @IsGranted(TransactionVoter::EDIT, subject="transaction")
 */
class TransactionCategoryController extends AbstractController
{
    /**
     * @Route("/new", name="transaction_category_new", methods={"GET","POST"})
     *
     * @IsGranted(TransactionCategoryVoter::PRE_CREATE, subject="company")
     *
     * @param \App\Entity\Company $company
     * @param \App\Entity\Transaction $transaction
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Company $company, Transaction $transaction, Request $request): Response
    {
        $transactionCategory = new TransactionCategory();
        $transactionCategory->setTransaction($transaction);
        $form = $this->createForm(TransactionCategoryType::class, $transactionCategory, ['csrf_protection' => false]);
        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
            && $this->isGranted(TransactionCategoryVoter::CREATE, $transactionCategory)
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($transactionCategory);
            $entityManager->flush();

            return $this->redirectToRoute(
                'transaction_edit',
                [
                    'company' => $company->getId(),
                    'id' => $transaction->getId(),
                ]
            );
        }

        return $this->render(
            'transaction_category/new.html.twig',
            [
                'transactionCategory' => $transactionCategory,
                'form' => $form->createView(),
                'company' => $company,
                'transaction' => $transaction,
                'categories' => $company->getCategories(),
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="transaction_category_edit", methods={"GET","POST"})
     * @IsGranted(TransactionCategoryVoter::EDIT, subject="transactionCategory")
     *
     * @param \App\Entity\Company $company
     * @param \App\Entity\Transaction $transaction
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\TransactionCategory $transactionCategory
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(
        Company $company,
        Transaction $transaction,
        Request $request,
        TransactionCategory $transactionCategory
    ): Response {
        $form = $this->createForm(TransactionCategoryType::class, $transactionCategory, ['csrf_protection' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute(
            'transaction_edit',
            [
                'company' => $company->getId(),
                'id' => $transaction->getId(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="transaction_category_delete", methods={"DELETE"})
     * @IsGranted(TransactionCategoryVoter::DELETE, subject="transactionCategory")
     *
     * @param \App\Entity\Company $company
     * @param \App\Entity\Transaction $transaction
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\TransactionCategory $transactionCategory
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(
        Company $company,
        Transaction $transaction,
        Request $request,
        TransactionCategory $transactionCategory
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$transactionCategory->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($transactionCategory);
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'transaction_edit',
            [
                'company' => $company->getId(),
                'id' => $transaction->getId(),
            ]
        );
    }
}

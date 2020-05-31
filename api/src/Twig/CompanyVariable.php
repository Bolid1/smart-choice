<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Company;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CompanyVariable
{
    private RequestStack $requestStack;

    /**
     * CompanyVariable constructor.
     *
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getId(): ?string
    {
        return ($company = $this->getCompany()) && ($id = $company->getId()) ? $id->toString() : null;
    }

    public function getName(): ?string
    {
        return ($company = $this->getCompany()) && ($name = $company->getName()) ? $name : null;
    }

    private function getCompany(): ?Company
    {
        $company = null;
        if ($request = $this->getRequest()) {
            $company = $request->get('company');
        }

        return $company instanceof Company ? $company : null;
    }

    private function getRequest(): ?Request
    {
        if (null !== $this->requestStack) {
            $request = $this->requestStack->getCurrentRequest();
        }

        return $request ?? null;
    }
}

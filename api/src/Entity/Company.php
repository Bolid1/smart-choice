<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CompanyRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     iri="https://schema.org/Organization",
 *     normalizationContext={
 *         "groups"={"company:read"},
 *         "swagger_definition_name": "Read",
 *     },
 *     denormalizationContext={
 *         "groups"={"company:edit"},
 *         "swagger_definition_name": "Edit",
 *     },
 *     collectionOperations={
 *         "get"={
 *             "security"="is_granted('ROLE_USER')",
 *             "security_message"="Only for registered users.",
 *         },
 *         "post"={
 *             "security"="is_granted('pre_create_company', object)",
 *             "security_message"="You have reached the limit for companies.",
 *         },
 *     },
 *     itemOperations={
 *         "get"={
 *             "security"="is_granted('view', object)",
 *             "security_message"="You can view only your companies.",
 *         },
 *         "patch"={
 *             "security"="is_granted('edit', object)",
 *             "security_message"="You can change only companies, in which you are admin.",
 *         },
 *         "delete"={
 *             "security"="is_granted('delete', object)",
 *             "security_message"="You can delete only your companies where only one user left.",
 *         },
 *     },
 * )
 * @ORM\Entity(repositoryClass=CompanyRepository::class)
 * @ORM\Table(
 *     name="`company`",
 * )
 *
 * @uses \App\Security\CompanyExtension::applyToCollection()
 * @uses \App\DataPersister\CompanyDataPersister::persist()
 * @uses \App\Security\CompanyVoter::voteOnAttribute()
 */
class Company
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private ?UuidInterface $id = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "Company creation date"})
     * @Gedmo\Timestampable(on="create")
     */
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "Company was last updated at date"})
     * @Gedmo\Timestampable(on="update")
     */
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"company:read", "company:edit"})
     * @Assert\Type("string")
     */
    private ?string $name = null;

    /**
     * @ORM\OneToMany(targetEntity=Right::class, mappedBy="company", orphanRemoval=true, cascade={"persist"})
     */
    private Collection $rights;

    /**
     * @ORM\OneToMany(targetEntity=Invitation::class, mappedBy="toCompany", orphanRemoval=true, cascade={"persist"})
     */
    private Collection $invitations;

    /**
     * @ORM\OneToMany(targetEntity=Account::class, mappedBy="company", orphanRemoval=true)
     */
    private Collection $accounts;

    /**
     * @ORM\OneToMany(targetEntity=Category::class, mappedBy="company", orphanRemoval=true)
     * @ORM\OrderBy({"left" = "ASC"})
     */
    private Collection $categories;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="company", orphanRemoval=true)
     */
    private Collection $transactions;

    /**
     * @ORM\OneToMany(targetEntity=TransactionCategory::class, mappedBy="company", orphanRemoval=true)
     */
    private Collection $transactionCategories;

    public function __construct()
    {
        $this->rights = new ArrayCollection();
        $this->invitations = new ArrayCollection();
        $this->accounts = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->transactionCategories = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Right[]
     */
    public function getRights(): Collection
    {
        return $this->rights;
    }

    public function getRightOf(User $user): ?Right
    {
        $result = null;
        foreach ($this->getRights() as $right) {
            if ($right->getUser() === $user) {
                $result = $right;
                break;
            }
        }

        return $result;
    }

    public function isUserAdmin(User $user): bool
    {
        return ($right = $this->getRightOf($user)) && $right->isAdmin();
    }

    public function addRight(Right $right): self
    {
        if (!$this->rights->contains($right)) {
            $this->rights[] = $right;
            $right->setCompany($this);
        }

        return $this;
    }

    public function addUser(User $user): Right
    {
        $right = (new Right())->setCompany($this)->setUser($user);
        $this->addRight($right);

        return $right;
    }

    public function removeRight(Right $right): self
    {
        if ($this->rights->contains($right)) {
            $this->rights->removeElement($right);
            // set the owning side to null (unless already changed)
            if ($right->getCompany() === $this) {
                $right->setCompany(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string)$this->name;
    }

    /**
     * @return Collection|Invitation[]
     */
    public function getInvitations(): Collection
    {
        return $this->invitations;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function getCategoryByName(string $name): ?Category
    {
        /** @var Category $category */
        foreach ($this->categories as $category) {
            if ($category->name === $name) {
                return $category;
            }
        }

        return null;
    }

    public function getCategoryById(string $id): ?Category
    {
        /** @var Category $category */
        foreach ($this->categories as $category) {
            if ($id === (string)$category->getId()) {
                return $category;
            }
        }

        return null;
    }

    /**
     * @return Collection|Account[]
     */
    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    public function getAccountByName(string $name): ?Account
    {
        /** @var Account $account */
        foreach ($this->accounts as $account) {
            if ($account->getName() === $name) {
                return $account;
            }
        }

        return null;
    }

    public function getAccountById(string $id): ?Account
    {
        /** @var Account $account */
        foreach ($this->accounts as $account) {
            if ($id === (string)$account->getId()) {
                return $account;
            }
        }

        return null;
    }

    public function addAccount(Account $account): self
    {
        if (!$this->accounts->contains($account)) {
            $this->accounts[] = $account;
            $account->setCompany($this);
        }

        return $this;
    }

    public function removeAccount(Account $account): self
    {
        if ($this->accounts->contains($account)) {
            $this->accounts->removeElement($account);
            // set the owning side to null (unless already changed)
            if ($account->getCompany() === $this) {
                $account->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setCompany($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getCompany() === $this) {
                $transaction->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TransactionCategory[]
     */
    public function getTransactionCategories(): Collection
    {
        return $this->transactionCategories;
    }
}

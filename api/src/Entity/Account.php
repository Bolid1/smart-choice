<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\AccountRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     iri="https://schema.org/BankAccount",
 *     normalizationContext={
 *         "groups"={"account:read"},
 *         "swagger_definition_name": "Read",
 *     },
 *     denormalizationContext={
 *         "groups"={"account:edit"},
 *         "swagger_definition_name": "Edit",
 *     },
 *     collectionOperations={
 *         "get"={
 *             "security"="is_granted('ROLE_USER')",
 *             "security_message"="Only for registered users.",
 *         },
 *         "post"={
 *             "denormalization_context"={
 *                 "groups"={"account:create"},
 *                 "swagger_definition_name": "Create",
 *             },
 *             "validation_groups"={"Default", "account:create"},
 *             "security_post_denormalize"="is_granted('create', object)",
 *             "security_message"="Only company admin can manage accounts of company.",
 *         },
 *     },
 *     itemOperations={
 *         "get"={
 *             "security"="is_granted('view', object)",
 *             "security_message"="You can view only your accounts or accounts of companies, where you are admin.",
 *         },
 *         "patch"={
 *             "security"="is_granted('edit', object)",
 *             "security_message"="You can't edit accounts of companies, where you are not is admin.",
 *         },
 *         "delete"={
 *             "security"="is_granted('delete', object)",
 *             "security_message"="You can't delete accounts of companies, where you are not is admin.",
 *         },
 *     },
 * )
 * @ORM\Entity(repositoryClass=AccountRepository::class)
 * @ORM\Table(
 *     name="`account`",
 *     indexes={
 *         @ORM\Index(name="account__company_id__idx", columns={"company_id"}),
 *     },
 * )
 *
 * @uses \App\Security\AccountExtension::applyToCollection()
 * @uses \App\DataPersister\AccountDataPersister::persist()
 * @uses \App\Security\AccountVoter::voteOnAttribute()
 */
class Account
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private ?UuidInterface $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="accounts")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"account:read", "account:create"})
     * @Assert\NotBlank()
     */
    private Company $company;

    /**
     * @ApiProperty(iri="https://schema.org/currency")
     * @ORM\Column(type="string", length=3)
     * @Groups({"account:read", "account:create"})
     * @Assert\NotBlank()
     * @Assert\Currency()
     */
    private string $currency;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"account:read", "account:create", "account:edit"})
     * @Assert\Type("string")
     * @Assert\NotBlank()
     */
    private string $name = '';

    /**
     * @ORM\Column(type="float")
     * @Groups({"account:read", "account:create"})
     * @Assert\Type("float")
     */
    private float $balance = 0.;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "Account creation date"})
     * @Gedmo\Timestampable(on="create")
     */
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "Account was last updated at date"})
     * @Gedmo\Timestampable(on="update")
     */
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="account")
     */
    private Collection $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getCompany(): ?Company
    {
        return $this->company ?? null;
    }

    public function setCompany(Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = \strtoupper($currency);

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function addBalance(float $amount): self
    {
        return $this->setBalance($this->getBalance() + $amount);
    }

    public function subBalance(float $amount): self
    {
        return $this->setBalance($this->getBalance() - $amount);
    }

    public function setBalance(float $balance): self
    {
        $this->balance = \round($balance, Currencies::getFractionDigits($this->currency));

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
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
            $transaction->setAccount($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getAccount() === $this) {
                $transaction->setAccount(null);
            }
        }

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\TransactionRepository;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use UnexpectedValueException;

/**
 * @ApiResource(
 *     iri="https://schema.org/MoneyTransfer",
 *     normalizationContext={
 *         "groups"={"transaction:read"},
 *         "swagger_definition_name": "Read",
 *         "skip_null_values": false,
 *     },
 *     denormalizationContext={
 *         "groups"={"transaction:edit"},
 *         "swagger_definition_name": "Edit",
 *     },
 *     collectionOperations={
 *         "get"={
 *             "security"="is_granted('ROLE_USER')",
 *             "security_message"="Only for registered users.",
 *         },
 *         "post"={
 *             "denormalization_context"={
 *                 "groups"={"transaction:create"},
 *                 "swagger_definition_name": "Create",
 *             },
 *             "validation_groups"={"Default", "transaction:create"},
 *             "security_post_denormalize"="is_granted('create', object)",
 *             "security_message"="You can't create transaction.",
 *         },
 *     },
 *     itemOperations={
 *         "get"={
 *             "security"="is_granted('view', object)",
 *             "security_message"="You can't view transaction.",
 *         },
 *         "patch"={
 *             "security"="is_granted('edit', object)",
 *             "security_message"="You can't edit transaction.",
 *         },
 *         "delete"={
 *             "security"="is_granted('delete', object)",
 *             "security_message"="You can't delete transaction.",
 *         },
 *     },
 * )
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 * @ORM\Table(
 *     name="`transaction`",
 *     indexes={
 *         @ORM\Index(name="transaction__account_id__idx", columns={"account_id"}),
 *         @ORM\Index(name="transaction__company_id__idx", columns={"company_id"}),
 *     },
 * )
 *
 * @uses \App\Security\TransactionExtension::applyToCollection()
 * @uses \App\DataPersister\TransactionDataPersister::persist()
 * @uses \App\Security\TransactionVoter::voteOnAttribute()
 *
 * @Assert\Expression("this.getCompany() == this.getAccount().getCompany()", message="You can't transfer transaction to another account.")
 */
class Transaction
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private ?UuidInterface $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"transaction:read"})
     */
    private Company $company;

    /**
     * @ORM\ManyToOne(targetEntity=Account::class, inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"transaction:read", "transaction:create", "transaction:edit"})
     * @Assert\NotBlank()
     */
    private Account $account;

    /**
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     * @Groups({"transaction:read", "transaction:create", "transaction:edit"})
     * @Assert\Type(DateTimeImmutable::class)
     */
    private ?DateTimeImmutable $date = null;

    /**
     * @ORM\Column(type="float", nullable=false)
     * @Groups({"transaction:read", "transaction:create", "transaction:edit"})
     * @Assert\Type("float")
     */
    private float $amount = 0.;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "Transaction creation date"})
     * @Gedmo\Timestampable(on="create")
     */
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "Transaction was last updated at date"})
     * @Gedmo\Timestampable(on="update")
     */
    private ?DateTimeImmutable $updatedAt = null;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getAccount(): ?Account
    {
        return $this->account ?? null;
    }

    public function setAccount(Account $account): self
    {
        $oldAccount = $this->getAccount();
        $newAccount = $account;

        if ($oldAccount !== $newAccount) {
            if ($oldAccount instanceof Account) {
                $oldAccount->subBalance($this->getAmount());
            }

            if ($newAccount instanceof Account) {
                $newAccount->addBalance($this->getAmount());
            }
        }

        if (!isset($this->company)) {
            $this->setCompany($account->getCompany());
        }

        $this->account = $account;

        return $this;
    }

    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?DateTimeInterface $date): self
    {
        if ($date instanceof DateTime) {
            $this->date = DateTimeImmutable::createFromMutable($date);
        } elseif ($date instanceof DateTimeImmutable) {
            $this->date = $date;
        } elseif (null === $date) {
            $this->date = null;
        } else {
            throw new UnexpectedValueException('Unexpected implementation of DateTimeInterface: ', \get_class($date));
        }

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount ?? 0.;
    }

    public function setAmount(float $amount): self
    {
        if ($account = $this->getAccount()) {
            $account->subBalance($this->amount);
            $account->addBalance($amount);
        }

        $this->amount = $amount;

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

    public function getCompany(): ?Company
    {
        return $this->company ?? null;
    }

    public function setCompany(Company $company): self
    {
        $this->company = $company;

        return $this;
    }
}

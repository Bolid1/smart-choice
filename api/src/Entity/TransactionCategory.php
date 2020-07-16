<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\TransactionCategoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LogicException;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     normalizationContext={
 *         "groups"={"transaction_category:read"},
 *         "swagger_definition_name": "Read",
 *         "skip_null_values": false,
 *     },
 *     denormalizationContext={
 *         "groups"={"transaction_category:edit"},
 *         "swagger_definition_name": "Edit",
 *     },
 *     collectionOperations={
 *         "get"={
 *             "security"="is_granted('ROLE_USER')",
 *             "security_message"="Only for registered users.",
 *         },
 *         "post"={
 *             "denormalization_context"={
 *                 "groups"={"transaction_category:create"},
 *                 "swagger_definition_name": "Create",
 *             },
 *             "validation_groups"={"Default", "transaction_category:create"},
 *             "security"="is_granted('ROLE_USER')",
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
 * @ORM\Entity(repositoryClass=TransactionCategoryRepository::class)
 * @ORM\Table(
 *     name="`transaction_category`",
 *     indexes={
 *         @ORM\Index(name="transaction_category__transaction_id__idx", columns={"transaction_id"}),
 *         @ORM\Index(name="transaction_category__company_id__idx", columns={"company_id"}),
 *         @ORM\Index(name="transaction_category__category_id__idx", columns={"category_id"}),
 *     },
 * )
 * @ORM\HasLifecycleCallbacks()
 *
 * @uses \App\Security\Extension\TransactionCategoryExtension::applyToCollection()
 * @uses \App\Security\Voter\TransactionCategoryVoter::voteOnAttribute()
 *
 * @Assert\Expression(
 *     "this.category and this.getTransaction() and this.category.company == this.getTransaction().getCompany()",
 *     message="Transaction and category should from one company.",
 * )
 */
class TransactionCategory
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private ?UuidInterface $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="transactionCategories")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"transaction_category:read"})
     */
    private Company $company;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="transactionCategories")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({
     *     "transaction_category:read",
     *     "transaction_category:create",
     *     "transaction_category:edit",
     *     "transaction:read",
     * })
     * @Assert\NotBlank()
     */
    public ?Category $category = null;

    /**
     * @ORM\ManyToOne(targetEntity=Transaction::class, inversedBy="transactionCategories")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"transaction_category:read", "transaction_category:create"})
     * @Assert\NotBlank()
     */
    private Transaction $transaction;

    /**
     * @ORM\Column(type="float", nullable=false)
     * @Groups({
     *     "transaction_category:read",
     *     "transaction_category:create",
     *     "transaction_category:edit",
     *     "transaction:read",
     * })
     * @Assert\Type("float")
     */
    public float $amount = 0.;

    /**
     * @ORM\Column(type="datetimetz_immutable")
     * @Gedmo\Timestampable(on="create")
     */
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(type="datetimetz_immutable")
     * @Gedmo\Timestampable(on="update")
     */
    private ?DateTimeImmutable $updatedAt = null;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    /**
     * @return \App\Entity\Company
     */
    public function getCompany(): ?Company
    {
        return $this->company ?? null;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction ?? null;
    }

    public function setTransaction(Transaction $transaction): self
    {
        $this->transaction = $transaction;
        $company = $transaction->getCompany();
        if (!$company instanceof Company) {
            throw new LogicException('Transaction without company');
        }

        /* @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->company = $company;

        return $this;
    }
}

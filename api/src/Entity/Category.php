<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CategoryRepository;
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
 *     iri="https://schema.org/category",
 *     normalizationContext={
 *         "groups"={"category:read"},
 *         "swagger_definition_name": "Read",
 *     },
 *     denormalizationContext={
 *         "groups"={"category:edit"},
 *         "swagger_definition_name": "Edit",
 *     },
 *     collectionOperations={
 *         "get"={
 *             "security"="is_granted('ROLE_USER')",
 *             "security_message"="Only for registered users.",
 *         },
 *         "post"={
 *             "denormalization_context"={
 *                 "groups"={"category:create"},
 *                 "swagger_definition_name": "Create",
 *             },
 *             "validation_groups"={"Default", "category:create"},
 *             "security_post_denormalize"="is_granted('create', object)",
 *             "security_message"="Only company admin can manage categories of company.",
 *         },
 *     },
 *     itemOperations={
 *         "get"={
 *             "security"="is_granted('view', object)",
 *             "security_message"="You can view only your categories or categories of companies, where you are admin.",
 *         },
 *         "patch"={
 *             "security"="is_granted('edit', object)",
 *             "security_message"="You can't edit categories of companies, where you are not is admin.",
 *         },
 *         "delete"={
 *             "security"="is_granted('delete', object)",
 *             "security_message"="You can't delete categories of companies, where you are not is admin.",
 *         },
 *     },
 * )
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @ORM\Table(
 *     name="`category`",
 *     indexes={
 *         @ORM\Index(name="category__company_id__idx", columns={"company_id"}),
 *         @ORM\Index(name="category__parent_id__idx", columns={"parent_id"}),
 *     },
 * )
 * @Gedmo\Tree(type="nested")
 *
 * @uses \App\Security\CategoryExtension::applyToCollection()
 * @uses \App\DataPersister\CategoryDataPersister::persist()
 * @uses \App\Security\Voter\CategoryVoter::voteOnAttribute()
 *
 * @Assert\Expression("not this.getParent() or this.company == this.getParent().company", message="You can't transfer category to another company.")
 */
class Category
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private ?UuidInterface $id = null;

    /**
     * @Gedmo\TreeRoot()
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="categories")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"category:read", "category:create"})
     * @Assert\NotBlank()
     */
    public Company $company;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"category:read", "category:create", "category:edit"})
     * @Assert\Type("string")
     * @Assert\NotBlank()
     * @Assert\Length(min=3)
     */
    public string $name = '';

    /**
     * @Gedmo\TreeLeft()
     * @ORM\Column(name="lft", type="integer")
     * @Groups({"category:read"})
     */
    private int $left;

    /**
     * @Gedmo\TreeRight()
     * @ORM\Column(name="rgt", type="integer")
     * @Groups({"category:read"})
     */
    private int $right;

    /**
     * @Gedmo\TreeParent()
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     * @Groups({"category:read", "category:create"})
     */
    private ?Category $parent = null;

    /**
     * @Gedmo\TreeLevel()
     * @ORM\Column(type="integer")
     * @Groups({"category:read"})
     */
    private int $level;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     * @ORM\OrderBy({"left" = "ASC"})
     * @Groups({"category:read"})
     */
    public Collection $children;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "Category creation date"})
     * @Gedmo\Timestampable(on="create")
     */
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "Category was last updated at date"})
     * @Gedmo\Timestampable(on="update")
     */
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * @ORM\OneToMany(targetEntity=TransactionCategory::class, mappedBy="category", orphanRemoval=true)
     */
    private Collection $transactionCategories;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->transactionCategories = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getLeft(): int
    {
        return $this->left;
    }

    /**
     * @return int
     */
    public function getRight(): int
    {
        return $this->right;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return Category|null
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @param Category|null $parent
     *
     * @return \$this
     */
    public function setParent(?self $parent): self
    {
        if (!$this->parent = $parent) {
            $this->level = 0;
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return Collection|TransactionCategory[]
     */
    public function getTransactionCategories(): Collection
    {
        return $this->transactionCategories;
    }
}

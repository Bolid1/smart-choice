<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\RightRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     iri="https://schema.org/Role",
 *     normalizationContext={
 *         "groups"={"right:read"},
 *         "swagger_definition_name": "Read",
 *     },
 *     denormalizationContext={
 *         "groups"={"right:edit"},
 *         "swagger_definition_name": "Edit",
 *     },
 *     collectionOperations={
 *         "get"={
 *             "security"="is_granted('ROLE_USER')",
 *             "security_message"="Only for registered users.",
 *         },
 *     },
 *     itemOperations={
 *         "get"={
 *             "security"="is_granted('view', object)",
 *             "security_message"="You can view only your rights or rights of companies, where you are admin.",
 *         },
 *         "patch"={
 *             "security"="is_granted('edit', object)",
 *             "security_message"="You can't edit rights of companies, where you are not is admin.",
 *         },
 *         "delete"={
 *             "security"="is_granted('delete', object)",
 *             "security_message"="You can't delete rights of companies, where you are not is admin.",
 *         },
 *     },
 * )
 * @ORM\Entity(repositoryClass=RightRepository::class)
 * @ORM\Table(
 *     name="`right`",
 *     indexes={
 *         @ORM\Index(name="right__user_id__idx", columns={"user_id"}),
 *         @ORM\Index(name="right__company_id__idx", columns={"company_id"}),
 *     }
 * )
 *
 * @uses \App\Security\RightExtension::applyToCollection()
 * @uses \App\Security\RightVoter::voteOnAttribute()
 */
class Right
{
    /** @var int Max count of companies, in which can user be added. */
    public const MAX_FOR_USER = 10;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="rights")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"right:read"})
     */
    private ?User $user = null;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="rights")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"right:read"})
     */
    private ?Company $company = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "Right creation date"})
     * @Gedmo\Timestampable(on="create")
     */
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "Right was last updated at date"})
     * @Gedmo\Timestampable(on="update")
     */
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * @ORM\Column(type="boolean", options={"comment": "Is user admin in company?", "default": false})
     * @Groups({"right:read", "right:edit"})
     * @Assert\Type("boolean")
     */
    private bool $admin = false;

    public function getId(): ?array
    {
        return $this->user && $this->company
            ? ['user' => $this->user->getId(), 'company' => $this->company->getId()]
            : null;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function isUserCompanyAdmin(User $user): bool
    {
        return ($company = $this->getCompany()) && $company->isUserAdmin($user);
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function isAdmin(): ?bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): self
    {
        $this->admin = $admin;

        return $this;
    }
}

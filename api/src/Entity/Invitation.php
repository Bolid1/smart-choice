<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Constraint;
use App\Repository\InvitationRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     normalizationContext={
 *         "groups"={"invitation:read"},
 *         "swagger_definition_name": "Read",
 *     },
 *     denormalizationContext={
 *         "groups"={"invitation:edit"},
 *         "swagger_definition_name": "Edit",
 *     },
 *     collectionOperations={
 *         "get"={
 *             "security"="is_granted('ROLE_USER')",
 *             "security_message"="Only for registered users.",
 *         },
 *         "post"={
 *             "denormalization_context"={
 *                 "groups"={"invitation:create"},
 *                 "swagger_definition_name": "Create",
 *             },
 *             "validation_groups"={"Default", "invitation:create"},
 *             "security_post_denormalize"="is_granted('create', object)",
 *             "security_message"="Only company admin can manage invitations of company.",
 *         },
 *     },
 *     itemOperations={
 *         "get"={
 *             "security"="is_granted('view', object)",
 *             "security_message"="You can view only your invitations or invitations of companies, where you are admin.",
 *         },
 *         "patch"={
 *             "security"="is_granted('edit', object)",
 *             "security_message"="You can't edit invitations of companies, where you are not is admin.",
 *         },
 *         "delete"={
 *             "security"="is_granted('delete', object)",
 *             "security_message"="You can't delete invitations of companies, where you are not is admin.",
 *         },
 *         "accept"={
 *             "method"="POST",
 *             "path"="/invitations/{id}/accept",
 *             "controller"=App\Controller\API\V0\AcceptInvitation::class,
 *             "security"="is_granted('accept', object)",
 *             "security_message"="You can't accept of another users.",
 *             "denormalization_context"={
 *                 "groups"={"invitation:accept"},
 *                 "swagger_definition_name": "Accept",
 *             },
 *             "validation_groups"={"Default", "invitation:accept"},
 *             "openapi_context"={
 *                 "summary"="Accept invitation.",
 *             },
 *         }
 *     },
 * )
 * @ORM\Entity(repositoryClass=InvitationRepository::class)
 * @ORM\Table(
 *     name="`invitation`",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="invitation__to_company__email__uniq", columns={"to_company_id", "email"}),
 *     },
 *     indexes={
 *         @ORM\Index(name="invitation__from_user_id__idx", columns={"from_user_id"}),
 *         @ORM\Index(name="invitation__to_company_id__idx", columns={"to_company_id"}),
 *         @ORM\Index(name="invitation__email__idx", columns={"email"}),
 *     }
 * )
 *
 * @uses \App\Security\InvitationExtension::applyToCollection()
 * @uses \App\Security\InvitationVoter::voteOnAttribute()
 *
 * @UniqueEntity(fields={"toCompany", "email"}, errorPath="email")
 */
class Invitation
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private ?UuidInterface $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Gedmo\Blameable(on="create")
     * @Groups({"invitation:read"})
     */
    private ?User $fromUser = null;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="invitations")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"invitation:read", "invitation:create"})
     * @Assert\NotBlank()
     */
    private ?Company $toCompany = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"invitation:read", "invitation:create"})
     * @Assert\Type("string")
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private string $email = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $secret;

    /**
     * Plain secret.
     *
     * @Groups({"invitation:create", "invitation:edit", "invitation:accept"})
     * @Assert\NotBlank(message="Please enter a secret", groups={"invitation:create"})
     * @Assert\Length(min=6, minMessage="Your secret should be at least {{ limit }} characters", max=4096)
     * @Constraint\IsInvitationSecretValid(groups={"invitation:accept"})
     */
    private string $plainSecret;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "Invitation creation date"})
     * @Gedmo\Timestampable(on="create")
     */
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "Invitation was last updated at date"})
     * @Gedmo\Timestampable(on="update")
     */
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     * @Groups({"invitation:read", "invitation:create", "invitation:edit"})
     * @Assert\Type("boolean")
     */
    private bool $admin = false;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getFromUser(): ?User
    {
        return $this->fromUser;
    }

    public function setFromUser(?User $fromUser): self
    {
        $this->fromUser = $fromUser;

        return $this;
    }

    public function getToCompany(): ?Company
    {
        return $this->toCompany;
    }

    public function setToCompany(?Company $toCompany): self
    {
        $this->toCompany = $toCompany;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSecret(): ?string
    {
        return $this->secret ?? null;
    }

    public function setSecret(string $secret): self
    {
        $this->secret = $secret;

        return $this;
    }

    public function getPlainSecret(): ?string
    {
        return $this->plainSecret ?? null;
    }

    public function setPlainSecret(string $plainSecret): self
    {
        $this->plainSecret = $plainSecret;

        return $this;
    }

    public function erasePlainSecret(): self
    {
        unset($this->plainSecret);

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

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}

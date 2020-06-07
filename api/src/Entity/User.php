<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     iri="https://schema.org/Person",
 *     normalizationContext={
 *         "groups"={"user:read"},
 *         "swagger_definition_name": "Read",
 *     },
 *     collectionOperations={
 *         "get"={
 *             "security"="is_granted('ROLE_USER')",
 *             "security_message"="Only for registered users.",
 *         },
 *         "post"={
 *             "denormalization_context"={
 *                 "groups"={"user:create"},
 *                 "swagger_definition_name": "Create",
 *             },
 *             "security"="not is_granted('ROLE_USER')",
 *             "security_message"="You're already registered.",
 *         },
 *     },
 *     itemOperations={
 *         "get"={
 *             "security"="object == user",
 *             "security_message"="You can view only self props.",
 *         },
 *         "patch"={
 *             "denormalization_context"={
 *                 "groups"={"user:patch"},
 *                 "swagger_definition_name": "Edit",
 *             },
 *             "security"="object == user",
 *             "security_message"="You can change only self props.",
 *         },
 *     },
 * )
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(
 *     name="`user`",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="user__email__uniq", columns={"email"})
 *     }
 * )
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 *
 * @uses \App\Security\UserExtension::applyToCollection()
 * @uses \App\DataPersister\UserDataPersister
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private ?UuidInterface $id = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "User registration date"})
     * @Gedmo\Timestampable(on="create")
     */
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "User was last updated at date"})
     * @Gedmo\Timestampable(on="update")
     */
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * The email of user.
     *
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"user:read", "user:create"})
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private ?string $email = null;

    /**
     * The hashed password.
     *
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * Plain user password.
     *
     * @Groups({"user:create", "user:patch"})
     * @Assert\NotBlank(message="Please enter a password")
     * @Assert\NotCompromisedPassword()
     * @Assert\Length(min=6, minMessage="Your password should be at least {{ limit }} characters", max=4096)
     */
    private ?string $plainPassword = null;

    /**
     * @ORM\OneToMany(targetEntity=Right::class, mappedBy="user", orphanRemoval=true)
     */
    private Collection $rights;

    public function __construct()
    {
        $this->rights = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
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
     * @return Collection|Right[]
     */
    public function getRights(): Collection
    {
        return $this->rights;
    }

    /**
     * @return Collection|Company[]
     */
    public function getCompanies(): Collection
    {
        return $this
            ->rights
            ->map(
                static function (Right $right) {
                    return $right->getCompany();
                }
            )
            ;
    }

    /**
     * @return bool Does user has reached the quota for memberships in companies?
     */
    public function isLimitForCompaniesReached(): bool
    {
        return $this->rights->count() >= Right::MAX_FOR_USER;
    }

    public function addRight(Right $right): self
    {
        if (!$this->rights->contains($right)) {
            $this->rights[] = $right;
            $right->setUser($this);
        }

        return $this;
    }

    public function removeRight(Right $right): self
    {
        if ($this->rights->contains($right)) {
            $this->rights->removeElement($right);
            // set the owning side to null (unless already changed)
            if ($right->getUser() === $this) {
                $right->setUser(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string)$this->email;
    }
}

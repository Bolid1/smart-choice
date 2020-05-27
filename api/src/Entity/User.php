<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     iri="https://schema.org/Person",
 *     normalizationContext={"groups"={"user:read"}},
 *     attributes={
 *         "security"="is_granted('ROLE_USER')",
 *         "security_message"="Only for registered users."
 *     },
 *     collectionOperations={
 *         "get",
 *         "post"={
 *             "denormalization_context"={"groups"={"user:create"}},
 *             "security"="not is_granted('ROLE_USER')",
 *             "security_message"="You're already registered."
 *         },
 *     },
 *     itemOperations={
 *         "get"={
 *             "security"="is_granted('ROLE_USER') and object == user",
 *             "security_message"="You can view only self props."
 *         },
 *         "patch"={
 *             "denormalization_context"={"groups"={"user:patch"}},
 *             "security"="is_granted('ROLE_USER') and object == user",
 *             "security_message"="You can change only self props."
 *         },
 *     }
 * )
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(
 *     name="`user`",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="user__email__uniq", columns={"email"})
 *     }
 * )
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 * @HasLifecycleCallbacks
 *
 * @uses \App\Doctrine\Security\UserExtension::applyToCollection()
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
     */
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "User was last updated at date"})
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
     * @return UuidInterface|null
     */
    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
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

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string|null $plainPassword
     *
     * @return User
     */
    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set timestamp for "created_at" column.
     *
     * @ORM\PreFlush
     */
    public function setActualCreatedAt(): void
    {
        if (null === $this->createdAt) {
            $this->createdAt = new DateTimeImmutable();
        }
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Set timestamp for "updated_at" column.
     *
     * @ORM\PreFlush
     */
    public function setActualUpdatedAt(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}

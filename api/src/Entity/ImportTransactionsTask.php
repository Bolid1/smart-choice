<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Helper\DateTimeHelper;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     iri="https://schema.org/ScheduleAction",
 *     normalizationContext={
 *         "groups"={"import_transactions_task:read"},
 *         "swagger_definition_name": "Read",
 *         "skip_null_values": false,
 *     },
 *     denormalizationContext={
 *         "groups"={"import_transactions_task:edit"},
 *         "swagger_definition_name": "Edit",
 *     },
 *     collectionOperations={
 *         "get"={
 *             "security"="is_granted('ROLE_USER')",
 *             "security_message"="Only for registered users.",
 *         },
 *         "post"={
 *             "denormalization_context"={
 *                 "groups"={"import_transactions_task:create"},
 *                 "swagger_definition_name": "Create",
 *             },
 *             "validation_groups"={"Default", "import_transactions_task:create"},
 *             "security"="is_granted('ROLE_USER')",
 *             "security_post_denormalize"="is_granted('create', object)",
 *             "security_message"="You can't create import of transactions task.",
 *         },
 *     },
 *     itemOperations={
 *         "get"={
 *             "security"="is_granted('view', object)",
 *             "security_message"="You can't view this task.",
 *         },
 *         "delete"={
 *             "security"="is_granted('delete', object)",
 *             "security_message"="You can't delete this task.",
 *         },
 *     },
 * )
 * @ORM\Entity()
 * @ORM\Table(
 *     name="`import_transactions_task`",
 *     indexes={
 *         @ORM\Index(name="import_transactions_task__user_id__idx", columns={"user_id"}),
 *         @ORM\Index(name="import_transactions_task__company_id__idx", columns={"company_id"}),
 *     },
 * )
 *
 * @uses \App\Security\ImportTransactionsTaskExtension::applyToCollection()
 * @uses \App\Security\ImportTransactionsTaskVoter::voteOnAttribute()
 */
class ImportTransactionsTask
{
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_STARTED = 'started';
    public const STATUS_FINISHED = 'finished';

    public const MIME_TYPES_VARIANTS = [
        'jsonld',
        'jsonhal',
        'jsonapi',
        'json',
        'xml',
        'yaml',
        'csv',
        'html',
    ];

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private ?UuidInterface $id = null;

    /**
     * Data to import.
     *
     * @ORM\Column(type="text", options={"comment": "Data to import"})
     * @Groups({"import_transactions_task:read", "import_transactions_task:create"})
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="10")
     */
    public string $data = '';

    /**
     * Mime type of data.
     *
     * @ApiProperty(
     *     iri="https://schema.org/contentType",
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *             "enum"={"jsonld", "jsonhal", "jsonapi", "json", "xml", "yaml", "csv", "html"},
     *             "example"="csv"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=10, options={"comment": "Mime type of data"})
     * @Groups({"import_transactions_task:read", "import_transactions_task:create"})
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={"jsonld", "jsonhal", "jsonapi", "json", "xml", "yaml", "csv", "html"})
     */
    public string $mimeType = 'csv';

    /**
     * Errors, that occurred during task process.
     *
     * @ORM\Column(
     *     type="json",
     *     nullable=true,
     *     options={"comment": "Errors, that occurred during task process"},
     * )
     * @Groups({"import_transactions_task:read"})
     */
    public ?array $errors = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "Task creation date"})
     * @Gedmo\Timestampable(on="create")
     */
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "Task was last updated at date"})
     * @Gedmo\Timestampable(on="update")
     */
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * To which company should transactions be added.
     *
     * @ORM\ManyToOne(targetEntity=Company::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"import_transactions_task:read", "import_transactions_task:create"})
     *
     * @Assert\NotBlank()
     */
    public Company $company;

    /**
     * From which user should transactions be added.
     *
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Gedmo\Blameable(on="create")
     *
     * @Groups({"import_transactions_task:read"})
     */
    public User $user;

    /**
     * Task process was started at.
     *
     * @ApiProperty(iri="https://schema.org/scheduledTime")
     *
     * @ORM\Column(type="datetimetz_immutable", options={"comment": "Task process should be started at"})
     * @Groups({"import_transactions_task:read"})
     *
     * @Assert\Type(DateTimeImmutable::class)
     */
    private DateTimeImmutable $scheduledTime;

    /**
     * Task status.
     *
     * @ApiProperty(
     *     iri="https://schema.org/actionStatus",
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *             "enum"={
     *                 ImportTransactionsTask::STATUS_ACCEPTED,
     *                 ImportTransactionsTask::STATUS_STARTED,
     *                 ImportTransactionsTask::STATUS_FINISHED,
     *             },
     *             "example"=ImportTransactionsTask::STATUS_ACCEPTED
     *         }
     *     }
     * )
     * @ORM\Column(
     *     type="string",
     *     length=15,
     *     options={
     *         "default": "accepted",
     *         "comment": "Task status",
     *     }
     * )
     * @Groups({"import_transactions_task:read"})
     */
    public string $status = self::STATUS_ACCEPTED;

    /**
     * Task process started at.
     *
     * @ApiProperty(iri="https://schema.org/startTime")
     * @ORM\Column(
     *     type="datetimetz_immutable",
     *     nullable=true,
     *     options={"comment": "Task process started at"},
     * )
     * @Groups({"import_transactions_task:read"})
     */
    private ?DateTimeImmutable $startTime;

    /**
     * Task process finished at.
     *
     * @ApiProperty(iri="https://schema.org/endTime")
     * @ORM\Column(
     *     type="datetimetz_immutable",
     *     nullable=true,
     *     options={"comment": "Task process finished at"},
     * )
     * @Groups({"import_transactions_task:read"})
     */
    private ?DateTimeImmutable $endTime;

    /**
     * Count of transactions, that was successfully imported.
     *
     * @ApiProperty(iri="https://schema.org/result")
     * @ORM\Column(type="integer", options={"comment": "Count of transactions, that was successfully imported", "default": 0})
     * @Groups({"import_transactions_task:read"})
     */
    public int $successfullyImported = 0;

    /**
     * Count of transactions, that was rejected.
     *
     * @ApiProperty(iri="https://schema.org/result")
     * @ORM\Column(type="integer", options={"comment": "Count of transactions, that was rejected", "default": 0})
     * @Groups({"import_transactions_task:read"})
     */
    public int $failedToImport = 0;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getScheduledTime(): ?DateTimeImmutable
    {
        return $this->scheduledTime ?? null;
    }

    /**
     * @param DateTimeInterface $scheduledTime
     *
     * @return ImportTransactionsTask
     */
    public function setScheduledTime(DateTimeInterface $scheduledTime): self
    {
        $this->scheduledTime = DateTimeHelper::toImmutable($scheduledTime);

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getStartTime(): ?DateTimeImmutable
    {
        return $this->startTime ?? null;
    }

    /**
     * @param DateTimeInterface $startTime
     *
     * @return ImportTransactionsTask
     */
    public function setStartTime(DateTimeInterface $startTime): self
    {
        $this->startTime = DateTimeHelper::toImmutable($startTime);

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getEndTime(): ?DateTimeImmutable
    {
        return $this->endTime ?? null;
    }

    /**
     * @param DateTimeInterface $endTime
     *
     * @return ImportTransactionsTask
     */
    public function setEndTime(DateTimeInterface $endTime): self
    {
        $this->endTime = DateTimeHelper::toImmutable($endTime);

        return $this;
    }

    public function beforeSchedule(): void
    {
        $this->scheduledTime = new DateTimeImmutable();
    }

    /**
     * @return ImportTransactionsTask
     */
    public function onStart(): self
    {
        $this->failedToImport = $this->successfullyImported = 0;
        $this->startTime = new DateTimeImmutable();
        $this->status = static::STATUS_STARTED;
        $this->errors = [];

        return $this;
    }

    /**
     * @param array $errors
     *
     * @return ImportTransactionsTask
     */
    public function onFinish(?array $errors = null): self
    {
        $this->endTime = new DateTimeImmutable();
        $this->status = static::STATUS_FINISHED;

        if ($errors) {
            $this->errors = \array_merge((array)$this->errors, $errors);
        }

        return $this;
    }
}

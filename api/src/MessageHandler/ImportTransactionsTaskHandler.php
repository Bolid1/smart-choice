<?php

declare(strict_types=1);

namespace App\MessageHandler;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\DataPersister\TransactionDataPersister;
use App\Entity\Transaction;
use App\Entity\TransactionCategory;
use App\ImportPreparer\TransactionImportPreparer;
use App\Message\ImportTransactionsTask;
use App\Repository\ImportTransactionsTaskRepository;
use App\Security\TransactionVoter;
use App\Serializer\EntitySerializer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Throwable;

class ImportTransactionsTaskHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ImportTransactionsTaskRepository $repository;
    private EntitySerializer $serializer;
    private TransactionVoter $voter;
    private TransactionImportPreparer $preparer;
    private TransactionDataPersister $persister;
    private ValidatorInterface $validator;

    /**
     * ImportTransactionsTaskHandler constructor.
     *
     * @param ImportTransactionsTaskRepository $repository
     * @param EntitySerializer $serializer
     * @param TransactionVoter $voter
     * @param TransactionImportPreparer $preparer
     * @param TransactionDataPersister $persister
     * @param ValidatorInterface $validator
     */
    public function __construct(
        ImportTransactionsTaskRepository $repository,
        EntitySerializer $serializer,
        TransactionVoter $voter,
        TransactionImportPreparer $preparer,
        TransactionDataPersister $persister,
        ValidatorInterface $validator
    ) {
        $this->repository = $repository;
        $this->serializer = $serializer;
        $this->voter = $voter;
        $this->preparer = $preparer;
        $this->persister = $persister;
        $this->validator = $validator;
    }

    public function __invoke(ImportTransactionsTask $message)
    {
        $taskId = $message->getTaskId();
        $this->logger->info("New task {$taskId}");
        if (!$task = $this->repository->find($taskId)) {
            return;
        }

        $task->onStart();

        $token = $this->createToken($task);

        if (!$this->isGranted($token, TransactionVoter::PRE_CREATE, $task->company)) {
            $this->logger->notice("User {$task->user} has no rights for create transactions.");
            $task->onFinish(['You have no rights for create transactions']);

            return;
        }

        $format = $task->mimeType;
        /** @noinspection PhpUnhandledExceptionInspection */
        $context = $this->serializer->createContext(Transaction::class, 'post', $format);
        $this->logger->debug('Context for creation', $context);

        $data = $this->serializer->decode($task->data, $format, $context);

        if (!\is_array(\reset($data))) {
            $data = [$data];
        }

        $total = 0;
        foreach ($data as $key => &$item) {
            try {
                $item = $this->preparer->prepare($item, ['company' => $task->company]);
                $transaction = $this->createTransaction($item, $format, $context);
                // Replace with subResource, when post operation become available
                $categories = $item['categories'] ?? [];
                $categoriesCount = \count($categories);
                foreach ($categories as $category) {
                    $transactionCategory = new TransactionCategory();
                    $transactionCategory->category = $category;
                    $transactionCategory->amount = $transaction->getAmount() / $categoriesCount;
                    $transaction->addTransactionCategory($transactionCategory);
                }

                $this->validator->validate($transaction, $context);

                if (!$this->isGranted($token, TransactionVoter::CREATE, $transaction)) {
                    throw new UnexpectedValueException('You have no rights for create such transaction.');
                }
                $this->persister->justPersist($transaction);
                ++$task->successfullyImported;
            } catch (ValidationException $exception) {
                $this->handleException($exception, $task);
                $violations = $exception->getConstraintViolationList();
                $errors = [];
                /** @var \Symfony\Component\Validator\ConstraintViolationInterface $error */
                foreach ($violations as $error) {
                    $errors[] = $error->getMessage();
                }

                $task->errors[$key] = \implode('; ', $errors);
            } catch (UnexpectedValueException $exception) {
                $this->handleException($exception, $task);
                $task->errors[$key] = $exception->getMessage();
            } catch (Throwable $exception) {
                $this->handleException($exception, $task);
                $task->errors[$key] = 'Unknown error';
            } finally {
                if (0 === ++$total % 20) {
                    $this->persister->flush();
                }
            }
        }
        unset($item);

        $task->onFinish();

        $this->persister->flush();

        $this->logger->info(
            "Task finished. Imported {$task->successfullyImported} transactions, failed - {$task->failedToImport}"
        );
    }

    /**
     * @param \App\Entity\ImportTransactionsTask|null $task
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken
     */
    private function createToken(\App\Entity\ImportTransactionsTask $task): TokenInterface
    {
        return new UsernamePasswordToken($task->user, null, 'main', $task->user->getRoles());
    }

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param string $attribute
     * @param $subject
     *
     * @return bool
     */
    private function isGranted(TokenInterface $token, string $attribute, $subject): bool
    {
        $voteResult = $this->voter->vote($token, $subject, [$attribute]);

        return TransactionVoter::ACCESS_GRANTED === $voteResult;
    }

    /**
     * @param array $item
     * @param string $format
     * @param array $context
     *
     * @return \App\Entity\Transaction
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    private function createTransaction(array $item, string $format, array $context): Transaction
    {
        $transaction = $this->serializer->denormalize($item, Transaction::class, $format, $context);

        if (!$transaction instanceof Transaction) {
            throw new UnexpectedValueException('Denormalization failed.');
        }

        return $transaction;
    }

    /**
     * @param $exception
     * @param \App\Entity\ImportTransactionsTask|null $task
     */
    private function handleException($exception, \App\Entity\ImportTransactionsTask $task): void
    {
        $this->logger->notice($exception->getMessage(), \explode(PHP_EOL, $exception->getTraceAsString()));
        ++$task->failedToImport;
    }
}

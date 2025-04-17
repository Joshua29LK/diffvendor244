<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Test\Unit\Model\Validator;

use Amasty\Affiliate\Model\Repository\TransactionRepository;
use Amasty\Affiliate\Model\Transaction;
use Amasty\Affiliate\Model\Validator\TransactionStatusValidator;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Ui\Model\BookmarkSearchResults;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see TransactionStatusValidator
 */
class TransactionStatusValidatorTest extends TestCase
{
    public const ORDER_INCREMENT_ID = '00001';

    public const PROGRAM_ID = 1;

    /**
     * @var TransactionStatusValidator
     */
    private $subject;

    /**
     * @var Transaction|MockObject
     */
    private $transactionEntityMock;

    /**
     * @var BookmarkSearchResults|MockObject
     */
    private $searchResultsMock;

    protected function setUp(): void
    {
        $this->transactionEntityMock = $this->createMock(Transaction::class);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->searchResultsMock = $this->createMock(BookmarkSearchResults::class);
        $searchCriteriaBuilderMock = $this->createConfiguredMock(
            SearchCriteriaBuilder::class,
            ['create' => $searchCriteriaMock]
        );
        $transactionRepositoryMock = $this->createConfiguredMock(
            TransactionRepository::class,
            ['getList' => $this->searchResultsMock]
        );

        $this->subject = new TransactionStatusValidator(
            $transactionRepositoryMock,
            $searchCriteriaBuilderMock
        );
    }

    /**
     * @covers TransactionStatusValidator::isCommissionAlreadyAdded
     */
    public function testIsCommissionAlreadyAddedWithTransactionItems(): void
    {
        $this->searchResultsMock->method('getItems')->willReturn([$this->transactionEntityMock]);

        $this->assertEquals(
            true,
            $this->subject->isCommissionAlreadyAdded(self::ORDER_INCREMENT_ID, self::PROGRAM_ID)
        );
    }

    /**
     * @covers TransactionStatusValidator::isCommissionAlreadyAdded
     */
    public function testIsCommissionAlreadyAddedWithoutTransactionItems(): void
    {
        $this->searchResultsMock->method('getItems')->willReturn([]);

        $this->assertEquals(
            false,
            $this->subject->isCommissionAlreadyAdded(self::ORDER_INCREMENT_ID, self::PROGRAM_ID)
        );
    }
}

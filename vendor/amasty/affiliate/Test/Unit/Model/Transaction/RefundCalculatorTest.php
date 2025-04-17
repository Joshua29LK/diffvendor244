<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Test\Unit\Model\Transaction;

use Amasty\Affiliate\Model\ResourceModel\Transaction as TransactionResource;
use Amasty\Affiliate\Model\Transaction;
use Amasty\Affiliate\Model\Transaction\RefundCalculator;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see RefundCalculator
 */
class RefundCalculatorTest extends TestCase
{
    public const TRANSACTION_COMMISSION = 20.00;

    public const ORDER_INCREMENT_ID = '00001';

    public const PROGRAM_ID = 1;

    public const NOT_REFUNDED_AMOUNT = 0.00;

    public const PART_REFUNDED_AMOUNT = 10.00;

    /**
     * @var TransactionResource|MockObject
     */
    private $transactionResourceMock;

    /**
     * @var Transaction|MockObject
     */
    private $transactionMock;

    /**
     * @var RefundCalculator
     */
    private $subject;

    protected function setUp(): void
    {
        $this->transactionResourceMock = $this->createMock(TransactionResource::class);
        $this->transactionMock = $this->createConfiguredMock(
            Transaction::class,
            [
                'getCommission' => self::TRANSACTION_COMMISSION,
                'getOrderIncrementId' => self::ORDER_INCREMENT_ID,
                'getProgramId' => self::PROGRAM_ID,
                ]
        );

        $this->subject = new RefundCalculator(
            $this->transactionResourceMock
        );
    }

    /**
     * @throws LocalizedException
     * @covers RefundCalculator::calculatePartToSubtract
     */
    public function testCalculatePartToSubtractWithNotRefundedAmount(): void
    {
        $this->transactionResourceMock->method('getRefundedSumForTransaction')->willReturn(
            self::NOT_REFUNDED_AMOUNT
        );

        $this->assertEquals(1.0, $this->subject->calculatePartToSubtract($this->transactionMock));
    }

    /**
     * @throws LocalizedException
     * @covers RefundCalculator::calculatePartToSubtract
     */
    public function testCalculatePartToSubtractWithAllRefundedAmount(): void
    {
        $this->transactionResourceMock->method('getRefundedSumForTransaction')->willReturn(
            self::TRANSACTION_COMMISSION
        );

        $this->assertEquals(0.0, $this->subject->calculatePartToSubtract($this->transactionMock));
    }

    /**
     * @throws LocalizedException
     * @covers RefundCalculator::calculatePartToSubtract
     */
    public function testCalculatePartToSubtractWithPartRefundedAmount(): void
    {
        $this->transactionResourceMock->method('getRefundedSumForTransaction')->willReturn(
            self::PART_REFUNDED_AMOUNT
        );

        $this->assertEquals(0.5, $this->subject->calculatePartToSubtract($this->transactionMock));
    }
}

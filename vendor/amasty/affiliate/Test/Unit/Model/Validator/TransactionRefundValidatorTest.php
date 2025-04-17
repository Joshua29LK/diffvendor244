<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Test\Unit\Model\Validator;

use Amasty\Affiliate\Model\ResourceModel\Transaction as TransactionResource;
use Amasty\Affiliate\Model\Transaction;
use Amasty\Affiliate\Model\Validator\TransactionRefundValidator;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see TransactionRefundValidator
 */
class TransactionRefundValidatorTest extends TestCase
{
    public const TRANSACTION_COMMISSION = 10.00;

    public const BIG_REFUND_AMOUNT = 20.00;

    public const SMALL_REFUND_AMOUNT = 5.00;

    public const EMPTY_REFUND_AMOUNT = 0.00;

    public const ORDER_INCREMENT_ID = '00001';

    public const PROGRAM_ID = 1;

    /**
     * @var TransactionResource|MockObject
     */
    private $transactionResourceMock;

    /**
     * @var Transaction|MockObject
     */
    private $transactionMock;

    /**
     * @var TransactionRefundValidator
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

        $this->subject = new TransactionRefundValidator(
            $this->transactionResourceMock
        );
    }

    /**
     * Test if commission less than refunded amount
     *
     * @throws LocalizedException
     * @covers TransactionRefundValidator::isCanSubtractCommission
     */
    public function testIsCanSubtractCommissionLess()
    {
        $this->transactionResourceMock->method('getRefundedSumForTransaction')->willReturn(
            self::BIG_REFUND_AMOUNT
        );
        $this->assertEquals(false, $this->subject->isCanSubtractCommission($this->transactionMock));
    }

    /**
     * Test if commission more than refunded amount
     *
     * @throws LocalizedException
     * @covers TransactionRefundValidator::isCanSubtractCommission
     */
    public function testIsCanSubtractCommissionMore()
    {
        $this->transactionResourceMock->method('getRefundedSumForTransaction')->willReturn(
            self::SMALL_REFUND_AMOUNT
        );
        $this->assertEquals(true, $this->subject->isCanSubtractCommission($this->transactionMock));
    }

    /**
     * Test if refunded amount is empty
     *
     * @throws LocalizedException
     * @covers TransactionRefundValidator::isCanSubtractCommission
     */
    public function testIsCanSubtractCommissionEmpty()
    {
        $this->transactionResourceMock->method('getRefundedSumForTransaction')->willReturn(
            self::EMPTY_REFUND_AMOUNT
        );
        $this->assertEquals(true, $this->subject->isCanSubtractCommission($this->transactionMock));
    }

    /**
     * Test if commission is equal to refunded amount
     *
     * @throws LocalizedException
     * @covers TransactionRefundValidator::isCanSubtractCommission
     */
    public function testIsCanSubtractCommissionEqual()
    {
        $this->transactionResourceMock->method('getRefundedSumForTransaction')->willReturn(
            self::TRANSACTION_COMMISSION
        );
        $this->assertEquals(false, $this->subject->isCanSubtractCommission($this->transactionMock));
    }
}

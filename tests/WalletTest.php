<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Tests;

use Bugfix666\CryptoBalanceWallet\Enums\BlockchainEnum;
use Bugfix666\CryptoBalanceWallet\Enums\OpStateEnum;
use Bugfix666\CryptoBalanceWallet\Enums\OpTypeEnum;
use Bugfix666\CryptoBalanceWallet\Enums\WalletCurrencyEnum;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\InvalidOperationStateException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\InvalidUuidStringException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\NotEnoughFundsException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\ProcessingAmountIsInvalidException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\UnsupportedBlockchainOrCurrencyException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\WalletCurrencyPrecisionNotSetException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\WalletNotFoundException;
use Bugfix666\CryptoBalanceWallet\Models\Operation;
use App\Models\User;
use Bugfix666\CryptoBalanceWallet\Models\Wallet;
use Bugfix666\CryptoBalanceWallet\Repositories\OperationRepository;
use Bugfix666\CryptoBalanceWallet\Repositories\PrecisionRepository;
use Bugfix666\CryptoBalanceWallet\Services\WalletService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Bugfix666\CryptoBalanceWallet\Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    private WalletService $walletService;
    private OperationRepository $operationRepository;
    private PrecisionRepository $precisionRepository;
    private User $user;
    private Wallet $wallet;
    private string $walletUuid;

    protected function setUp(): void
    {
        parent::setUp();
        $this->walletService = app(WalletService::class);
        $this->precisionRepository = app(PrecisionRepository::class);
        $this->operationRepository = app(OperationRepository::class);
        $this->user = User::factory()->create();
        $this->walletUuid = fake()->unique()->uuid();
        $this->wallet = Wallet::query()->create([
            'user_id' => $this->user->id,
            'uuid' => $this->walletUuid,
            'currency' => WalletCurrencyEnum::BTC,
            'blockchain_id' => BlockchainEnum::BTC,
            'amount' => '0',
        ]);
    }

    /**
     * @throws InvalidUuidStringException
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws WalletCurrencyPrecisionNotSetException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletNotFoundException
     */
    public function test_add_balance_completes_immediately(): void
    {
        $amount = '12.345678';
        $operation = $this->walletService->addBalance(
            amount: $amount,
            walletUuid: $this->walletUuid,
            opState: OpStateEnum::OS_COMPLETE
        );

        $this->assertInstanceOf(Operation::class, $operation);
        $this->assertEquals(OpStateEnum::OS_COMPLETE, $operation->op_state);
        $this->assertSame(0, bccomp($operation->amount, $amount));

        $wallet = $this->walletService->findByUuid($this->walletUuid);
        $this->assertSame(0, bccomp($wallet->amount, $amount));
    }

    /**
     * @throws InvalidUuidStringException
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws WalletCurrencyPrecisionNotSetException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletNotFoundException
     */
    public function test_add_balance_creates_hold_operation(): void
    {
        $amount = '10.00';
        $operation = $this->walletService->addBalance(
            amount: $amount,
            walletUuid: $this->walletUuid,
            opState: OpStateEnum::OS_HOLD
        );

        $this->assertInstanceOf(Operation::class, $operation);
        $this->assertEquals(OpStateEnum::OS_HOLD, $operation->op_state);
        $this->assertSame(0, bccomp($operation->amount, $amount));

        $wallet = $this->walletService->findByUuid($this->walletUuid);
        $this->assertSame(0, bccomp($wallet->amount, $amount));
    }

    /**
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletNotFoundException
     * @throws InvalidUuidStringException
     * @throws WalletCurrencyPrecisionNotSetException
     */
    public function test_complete_hold_credit_operation(): void
    {
        $amount = '5.50';
        $holdOp = $this->walletService->addBalance(
            amount: $amount,
            walletUuid: $this->walletUuid,
            opState: OpStateEnum::OS_HOLD
        );

        $completedOp = $this->walletService->addBalance(
            amount: $amount,
            walletUuid: $this->walletUuid,
            opState: OpStateEnum::OS_COMPLETE,
            operation: $holdOp
        );

        $this->assertEquals(OpStateEnum::OS_COMPLETE, $completedOp->op_state);
        $wallet = $this->walletService->findByUuid($this->walletUuid);
        $this->assertSame(0, bccomp($wallet->amount, $amount));
    }

    /**
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletNotFoundException
     * @throws InvalidUuidStringException
     * @throws WalletCurrencyPrecisionNotSetException
     */
    public function test_cancel_hold_credit_operation(): void
    {
        $amount = '7.77';
        $holdOp = $this->walletService->addBalance(
            amount: $amount,
            walletUuid: $this->walletUuid,
            opState: OpStateEnum::OS_HOLD
        );

        $walletBefore = $this->walletService->findByUuid($this->walletUuid);
        $this->assertSame(0, bccomp($walletBefore->amount, $amount));

        $canceledOp = $this->walletService->addBalance(
            amount: $amount,
            walletUuid: $this->walletUuid,
            opState: OpStateEnum::OS_CANCELED,
            operation: $holdOp
        );

        $this->assertEquals(OpStateEnum::OS_CANCELED, $canceledOp->op_state);
        $walletAfter = $this->walletService->findByUuid($this->walletUuid);
        $this->assertSame(0, bccomp($walletAfter->amount, '0'));
    }

    /**
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletNotFoundException
     * @throws InvalidUuidStringException
     * @throws WalletCurrencyPrecisionNotSetException
     */
    public function test_sub_balance_completes_immediately(): void
    {
        $initial = '20.00';
        $this->walletService->addBalance($initial, $this->walletUuid, OpStateEnum::OS_COMPLETE);

        $amount = '12.345678';
        $operation = $this->walletService->subBalance(
            amount: $amount,
            walletUuid: $this->walletUuid,
            opState: OpStateEnum::OS_COMPLETE
        );

        $this->assertInstanceOf(Operation::class, $operation);
        $this->assertEquals(OpStateEnum::OS_COMPLETE, $operation->op_state);
        $this->assertSame(0, bccomp($operation->amount, '-' . $amount));

        $wallet = $this->walletService->findByUuid($this->walletUuid);
        $expected = bcsub($initial, $amount);
        $this->assertSame(0, bccomp($wallet->amount, $expected));
    }

    /**
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletNotFoundException
     * @throws InvalidUuidStringException
     * @throws WalletCurrencyPrecisionNotSetException
     */
    public function test_sub_balance_creates_hold_operation(): void
    {
        $initial = '15.00';
        $this->walletService->addBalance($initial, $this->walletUuid, OpStateEnum::OS_COMPLETE);

        $amount = '8.25';
        $operation = $this->walletService->subBalance(
            amount: $amount,
            walletUuid: $this->walletUuid,
            opState: OpStateEnum::OS_HOLD
        );

        $this->assertEquals(OpStateEnum::OS_HOLD, $operation->op_state);
        $wallet = $this->walletService->findByUuid($this->walletUuid);
        $expected = bcsub($initial, $amount);
        $this->assertSame(0, bccomp($wallet->amount, $expected));
    }

    /**
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletNotFoundException
     * @throws InvalidUuidStringException
     * @throws WalletCurrencyPrecisionNotSetException
     */
    public function test_complete_hold_debit_operation(): void
    {
        $initial = '30.00';
        $this->walletService->addBalance($initial, $this->walletUuid, OpStateEnum::OS_COMPLETE);

        $amount = '10.00';
        $holdOp = $this->walletService->subBalance(
            amount: $amount,
            walletUuid: $this->walletUuid,
            opState: OpStateEnum::OS_HOLD
        );

        $walletBefore = $this->walletService->findByUuid($this->walletUuid);
        $this->assertSame(0, bccomp($walletBefore->amount, bcsub($initial, $amount)));

        $completedOp = $this->walletService->subBalance(
            amount: $amount,
            walletUuid: $this->walletUuid,
            opState: OpStateEnum::OS_COMPLETE,
            operation: $holdOp
        );

        $this->assertEquals(OpStateEnum::OS_COMPLETE, $completedOp->op_state);
        $walletAfter = $this->walletService->findByUuid($this->walletUuid);
        $this->assertSame(0, bccomp($walletAfter->amount, bcsub($initial, $amount)));
    }

    /**
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletNotFoundException
     * @throws InvalidUuidStringException
     * @throws WalletCurrencyPrecisionNotSetException
     */
    public function test_cancel_hold_debit_operation(): void
    {
        $initial = '40.00';
        $this->walletService->addBalance($initial, $this->walletUuid, OpStateEnum::OS_COMPLETE);

        $amount = '15.00';
        $holdOp = $this->walletService->subBalance(
            amount: $amount,
            walletUuid: $this->walletUuid,
            opState: OpStateEnum::OS_HOLD
        );

        $walletBefore = $this->walletService->findByUuid($this->walletUuid);
        $this->assertSame(0, bccomp($walletBefore->amount, bcsub($initial, $amount)));

        $canceledOp = $this->walletService->subBalance(
            amount: $amount,
            walletUuid: $this->walletUuid,
            opState: OpStateEnum::OS_CANCELED,
            operation: $holdOp
        );

        $this->assertEquals(OpStateEnum::OS_CANCELED, $canceledOp->op_state);
        $walletAfter = $this->walletService->findByUuid($this->walletUuid);
        $this->assertSame(0, bccomp($walletAfter->amount, $initial));
    }

    /**
     * @throws InvalidUuidStringException
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws WalletCurrencyPrecisionNotSetException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletNotFoundException
     */
    public function test_sub_balance_throws_not_enough_funds(): void
    {
        $this->expectException(NotEnoughFundsException::class);
        $this->walletService->subBalance('100.00', $this->walletUuid, OpStateEnum::OS_COMPLETE);
    }

    /**
     * @throws InvalidUuidStringException
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletCurrencyPrecisionNotSetException
     * @throws WalletNotFoundException
     */
    public function test_sub_balance_throws_not_enough_funds_even_for_hold(): void
    {
        $this->expectException(NotEnoughFundsException::class);
        $this->walletService->subBalance('100.00', $this->walletUuid, OpStateEnum::OS_HOLD);
    }

    /**
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws WalletCurrencyPrecisionNotSetException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletNotFoundException
     */
    public function test_add_balance_invalid_uuid(): void
    {
        $this->expectException(InvalidUuidStringException::class);
        $this->walletService->addBalance('10.00', 'not-a-uuid');
    }

    /**
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws WalletCurrencyPrecisionNotSetException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletNotFoundException
     */
    public function test_sub_balance_invalid_uuid(): void
    {
        $this->expectException(InvalidUuidStringException::class);
        $this->walletService->subBalance('10.00', 'not-a-uuid');
    }

    /**
     * @throws InvalidUuidStringException
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws WalletCurrencyPrecisionNotSetException
     * @throws WalletNotFoundException
     */
    public function test_add_balance_zero_amount_not_allowed(): void
    {
        $this->expectException(ProcessingAmountIsInvalidException::class);
        $this->walletService->addBalance('0', $this->walletUuid);
    }

    /**
     * @throws InvalidUuidStringException
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws WalletCurrencyPrecisionNotSetException
     * @throws WalletNotFoundException
     */
    public function test_add_balance_negative_amount_not_allowed(): void
    {
        $this->expectException(ProcessingAmountIsInvalidException::class);
        $this->walletService->addBalance('-5.00', $this->walletUuid);
    }

    /**
     * @throws InvalidUuidStringException
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws WalletCurrencyPrecisionNotSetException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletNotFoundException
     */
    public function test_cancel_non_existent_operation_throws_exception(): void
    {
        $fakeOperation = new Operation();
        $fakeOperation->id = 999999;

        $this->expectException(ModelNotFoundException::class);
        $this->walletService->addBalance(
            amount: '10.00',
            walletUuid: $this->walletUuid,
            opState: OpStateEnum::OS_CANCELED,
            operation: $fakeOperation
        );
    }

    /**
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletNotFoundException
     * @throws InvalidUuidStringException
     * @throws WalletCurrencyPrecisionNotSetException
     */
    public function test_cancel_operation_that_is_not_in_hold_state_throws_exception(): void
    {
        $completedOp = $this->walletService->addBalance('10.00', $this->walletUuid, OpStateEnum::OS_COMPLETE);

        $this->expectException(InvalidOperationStateException::class);
        $this->walletService->addBalance('10.00', $this->walletUuid, OpStateEnum::OS_CANCELED, $completedOp);
    }

    public function test_user_successful_response(): void
    {
        $this->seed();
        $response = $this->get('/api/v1/users');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                0 => [
                    'wallet' => ['uuid', 'amount', 'currency'],
                    'email',
                    'name',
                    'user_uuid',
                ]
            ]
        ]);
    }

    /**
     * @throws InvalidUuidStringException
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletCurrencyPrecisionNotSetException
     * @throws WalletNotFoundException
     */
    public function test_operation_successful_response(): void
    {
        $precision = $this->precisionRepository->getPrecisionByWallet($this->wallet);
        $amount = $this->operationRepository->prepareValue('10.00', $precision->getPrecision());
        $operation = $this->walletService->addBalance(
            amount: $amount,
            walletUuid: $this->walletUuid,
            opState: OpStateEnum::OS_COMPLETE
        );
        $this->assertNotNull($operation);

        $response = $this->post('/api/v1/operations', [
            'user_uuid' => $this->user->uuid
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                0 => ['amount', 'created_at', 'currency', 'op_type', 'uuid']
            ]
        ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($amount, $data[0]['amount']);
        $this->assertEquals(OpTypeEnum::OP_TYPE_CREDIT->value, $data[0]['op_type']);
    }

    public function test_operation_unsuccessful_response(): void
    {
        $response = $this->post('/api/v1/operations', [
            'user_uuid' => 'ba644bb1-1111-3333-2222-d13c49695dc7'
        ]);
        $response->assertStatus(400);
    }

    public function test_wallet_successful_response(): void
    {
        $this->seed();
        $userResponse = $this->get('/api/v1/users');
        $walletUuid = $userResponse->json('data.0.wallet.uuid');

        $response = $this->post('/api/v1/wallet/add-balance', [
            'wallet_uuid' => $walletUuid,
            'amount' => '123.456'
        ]);
        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertArrayHasKey('id', $response->json('data'));
    }
}

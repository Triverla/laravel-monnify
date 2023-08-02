<?php

namespace Triverla\LaravelMonnify;

use Triverla\LaravelMonnify\Exceptions\FailedRequestException;
use Triverla\LaravelMonnify\Helpers\BankAccount;
use Triverla\LaravelMonnify\Helpers\OnFailureValidate;
use Triverla\LaravelMonnify\Helpers\Tranx;
use Triverla\LaravelMonnify\Helpers\TranxList;

abstract class Disbursement
{

    private Monnify $monnify;
    private $config;

    public function __construct(Monnify $monnify, $config)
    {
        $this->config = $config;
        $this->monnify = $monnify;
    }

    /**
     * @throws FailedRequestException
     */
    public function initiateTransferSingle(float $amount, string $reference, string $narration, BankAccount $bankAccount, string $currencyCode = null)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v2}/disbursements/single";
        $response = $this->monnify->withOAuth()->post($endpoint, [
            "amount" => $amount,
            "reference" => trim($reference),
            "narration" => trim($narration),
            "destinationBankCode" => $bankAccount->getBankCode(),
            "destinationAccountNumber" => $bankAccount->getAccountNumber(),
            "currency" => $currencyCode ?? $this->config['default_currency_code'],
            "sourceAccountNumber" => $this->config['source_account_number']
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }


    /**
     * @throws FailedRequestException
     */
    public function initiateSingleTransferWithTransaction(Tranx $transaction)
    {
        return $this->initiateTransferSingle($transaction->getAmount(), $transaction->getReference(), $transaction->getNarration(), $transaction->getBankAccount(), $transaction->getCurrencyCode());
    }

    /**
     * @throws FailedRequestException
     */
    public function initiateTransferBulk(string $title, string $batchReference, string $narration, OnFailureValidate $onFailureValidate, int $notificationInterval, TranxList $transactionList)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v2}/disbursements/batch";
        $response = $this->monnify->withOAuth()->post($endpoint, [
            "title" => $title,
            "batchReference" => trim($batchReference),
            "narration" => trim($narration),
            "sourceAccountNumber" => $this->config['source_account_number'],
            "onValidationFailure" => "$onFailureValidate",
            "notificationInterval" => $notificationInterval,
            "transactionList" => $transactionList->toArray()
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @throws FailedRequestException
     */
    private function authorizeTransfer2FA(string $authorizationCode, string $reference, string $path)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v2}/disbursements/$path/validate-otp";
        $response = $this->monnify->withOAuth()->post($endpoint, [
            "reference" => trim($reference),
            "authorizationCode" => $authorizationCode,
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @throws FailedRequestException
     */
    public function authorizeSingleTransfer2FA(string $authorizationCode, string $reference)
    {
        return $this->authorizeTransfer2FA($authorizationCode, $reference, 'single');
    }

    /**
     * @throws FailedRequestException
     */
    public function authorizeBulkTransfer2FA(string $authorizationCode, string $reference)
    {
        return $this->authorizeTransfer2FA($authorizationCode, $reference, 'batch');
    }

    /**
     * @throws FailedRequestException
     */
    private function getTransferDetails(string $reference, string $path)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/disbursements/$path/summary?reference=$reference";
        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @throws FailedRequestException
     */
    public function getSingleTransferDetails(string $reference)
    {
        return $this->getTransferDetails($reference, 'single');
    }

    /**
     * @throws FailedRequestException
     */
    public function getBulkTransferDetails(string $batchReference)
    {
        return $this->getTransferDetails($batchReference, 'batch');
    }


    /**
     * @throws FailedRequestException
     */
    public function getBulkTransferTransactions(string $batchReference, int $pageNo = 0, int $pageSize = 10)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v2}/disbursements/bulk/$batchReference/transactions?pageNo=$pageNo&pageSize=$pageSize";
        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @throws FailedRequestException
     */
    private function listAllTransfers(string $path, int $pageNo = 0, int $pageSize = 10)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v2}/disbursements/$path/transactions?pageNo=$pageNo&pageSize=$pageSize";
        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @throws FailedRequestException
     */
    public function getSingleTransferTransactions(int $pageNo = 0, int $pageSize = 10)
    {
        return $this->listAllTransfers('single', $pageNo, $pageSize);
    }

    /**
     * @throws FailedRequestException
     */
    public function getAllBulkTransferTransactions(int $pageNo = 0, int $pageSize = 10)
    {
        return $this->listAllTransfers('bulk', $pageNo, $pageSize);
    }

    /**
     * @throws FailedRequestException
     */
    public function getWalletBalance()
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v2}/disbursements/wallet-balance?accountNumber={$this->config['source_account_number']}";
        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @throws FailedRequestException
     */
    public function resendOTP(string $reference)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v2}/disbursements/single/resend-otp";
        $response = $this->monnify->withOAuth()->post($endpoint, [
            'reference' => $reference
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @throws FailedRequestException
     */
    public function searchTransferTransactions(int $pageNo = 0, int $pageSize = 20)
    {
        $sourceAccountNumber = $this->config['source_account_number'];
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v2}/disbursements/search-transactions?sourceAccountNumber=$sourceAccountNumber&pageNo=$pageNo&pageSize=$pageSize";
        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }
}

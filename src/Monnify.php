<?php

namespace Triverla\LaravelMonnify;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Triverla\LaravelMonnify\Exceptions\FailedRequestException;

class Monnify
{

    public string $baseUrl;
    public string $v1 = "/api/v1";
    public string $v2 = "/api/v2";
    private $config;
    private $client;

    private string $oAuthToken = '';
    private int $oAuthTokenExpirationTime = 0;

    private $banks;
    private $reservedAccount;
    private $disbursement;
    private $invoice;
    private $subAccount;
    private $transaction;
    private $payment;
    private $verify;
    private $refund;
    private $limitProfile;
    private $settlement;

    public function __construct(string $baseUrl, $config)
    {
        $this->baseUrl = $baseUrl;
        $this->config = $config;
    }

    /**
     * @return PendingRequest
     */
    public function withBasicAuth(): PendingRequest
    {
        $this->client = Http::withBasicAuth($this->config['api_key'], $this->config['secret_key'])->asJson();

        return $this->client;
    }

    /**
     * @throws FailedRequestException
     */
    private function getAuthToken(): void
    {
        $endpoint = "{$this->baseUrl}{$this->v1}/auth/login";
        $response = $this->withBasicAuth()->post($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        $this->oAuthToken = $result->responseBody->accessToken;
        $this->oAuthTokenExpirationTime = ((time() + $result->responseBody->expiresIn) - 60);

    }

    /**
     * @param string $data
     * @return bool|string
     */
    public function computeRequestValidationHash(string $data): bool|string
    {
        $clientSK = $this->config['secret_key'];
        return hash_hmac('sha512', $data, $clientSK);
    }

    /**
     * @throws FailedRequestException
     */
    public function withOAuth(): PendingRequest
    {
        if (time() >= $this->oAuthTokenExpirationTime) {
            $this->getAuthToken();
            $this->client = Http::withToken($this->oAuthToken);
        }

        return $this->client;
    }

    /**
     * @return Bank
     */
    public function Bank(): Bank
    {
        if (is_null($this->banks))
            $this->banks = new class($this, $this->config) extends Bank {
            };
        return $this->banks;
    }

    /**
     * @return ReservedAccount
     */
    public function ReservedAccount(): ReservedAccount
    {
        if (is_null($this->reservedAccount))
            $this->reservedAccount = new class($this, $this->config) extends ReservedAccount {
            };
        return $this->reservedAccount;
    }

    /**
     * @return Disbursement
     */
    public function Disbursement(): Disbursement
    {
        if (is_null($this->disbursement))
            $this->disbursement = new class($this, $this->config) extends Disbursement {
            };
        return $this->disbursement;
    }

    /**
     * @return Invoice
     */
    public function Invoice(): Invoice
    {
        if (is_null($this->invoice))
            $this->invoice = new class($this, $this->config) extends Invoice {
            };
        return $this->invoice;
    }

    /**
     * @return SubAccount
     */
    public function SubAccount(): SubAccount
    {
        if (is_null($this->subAccount))
            $this->subAccount = new class($this, $this->config) extends SubAccount {
            };
        return $this->subAccount;
    }

    /**
     * @return Transaction
     */
    public function Transaction(): Transaction
    {
        if (is_null($this->transaction))
            $this->transaction = new class($this, $this->config) extends Transaction {
            };
        return $this->transaction;
    }

    /**
     * @return Payment
     */
    public function Payment(): Payment
    {
        if (is_null($this->payment))
            $this->payment= new class($this, $this->config) extends Payment {
            };
        return $this->payment;
    }

    /**
     * @return Verify
     */
    public function Verify(): Verify
    {
        if (is_null($this->verify))
            $this->verify = new class($this, $this->config) extends Verify {
            };
        return $this->verify;
    }

    /**
     * @return Refund
     */
    public function Refund(): Refund
    {
        if (is_null($this->refund))
            $this->refund = new class($this, $this->config) extends Refund {
            };
        return $this->refund;
    }

    /**
     * @return LimitProfile
     */
    public function LimitProfile(): LimitProfile
    {
        if (is_null($this->limitProfile))
            $this->limitProfile = new class($this, $this->config) extends LimitProfile {
            };
        return $this->limitProfile;
    }

    /**
     * @return Settlement
     */
    public function Settlement(): Settlement
    {
        if (is_null($this->settlement))
            $this->settlement = new class($this, $this->config) extends Settlement {
            };
        return $this->settlement;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function genTransactionReference(): string
    {
        $randomString = bin2hex(random_bytes(4));
        return 'MN' . $randomString;
    }

}

<?php

namespace Triverla\LaravelMonnify\Helpers;


class ReservedAccountSplit
{

    private string $subAccountCode;
    private float $feePercentage;
    private float $splitPercentage;
    private bool $feeBearer;

    public function __construct(string $subAccountCode, float $feePercentage, bool $feeBearer, float $splitPercentage)
    {
        $this->subAccountCode = trim($subAccountCode);
        $this->feePercentage = $feePercentage;
        $this->feeBearer = $feeBearer;
        $this->splitPercentage = $splitPercentage;
    }

    public function toArray(): array
    {
        return [
            "subAccountCode" => $this->subAccountCode,
            "feePercentage" => $this->feePercentage,
            "splitPercentage" => $this->splitPercentage,
            "feeBearer" => $this->feeBearer
        ];
    }
}

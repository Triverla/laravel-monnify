<?php

namespace Triverla\LaravelMonnify\Helpers;


class IncomeSplitConfig
{

    private array $incomeSplitConfig = [];

    public function __construct(CardDetails ...$reservedAccountSplits)
    {
        foreach ($reservedAccountSplits as $split) {
            $this->incomeSplitConfig[] = $split->toArray();
        }
    }

    public function toArray(): array
    {
        return $this->incomeSplitConfig;
    }
}

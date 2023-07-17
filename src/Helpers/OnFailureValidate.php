<?php

namespace Triverla\LaravelMonnify\Helpers;


class OnFailureValidate
{
    private string $onFailureValidate;

    private static array $cache = [];
    private const CONTINUE = "CONTINUE";
    private const BREAK = "BREAK";

    /**
     * OnFailureValidate constructor.
     * @param string $onFailureValidate
     */
    private function __construct(string $onFailureValidate)
    {
        $this->onFailureValidate = $onFailureValidate;
    }

    public static function continue()
    {
        if (!key_exists(self::CONTINUE, self::$cache))
            self::$cache[self::CONTINUE] = new OnFailureValidate( self::CONTINUE);

        return self::$cache[self::CONTINUE];
    }

    public static function break()
    {
        if (!key_exists(self::BREAK, self::$cache))
            self::$cache[self::BREAK] = new OnFailureValidate( self::BREAK);

        return self::$cache[self::BREAK];
    }

    public static function findOnFailureValidate(string $onFailureValidate)
    {
        return match (strtoupper($onFailureValidate)) {
            self::BREAK => self::BREAK(),
            self::CONTINUE => self::CONTINUE(),
            default => null,
        };
    }

    public function __toString(): string
    {
        return $this->onFailureValidate;
    }

}

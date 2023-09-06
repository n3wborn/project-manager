<?php

namespace App\Helper;

use Psr\Log\LoggerInterface;

final class Randomizer
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public static function generateSlug(): string
    {
        return self::generateRandomBytes();
    }

    public static function generateRandomBytes(int $nbytes = 16): string
    {
        try {
            $result = bin2hex(random_bytes($nbytes));

            version_compare(phpversion(), '8.2.0', '>=')
            && $result = bin2hex((new \Random\Randomizer())->getBytes($nbytes));
        } catch (\Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
            $result = uniqid(uniqid('', true), true);
        }

        return $result;
    }
}

<?php

namespace App\Helper;

use Psr\Log\LoggerInterface;

final class ExceptionLogger
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function logAndTrace(\Throwable $exception, string $message = null): void
    {
        $message && $this->logger->error($message);
        $this->logger->error($exception->getMessage());
        $this->logger->debug($exception->getTraceAsString());
    }

    public function logCriticalAndTrace(\Throwable $exception, string $message = null): void
    {
        $message && $this->logger->critical($message);
        $this->logger->critical($exception->getMessage());
        $this->logger->critical($exception->getTraceAsString());
    }

    public function log(\Throwable $exception, string $message = null): void
    {
        $message && $this->logger->error($message);
        $this->logger->error($exception->getMessage());
    }

    public function logNotice(\Throwable $exception, string $message = null): void
    {
        $message && $this->logger->notice($message);
        $this->logger->notice($exception->getMessage());
    }
}

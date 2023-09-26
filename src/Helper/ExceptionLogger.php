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
        $message && $this->logger->error($message, ['exception' => $exception]);
        $this->logger->error($exception->getMessage(), ['exception' => $exception]);
        $this->logger->debug($exception->getTraceAsString(), ['exception' => $exception]);
    }

    public function logCriticalAndTrace(\Throwable $exception, string $message = null): void
    {
        $message && $this->logger->critical($message, ['exception' => $exception]);
        $this->logger->critical($exception->getMessage(), ['exception' => $exception]);
        $this->logger->critical($exception->getTraceAsString(), ['exception' => $exception]);
    }

    public function log(\Throwable $exception, string $message = null): void
    {
        $message && $this->logger->error($message, ['exception' => $exception]);
        $this->logger->error($exception->getMessage(), ['exception' => $exception]);
    }

    public function logNotice(\Throwable $exception, string $message = null): void
    {
        $message && $this->logger->notice($message, ['exception' => $exception]);
        $this->logger->notice($exception->getMessage(), ['exception' => $exception]);
    }
}

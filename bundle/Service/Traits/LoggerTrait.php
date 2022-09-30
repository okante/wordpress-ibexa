<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service\Traits;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

trait LoggerTrait
{
    private LoggerInterface $logger;

    /**
     * @required
     */
    public function setLogger(LoggerInterface $wordPressIbexaLogger)
    {
        $this->logger = $wordPressIbexaLogger;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    private function log(string $message, array $context = [], string $level = LogLevel::DEBUG): void
    {
        if (null !== $this->getLogger()) {
            if (in_array($level, [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR], true)) {
                $this->getLogger()->error($message, $context);

                return;
            }
            if (in_array($level, [LogLevel::WARNING, LogLevel::NOTICE, LogLevel::INFO], true)) {
                $this->getLogger()->info($message, $context);

                return;
            }
            $this->getLogger()->debug($message, $context);
        }
    }

    public function debug(string $message, array $context = [])
    {
        $this->log($message, $context);
    }

    public function info(string $message, array $context = [])
    {
        $this->log($message, $context, LogLevel::INFO);
    }

    public function error(string $message, array $context = [])
    {
        $this->log($message, $context, LogLevel::ERROR);
    }
}

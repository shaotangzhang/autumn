<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/06/2024
 */

namespace Autumn\Logging;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class ConsoleLogger implements LoggerInterface
{
    /**
     * @var array<string, int> Mapping of log levels to numerical values for filtering
     */
    private array $logLevels = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT => 1,
        LogLevel::CRITICAL => 2,
        LogLevel::ERROR => 3,
        LogLevel::WARNING => 4,
        LogLevel::NOTICE => 5,
        LogLevel::INFO => 6,
        LogLevel::DEBUG => 7,
    ];

    /**
     * @var int The current log level threshold
     */
    private int $currentLogLevel;

    /**
     * Constructor to initialize the logger with a log level.
     *
     * @param string $logLevel The minimum log level for logging.
     */
    public function __construct(string $logLevel = LogLevel::DEBUG)
    {
        $this->currentLogLevel = $this->logLevels[$logLevel] ?? $this->logLevels[LogLevel::DEBUG];
    }

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param string $level The log level.
     * @param \Stringable|string $message The log message.
     * @param array $context The log context.
     */
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        if ($this->logLevels[$level] > $this->currentLogLevel) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextString = $context ? json_encode($context) : '';
        $formattedMessage = sprintf(
            "[%s] %s: %s %s\n",
            $timestamp,
            strtoupper($level),
            $message,
            $contextString
        );

        static $stdOut;

        $stdOut ??= defined('STDOUT') ? \STDOUT : fopen('php://stdout', 'w');

        fwrite($stdOut, $formattedMessage);
    }
}

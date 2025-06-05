<?php
namespace App\Utils;

class Logger
{
    private $logDir;
    private $logFile;

    public function __construct()
    {
        $this->logDir = dirname(__DIR__, 2) . '/logs';
        $this->logFile = $this->logDir . '/app.log';

        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        $logMessage = "[$timestamp] $level: $message $contextStr\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    public function error(string $message, array $context = []): void
    {
        if (isset($context['exception']) && $context['exception'] instanceof \Exception) {
            $exception = $context['exception'];
            $message .= " (Code: {$exception->getCode()}) in {$exception->getFile()} on line {$exception->getLine()}";
            $context['trace'] = $exception->getTraceAsString();
        }
        $this->log('ERROR', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function access(?int $userId, string $action, string $status): void
    {
        $context = ['user_id' => $userId, 'action' => $action, 'status' => $status];
        $this->log('ACCESS', "Attempted action: $action", $context);
    }
}
?>
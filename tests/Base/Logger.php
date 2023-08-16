<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Db\Tests\Base;

use Psr\Log\AbstractLogger;
use Stringable;

class Logger extends AbstractLogger
{
    /**
     * @var string[]
     */
    private array $messages = [];

    public function log($level, string|Stringable $message, array $context = []): void
    {
        if (getenv('DB_LOG_QUERIES') !== false) {
            var_dump($message);
        }

        $this->messages[] = $message;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}

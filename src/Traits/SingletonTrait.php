<?php

namespace WpPluginBoilerPlate\Traits;

trait SingletonTrait
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    private function __clone(): void
    {
    }

    public function __wakeup(): void
    {
        throw new \LogicException('Singleton instances cannot be unserialized.');
    }
}

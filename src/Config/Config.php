<?php

declare(strict_types=1);

namespace ClaudeCli\Config;

final class Config
{
    public function __construct(
        private string $model,
        private string $apiKey
    ) {}

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setKey(string $key): void
    {
        $this->apiKey = $key;
    }

    public function getKey(): string
    {
        return $this->apiKey;
    }
}

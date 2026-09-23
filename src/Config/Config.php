<?php

declare(strict_types=1);

namespace ClaudeCli\Config;

/**
 * Immutable application configuration.
 *
 * @phpstan-type ModelName non-empty-string
 * @phpstan-type ApiKey non-empty-string
 */
final readonly class Config
{
    /**
     * @param ModelName $model
     * @param ApiKey    $apiKey
     */
    public function __construct(
        private string $model,
        private string $apiKey,
    ) {}

    /**
     * Get the configured model name.
     *
     * @return ModelName
     */
    public function model(): string
    {
        return $this->model;
    }

    /**
     * Get the API key.
     *
     * Keep the returned value out of logs, exceptions, dumps, and user output.
     *
     * @return ApiKey
     */
    public function apiKey(): string
    {
        return $this->apiKey;
    }
}

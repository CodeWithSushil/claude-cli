<?php

declare(strict_types=1);

namespace ClaudeCli;

use Anthropic\Client;
use ClaudeCli\Config\Config;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Main application service for communicating with Anthropic's API.
 *
 * Security considerations:
 * - API credentials are never exposed through public properties.
 * - Environment variables take precedence over configuration files.
 * - Empty credentials are rejected.
 * - User input is validated before being sent to the API.
 * - API exceptions are not silently swallowed.
 * - API responses are normalized to plain text.
 * - No credentials or full API responses are logged.
 */
final class Application
{
    /**
     * Application configuration.
     */
    private readonly Config $config;

    /**
     * Anthropic API client.
     */
    private readonly Client $client;

    /**
     * Create the application.
     *
     * The API key is loaded from the ANTHROPIC_API_KEY environment
     * variable first. If it is not available, the configured key
     * from Config is used.
     *
     * @param  Config|null  $config  Optional application configuration.
     *
     * @throws RuntimeException When no API key is configured.
     */
    public function __construct(?Config $config = null)
    {
        $this->config = $config ?? new Config;

        $apiKey = getenv('ANTHROPIC_API_KEY');

        if ($apiKey === false || trim($apiKey) === '') {
            $apiKey = $this->config->apiKey();
        }

        $apiKey = trim($apiKey);

        if ($apiKey === '') {
            throw new RuntimeException('Anthropic API key is not configured.');
        }

        /*
         * Do not log or expose $apiKey.
         *
         * The SDK uses named arguments intentionally. This also
         * improves compatibility with future SDK versions.
         */
        $this->client = new Client(
            apiKey: $apiKey,
        );
    }

    /**
     * Get the configured API key.
     *
     * Note:
     * Returning secrets from application code should generally be avoided.
     * This method is kept for compatibility with the original API.
     *
     * @return string Configured API key.
     */
    public function getKey(): string
    {
        return $this->config->apiKey();
    }

    /**
     * Send a message to Claude.
     *
     * @param  string  $message  User message.
     * @return string Claude's textual response.
     *
     * @throws InvalidArgumentException If the message is empty.
     * @throws RuntimeException If the API request fails or produces
     *                          an unexpected response.
     */
    public function message(string $message): string
    {
        $message = trim($message);

        if ($message === '') {
            throw new InvalidArgumentException(
                'Message cannot be empty.'
            );
        }

        try {
            $response = $this->client->messages->create(
                maxTokens: 1024,
                messages: [
                    [
                        'role' => 'user',
                        'content' => $message,
                    ],
                ],
                model: $this->config->model(),
            );
        } catch (Throwable $exception) {
            /*
             * Do not expose internal SDK details, API keys,
             * request data, or stack traces to CLI users.
             *
             * The original exception should be logged by the
             * application's logging layer if required.
             */
            throw new RuntimeException(
                'Unable to communicate with the Anthropic API.',
                previous: $exception,
            );
        }

        return $this->extractText($response);
    }

    /**
     * Extract text content from an Anthropic response.
     *
     * The response may contain multiple content blocks, so all
     * text blocks are concatenated.
     *
     * @param  object  $response  Anthropic API response.
     * @return string Response text.
     *
     * @throws RuntimeException When the response contains no text.
     */
    private function extractText(object $response): string
    {
        if (! isset($response->content) || ! is_iterable($response->content)) {
            throw new RuntimeException(
                'Anthropic API returned an invalid response.'
            );
        }

        $text = '';

        foreach ($response->content as $content) {
            if (isset($content->text) && is_string($content->text)) {
                $text .= $content->text;
            }
        }

        if ($text === '') {
            throw new RuntimeException(
                'Anthropic API returned an empty response.'
            );
        }

        return $text;
    }
}

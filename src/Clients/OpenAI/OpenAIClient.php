<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Clients\OpenAI;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Oziri\LlmSuite\Contracts\ChatClient;
use Oziri\LlmSuite\Contracts\ImageClient;
use Oziri\LlmSuite\Exceptions\ProviderRequestException;
use Oziri\LlmSuite\Support\ChatResponse;
use Oziri\LlmSuite\Support\ImageResponse;
use Oziri\LlmSuite\Support\TokenUsage;

/**
 * OpenAI API client implementation.
 * Supports both chat completions and image generation.
 */
class OpenAIClient implements ChatClient, ImageClient
{
    /**
     * Default base URL for OpenAI API.
     */
    protected const DEFAULT_BASE_URL = 'https://api.openai.com/v1';

    /**
     * Default chat model.
     */
    protected const DEFAULT_CHAT_MODEL = 'gpt-4.1-mini';

    /**
     * Default image model.
     */
    protected const DEFAULT_IMAGE_MODEL = 'dall-e-3';

    /**
     * Default image size.
     */
    protected const DEFAULT_IMAGE_SIZE = '1024x1024';

    /**
     * API endpoint for chat completions.
     */
    protected const ENDPOINT_CHAT = '/chat/completions';

    /**
     * API endpoint for image generation.
     */
    protected const ENDPOINT_IMAGES = '/images/generations';

    /**
     * API endpoint for listing models.
     */
    protected const ENDPOINT_MODELS = '/models';

    /**
     * Error message for failed chat requests.
     */
    protected const ERROR_CHAT_FAILED = 'OpenAI chat request failed';

    /**
     * Error message for failed image requests.
     */
    protected const ERROR_IMAGE_FAILED = 'OpenAI image request failed';

    public function __construct(
        protected array $config
    ) {}

    /**
     * Get a configured HTTP client for OpenAI API requests.
     */
    protected function http(): PendingRequest
    {
        return Http::withToken($this->config['api_key'])
            ->baseUrl($this->config['base_url'] ?? self::DEFAULT_BASE_URL)
            ->acceptJson()
            ->asJson();
    }

    /**
     * Send a chat message to OpenAI.
     */
    public function chat(string $prompt, array $options = []): ChatResponse
    {
        $startTime = microtime(true);

        $messages = $options['messages'] ?? [
            ['role' => 'user', 'content' => $prompt],
        ];

        // If a system prompt is provided, prepend it
        if (isset($options['system'])) {
            array_unshift($messages, ['role' => 'system', 'content' => $options['system']]);
        }

        $payload = [
            'model' => $options['model'] ?? $this->config['chat_model'] ?? self::DEFAULT_CHAT_MODEL,
            'messages' => $messages,
        ];

        // Add optional parameters if provided
        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }

        if (isset($options['max_tokens'])) {
            $payload['max_tokens'] = $options['max_tokens'];
        }

        if (isset($options['top_p'])) {
            $payload['top_p'] = $options['top_p'];
        }

        $response = $this->http()->post(self::ENDPOINT_CHAT, $payload);

        if (! $response->successful()) {
            throw ProviderRequestException::fromResponse(self::ERROR_CHAT_FAILED, $response);
        }

        $latencyMs = (microtime(true) - $startTime) * 1000;

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';

        // Parse token usage from response
        $tokenUsage = isset($data['usage']) 
            ? TokenUsage::fromArray($data['usage']) 
            : TokenUsage::empty();

        return new ChatResponse(
            content: $content,
            raw: $data,
            model: $data['model'] ?? null,
            id: $data['id'] ?? null,
            latencyMs: $latencyMs,
            tokenUsage: $tokenUsage,
        );
    }

    /**
     * Generate an image using OpenAI's DALL-E.
     */
    public function generate(array $params): ImageResponse
    {
        $payload = [
            'model' => $params['model'] ?? $this->config['image_model'] ?? self::DEFAULT_IMAGE_MODEL,
            'prompt' => $params['prompt'] ?? '',
            'size' => $params['size'] ?? self::DEFAULT_IMAGE_SIZE,
            'n' => $params['n'] ?? 1,
        ];

        // Add optional parameters
        if (isset($params['quality'])) {
            $payload['quality'] = $params['quality'];
        }

        if (isset($params['style'])) {
            $payload['style'] = $params['style'];
        }

        if (isset($params['response_format'])) {
            $payload['response_format'] = $params['response_format'];
        }

        $response = $this->http()->post(self::ENDPOINT_IMAGES, $payload);

        if (! $response->successful()) {
            throw ProviderRequestException::fromResponse(self::ERROR_IMAGE_FAILED, $response);
        }

        $data = $response->json();
        $imageData = $data['data'][0] ?? [];

        return new ImageResponse(
            url: $imageData['url'] ?? null,
            base64: $imageData['b64_json'] ?? null,
            raw: $data,
            revisedPrompt: $imageData['revised_prompt'] ?? null,
        );
    }

    /**
     * Check if the OpenAI API is accessible.
     */
    public function isAvailable(): bool
    {
        try {
            $response = $this->http()->get(self::ENDPOINT_MODELS);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the list of available models from OpenAI.
     */
    public function getAvailableModels(): array
    {
        try {
            $response = $this->http()->get(self::ENDPOINT_MODELS);
            
            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();
            return array_column($data['data'] ?? [], 'id');
        } catch (\Exception $e) {
            return [];
        }
    }
}


<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Support;

/**
 * Represents a response from a chat completion request.
 */
class ChatResponse
{
    public function __construct(
        public string $content,
        public array $raw = [],
        public ?string $model = null,
        public ?string $id = null,
        public ?float $latencyMs = null,
        public ?TokenUsage $tokenUsage = null,
    ) {
        // Ensure tokenUsage is never null for convenience
        $this->tokenUsage ??= TokenUsage::empty();
    }

    /**
     * Get the response content as a string.
     */
    public function __toString(): string
    {
        return $this->content;
    }

    /**
     * Get the raw response data.
     */
    public function getRaw(): array
    {
        return $this->raw;
    }

    /**
     * Check if the response is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->content);
    }

    /**
     * Get the total tokens used in this request.
     */
    public function getTotalTokens(): int
    {
        return $this->tokenUsage->totalTokens;
    }

    /**
     * Get the prompt/input tokens used.
     */
    public function getPromptTokens(): int
    {
        return $this->tokenUsage->promptTokens;
    }

    /**
     * Get the completion/output tokens used.
     */
    public function getCompletionTokens(): int
    {
        return $this->tokenUsage->completionTokens;
    }
}

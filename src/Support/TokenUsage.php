<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Support;

/**
 * Represents token usage information from an LLM request.
 */
class TokenUsage
{
    public function __construct(
        public int $promptTokens = 0,
        public int $completionTokens = 0,
        public int $totalTokens = 0,
    ) {
        // If total not provided, calculate it
        if ($this->totalTokens === 0 && ($this->promptTokens > 0 || $this->completionTokens > 0)) {
            $this->totalTokens = $this->promptTokens + $this->completionTokens;
        }
    }

    /**
     * Create a TokenUsage instance from an array.
     */
    public static function fromArray(array $data): static
    {
        return new static(
            promptTokens: $data['prompt_tokens'] ?? $data['input_tokens'] ?? 0,
            completionTokens: $data['completion_tokens'] ?? $data['output_tokens'] ?? 0,
            totalTokens: $data['total_tokens'] ?? 0,
        );
    }

    /**
     * Create an empty TokenUsage instance.
     */
    public static function empty(): static
    {
        return new static(0, 0, 0);
    }

    /**
     * Check if token usage data is available.
     */
    public function hasData(): bool
    {
        return $this->totalTokens > 0 || $this->promptTokens > 0 || $this->completionTokens > 0;
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'prompt_tokens' => $this->promptTokens,
            'completion_tokens' => $this->completionTokens,
            'total_tokens' => $this->totalTokens,
        ];
    }
}


<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Contracts;

/**
 * Interface for conversation storage drivers.
 */
interface ConversationStore
{
    /**
     * Get the messages for a conversation.
     *
     * @param string $conversationId
     * @return array
     */
    public function getMessages(string $conversationId): array;

    /**
     * Save messages for a conversation.
     *
     * @param string $conversationId
     * @param array $messages
     * @return void
     */
    public function saveMessages(string $conversationId, array $messages): void;

    /**
     * Add a message to a conversation.
     *
     * @param string $conversationId
     * @param array $message
     * @return void
     */
    public function addMessage(string $conversationId, array $message): void;

    /**
     * Get the system prompt for a conversation.
     *
     * @param string $conversationId
     * @return string|null
     */
    public function getSystemPrompt(string $conversationId): ?string;

    /**
     * Set the system prompt for a conversation.
     *
     * @param string $conversationId
     * @param string $prompt
     * @return void
     */
    public function setSystemPrompt(string $conversationId, string $prompt): void;

    /**
     * Clear all messages from a conversation.
     *
     * @param string $conversationId
     * @return void
     */
    public function clear(string $conversationId): void;

    /**
     * Check if a conversation exists.
     *
     * @param string $conversationId
     * @return bool
     */
    public function exists(string $conversationId): bool;

    /**
     * Delete a conversation entirely.
     *
     * @param string $conversationId
     * @return void
     */
    public function delete(string $conversationId): void;
}


<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\ConversationStores;

use Illuminate\Support\Facades\Session;
use Oziri\LlmSuite\Contracts\ConversationStore;

/**
 * Session-based conversation storage.
 * Stores conversation history in Laravel session.
 */
class SessionStore implements ConversationStore
{
    /**
     * Session key prefix for conversations.
     */
    protected const SESSION_PREFIX = 'llm_conversation_';

    /**
     * Get the session key for a conversation.
     */
    protected function getKey(string $conversationId): string
    {
        return self::SESSION_PREFIX . $conversationId;
    }

    /**
     * Get the messages for a conversation.
     */
    public function getMessages(string $conversationId): array
    {
        $data = Session::get($this->getKey($conversationId), []);
        
        return $data['messages'] ?? [];
    }

    /**
     * Save messages for a conversation.
     */
    public function saveMessages(string $conversationId, array $messages): void
    {
        $data = Session::get($this->getKey($conversationId), []);
        $data['messages'] = $messages;
        
        Session::put($this->getKey($conversationId), $data);
    }

    /**
     * Add a message to a conversation.
     */
    public function addMessage(string $conversationId, array $message): void
    {
        $messages = $this->getMessages($conversationId);
        $messages[] = $message;
        
        $this->saveMessages($conversationId, $messages);
    }

    /**
     * Get the system prompt for a conversation.
     */
    public function getSystemPrompt(string $conversationId): ?string
    {
        $data = Session::get($this->getKey($conversationId), []);
        
        return $data['system_prompt'] ?? null;
    }

    /**
     * Set the system prompt for a conversation.
     */
    public function setSystemPrompt(string $conversationId, string $prompt): void
    {
        $data = Session::get($this->getKey($conversationId), []);
        $data['system_prompt'] = $prompt;
        
        Session::put($this->getKey($conversationId), $data);
    }

    /**
     * Clear all messages from a conversation.
     */
    public function clear(string $conversationId): void
    {
        $data = Session::get($this->getKey($conversationId), []);
        $data['messages'] = [];
        
        Session::put($this->getKey($conversationId), $data);
    }

    /**
     * Check if a conversation exists.
     */
    public function exists(string $conversationId): bool
    {
        return Session::has($this->getKey($conversationId));
    }

    /**
     * Delete a conversation entirely.
     */
    public function delete(string $conversationId): void
    {
        Session::forget($this->getKey($conversationId));
    }
}


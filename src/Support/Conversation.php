<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Support;

use Oziri\LlmSuite\Contracts\ChatClient;
use Oziri\LlmSuite\Contracts\ConversationStore;

/**
 * Manages conversation history and context for chat interactions.
 */
class Conversation
{
    /**
     * The conversation ID.
     */
    protected string $id;

    /**
     * The storage driver.
     */
    protected ConversationStore $store;

    /**
     * The chat client to use.
     */
    protected ChatClient $client;

    /**
     * The provider name.
     */
    protected string $provider;

    public function __construct(
        string $id,
        ConversationStore $store,
        ChatClient $client,
        string $provider
    ) {
        $this->id = $id;
        $this->store = $store;
        $this->client = $client;
        $this->provider = $provider;
    }

    /**
     * Get the conversation ID.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the system prompt for this conversation.
     */
    public function system(string $prompt): static
    {
        $this->store->setSystemPrompt($this->id, $prompt);

        return $this;
    }

    /**
     * Get the system prompt for this conversation.
     */
    public function getSystemPrompt(): ?string
    {
        return $this->store->getSystemPrompt($this->id);
    }

    /**
     * Send a chat message and maintain history.
     */
    public function chat(string $message, array $options = []): ChatResponse
    {
        // Add user message to history
        $this->addMessage('user', $message);

        // Get all messages for context
        $messages = $this->getMessages();

        // Build options with message history
        $chatOptions = array_merge($options, [
            'messages' => $messages,
        ]);

        // Add system prompt if set
        $systemPrompt = $this->getSystemPrompt();
        if ($systemPrompt && ! isset($options['system'])) {
            $chatOptions['system'] = $systemPrompt;
        }

        // Send to LLM
        $response = $this->client->chat('', $chatOptions);

        // Add assistant response to history
        $this->addMessage('assistant', $response->content);

        return $response;
    }

    /**
     * Add a message to the conversation history.
     */
    public function addMessage(string $role, string $content): static
    {
        $this->store->addMessage($this->id, [
            'role' => $role,
            'content' => $content,
        ]);

        return $this;
    }

    /**
     * Get all messages in the conversation.
     */
    public function getMessages(): array
    {
        return $this->store->getMessages($this->id);
    }

    /**
     * Get the number of messages in the conversation.
     */
    public function getMessageCount(): int
    {
        return count($this->getMessages());
    }

    /**
     * Clear the conversation history (keeps system prompt).
     */
    public function clear(): static
    {
        $this->store->clear($this->id);

        return $this;
    }

    /**
     * Delete the entire conversation (including system prompt).
     */
    public function delete(): void
    {
        $this->store->delete($this->id);
    }

    /**
     * Check if the conversation has any messages.
     */
    public function hasMessages(): bool
    {
        return $this->getMessageCount() > 0;
    }

    /**
     * Get the last message in the conversation.
     */
    public function getLastMessage(): ?array
    {
        $messages = $this->getMessages();
        
        return empty($messages) ? null : end($messages);
    }

    /**
     * Get the last N messages.
     */
    public function getLastMessages(int $count): array
    {
        $messages = $this->getMessages();
        
        return array_slice($messages, -$count);
    }

    /**
     * Load messages from an external source.
     */
    public function loadHistory(array $messages): static
    {
        $this->store->saveMessages($this->id, $messages);

        return $this;
    }

    /**
     * Export the conversation history.
     */
    public function export(): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'system_prompt' => $this->getSystemPrompt(),
            'messages' => $this->getMessages(),
        ];
    }

    /**
     * Get the chat client being used.
     */
    public function getClient(): ChatClient
    {
        return $this->client;
    }

    /**
     * Get the provider name.
     */
    public function getProvider(): string
    {
        return $this->provider;
    }
}


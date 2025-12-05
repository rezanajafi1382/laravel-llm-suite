<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\ConversationStores;

use Illuminate\Support\Facades\DB;
use Oziri\LlmSuite\Contracts\ConversationStore;

/**
 * Database-based conversation storage.
 * Stores conversation history in a database table.
 */
class DatabaseStore implements ConversationStore
{
    /**
     * The database table name.
     */
    protected string $table;

    public function __construct(array $config = [])
    {
        $this->table = $config['table'] ?? 'llm_conversations';
    }

    /**
     * Get the messages for a conversation.
     */
    public function getMessages(string $conversationId): array
    {
        $record = $this->getRecord($conversationId);
        
        if (! $record) {
            return [];
        }

        return json_decode($record->messages, true) ?? [];
    }

    /**
     * Save messages for a conversation.
     */
    public function saveMessages(string $conversationId, array $messages): void
    {
        $record = $this->getRecord($conversationId);
        
        if ($record) {
            DB::table($this->table)
                ->where('conversation_id', $conversationId)
                ->update([
                    'messages' => json_encode($messages),
                    'updated_at' => now(),
                ]);
        } else {
            DB::table($this->table)->insert([
                'conversation_id' => $conversationId,
                'messages' => json_encode($messages),
                'system_prompt' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
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
        $record = $this->getRecord($conversationId);
        
        return $record?->system_prompt;
    }

    /**
     * Set the system prompt for a conversation.
     */
    public function setSystemPrompt(string $conversationId, string $prompt): void
    {
        $record = $this->getRecord($conversationId);
        
        if ($record) {
            DB::table($this->table)
                ->where('conversation_id', $conversationId)
                ->update([
                    'system_prompt' => $prompt,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table($this->table)->insert([
                'conversation_id' => $conversationId,
                'messages' => json_encode([]),
                'system_prompt' => $prompt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Clear all messages from a conversation.
     */
    public function clear(string $conversationId): void
    {
        DB::table($this->table)
            ->where('conversation_id', $conversationId)
            ->update([
                'messages' => json_encode([]),
                'updated_at' => now(),
            ]);
    }

    /**
     * Check if a conversation exists.
     */
    public function exists(string $conversationId): bool
    {
        return $this->getRecord($conversationId) !== null;
    }

    /**
     * Delete a conversation entirely.
     */
    public function delete(string $conversationId): void
    {
        DB::table($this->table)
            ->where('conversation_id', $conversationId)
            ->delete();
    }

    /**
     * Get a conversation record from the database.
     */
    protected function getRecord(string $conversationId): ?object
    {
        return DB::table($this->table)
            ->where('conversation_id', $conversationId)
            ->first();
    }
}


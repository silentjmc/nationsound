<?php

namespace App\Service;

/**
 * MessageService is responsible for managing messages in the application.
 * It allows adding, retrieving, and clearing messages of different types.
 */
class MessageService
{
    /**
     * @var array An array to store messages, each with a type and message content.
     */
    private array $messages = [];

    /**
     * Adds a message to the message service.
     *
     * @param string $type The type of the message (e.g., 'success', 'error', 'info').
     * @param string $message The content of the message.
     */
    public function addMessage(string $type, string $message): void
    {
        $this->messages[] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Retrieves all messages stored in the message service.
     *
     * @return array An array of messages, each containing a type and message content.
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Clears all messages from the message service.
     *
     * This method resets the messages array, effectively removing all stored messages.
     */
    public function clearMessages(): void
    {
        $this->messages = [];
    }
}
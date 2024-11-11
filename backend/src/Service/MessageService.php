<?php

namespace App\Service;

class MessageService
{
    
    private array $messages = [];

    public function addMessage(string $type, string $message): void
    {
        $this->messages[] = [
            'type' => $type,
            'message' => $message
        ];
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function clearMessages(): void
    {
        $this->messages = [];
    }
}
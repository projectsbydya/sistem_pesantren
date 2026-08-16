<?php

declare(strict_types=1);

namespace App\Services\WhatsApp\Contracts;

use App\Services\WhatsApp\WhatsAppMessage;

/**
 * WhatsApp Service Interface
 *
 * Contract for WhatsApp provider implementations.
 * All providers must implement this interface for swappable architecture.
 */
interface WhatsAppServiceInterface
{
    /**
     * Send a WhatsApp message.
     *
     * @param WhatsAppMessage $message The message to send
     * @return string|null Message ID from provider (if available)
     * @throws \App\Services\WhatsApp\Exceptions\WhatsAppException
     * @throws \App\Services\WhatsApp\Exceptions\RateLimitException
     * @throws \App\Services\WhatsApp\Exceptions\InvalidNumberException
     */
    public function send(WhatsAppMessage $message): ?string;

    /**
     * Send multiple messages in batch.
     *
     * @param array<WhatsAppMessage> $messages
     * @return array<string|null> Array of message IDs
     */
    public function sendBatch(array $messages): array;

    /**
     * Get provider name.
     */
    public function getProviderName(): string;

    /**
     * Check if provider is properly configured and ready.
     */
    public function isConfigured(): bool;

    /**
     * Get provider configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array;

    /**
     * Validate a phone number format.
     *
     * @param string $number Phone number to validate
     * @return bool True if valid for this provider
     */
    public function validateNumber(string $number): bool;

    /**
     * Get message status (if provider supports status tracking).
     *
     * @param string $messageId Message ID from send()
     * @return array<string, mixed>|null Status data or null if unsupported
     */
    public function getMessageStatus(string $messageId): ?array;

    /**
     * Check if provider supports templates.
     */
    public function supportsTemplates(): bool;

    /**
     * Check if provider supports media messages.
     */
    public function supportsMedia(): bool;

    /**
     * Check if provider supports interactive buttons.
     */
    public function supportsButtons(): bool;

    /**
     * Get available templates (if provider supports templates).
     *
     * @return array<string> Template names
     */
    public function getTemplates(): array;
}

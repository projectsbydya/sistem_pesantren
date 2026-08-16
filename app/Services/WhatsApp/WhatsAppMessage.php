<?php

declare(strict_types=1);

namespace App\Services\WhatsApp;

/**
 * WhatsApp Message Data Transfer Object
 *
 * Immutable DTO for building WhatsApp messages.
 * Supports text, template, and media messages.
 */
final class WhatsAppMessage
{
    private ?string $to = null;
    private ?string $from = null;
    private ?string $body = null;
    private ?string $template = null;
    private array $templateParams = [];
    private ?string $mediaUrl = null;
    private ?string $mediaType = null; // image, document, audio, video
    private ?string $caption = null;
    private ?string $footer = null;
    private array $buttons = [];
    private array $sections = [];
    private ?string $language = null;
    private array $metadata = [];

    /**
     * Create a new text message instance.
     */
    public static function text(string $body): self
    {
        return (new self())->body($body);
    }

    /**
     * Create a new template message instance.
     */
    public static function template(string $template, array $params = []): self
    {
        return (new self())->usingTemplate($template, $params);
    }

    /**
     * Set recipient phone number.
     */
    public function to(string $number): self
    {
        $this->to = $this->sanitizeNumber($number);
        return $this;
    }

    /**
     * Set sender phone number (optional override).
     */
    public function from(string $number): self
    {
        $this->from = $this->sanitizeNumber($number);
        return $this;
    }

    /**
     * Set message body text.
     */
    public function body(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Set template name and parameters.
     */
    public function usingTemplate(string $template, array $params = []): self
    {
        $this->template = $template;
        $this->templateParams = $params;
        return $this;
    }

    /**
     * Set media attachment.
     */
    public function media(string $url, string $type = 'image', ?string $caption = null): self
    {
        $this->mediaUrl = $url;
        $this->mediaType = $type;
        $this->caption = $caption;
        return $this;
    }

    /**
     * Set message footer.
     */
    public function footer(string $footer): self
    {
        $this->footer = $footer;
        return $this;
    }

    /**
     * Add interactive button.
     */
    public function button(string $id, string $text, string $type = 'quick_reply'): self
    {
        $this->buttons[] = [
            'id' => $id,
            'text' => $text,
            'type' => $type,
        ];
        return $this;
    }

    /**
     * Set template language code.
     */
    public function language(string $language): self
    {
        $this->language = $language;
        return $this;
    }

    /**
     * Set message metadata (for tracking/debugging).
     */
    public function metadata(array $metadata): self
    {
        $this->metadata = array_merge($this->metadata, $metadata);
        return $this;
    }

    /**
     * Add tenant context to metadata.
     */
    public function forTenant(int $tenantId): self
    {
        $this->metadata['tenant_id'] = $tenantId;
        return $this;
    }

    // Getters

    public function getTo(): ?string
    {
        return $this->to;
    }

    public function getFrom(): ?string
    {
        return $this->from ?? config('whatsapp.default_sender');
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function getTemplateParams(): array
    {
        return $this->templateParams;
    }

    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function getFooter(): ?string
    {
        return $this->footer ?? config('whatsapp.templates.footer');
    }

    public function getButtons(): array
    {
        return $this->buttons;
    }

    public function getLanguage(): ?string
    {
        return $this->language ?? config('whatsapp.templates.language', 'id');
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Check if this is a template message.
     */
    public function isTemplate(): bool
    {
        return ! empty($this->template);
    }

    /**
     * Check if this message has media.
     */
    public function hasMedia(): bool
    {
        return ! empty($this->mediaUrl);
    }

    /**
     * Check if this message has buttons.
     */
    public function hasButtons(): bool
    {
        return ! empty($this->buttons);
    }

    /**
     * Validate the message has required fields.
     *
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        if (empty($this->to)) {
            throw new \InvalidArgumentException('WhatsApp message recipient (to) is required.');
        }

        if (empty($this->body) && empty($this->template)) {
            throw new \InvalidArgumentException('WhatsApp message body or template is required.');
        }

        if ($this->hasMedia() && empty($this->mediaType)) {
            throw new \InvalidArgumentException('Media type is required when sending media.');
        }
    }

    /**
     * Convert to array for provider APIs.
     */
    public function toArray(): array
    {
        return [
            'to' => $this->to,
            'from' => $this->getFrom(),
            'body' => $this->body,
            'template' => $this->template,
            'template_params' => $this->templateParams,
            'media_url' => $this->mediaUrl,
            'media_type' => $this->mediaType,
            'caption' => $this->caption,
            'footer' => $this->getFooter(),
            'buttons' => $this->buttons,
            'language' => $this->getLanguage(),
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Sanitize phone number.
     */
    private function sanitizeNumber(string $number): string
    {
        $number = preg_replace('/[^0-9]/', '', $number);

        if (str_starts_with($number, '0') && strlen($number) > 10) {
            $number = substr($number, 1);
        }

        if (! str_starts_with($number, '62') && strlen($number) >= 10) {
            $number = '62' . $number;
        }

        return $number;
    }
}

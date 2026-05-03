<?php

namespace App\Services\AI\Contracts;

interface AIProviderInterface
{
    public function review(array $chunk): string;

    public function getName(): string;

    public function isConfigured(): bool;
}

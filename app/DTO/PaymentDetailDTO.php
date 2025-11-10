<?php

namespace App\DTO;

readonly class PaymentDetailDTO
{
    public function __construct(
        public string $label,
        public string $value,
    ) {}

    public static function fromArray(array $data): ?self
    {
        $label = trim((string) ($data['label'] ?? ''));
        $value = trim((string) ($data['value'] ?? ''));

        if ($label === '' && $value === '') {
            return null;
        }

        return new self($label, $value);
    }
}

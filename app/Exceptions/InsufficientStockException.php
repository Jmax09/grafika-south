<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an order's BOM deduction cannot be fulfilled
 * because one or more raw materials have insufficient stock.
 */
class InsufficientStockException extends RuntimeException
{
    /**
     * Array of shortage details.
     *
     * Each entry: ['material' => string, 'required' => float, 'available' => float]
     *
     * @var array<int, array{material: string, required: float, available: float}>
     */
    public array $shortages;

    /**
     * @param  array  $shortages  List of materials that are short.
     * @param  string  $message
     */
    public function __construct(array $shortages, string $message = 'Insufficient stock for one or more materials.')
    {
        $this->shortages = $shortages;
        parent::__construct($message);
    }

    /**
     * Build a human-readable summary of all shortages for flash messages.
     */
    public function getSummary(): string
    {
        $lines = [];
        foreach ($this->shortages as $s) {
            $deficit = round($s['required'] - $s['available'], 4);
            $lines[] = "• {$s['material']}: need {$s['required']}, only {$s['available']} available (short by {$deficit})";
        }

        return implode("\n", $lines);
    }
}

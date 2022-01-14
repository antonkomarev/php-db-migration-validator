<?php

declare(strict_types=1);

final class SuccessMigration
{
    public function up(): void
    {
        // SQL CODE
    }

    public function down(): void
    {
        throw new \Exception('This migration is irreversible and cannot be reverted.');
    }
}

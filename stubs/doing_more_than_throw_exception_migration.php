<?php

declare(strict_types=1);

final class DoingMoreThanThrowExceptionMigration
{
    public function up(): void
    {
        // SQL CODE
    }

    public function down(): void
    {
        echo 'Just another AST statement';

        throw new \Exception('This migration is irreversible and cannot be reverted.');
    }
}

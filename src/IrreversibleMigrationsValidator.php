<?php

declare(strict_types=1);

namespace AK\DbMigrationValidator;

use DomainException;
use Exception;
use PhpParser\Error;
use PhpParser\ErrorHandler;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

final class IrreversibleMigrationsValidator
{
    private const EXIT_CODE_SUCCESS = 0;
    private const EXIT_CODE_ERROR = 1;

    private const ROLLBACK_MIGRATION_METHOD_NAME = 'down';

    /**
     * @param array<string> $inputPaths
     * @return int
     */
    public function __invoke(
        array $inputPaths
    ): int {
        try {
            $filePathList = (new FileFinder())->findInPaths($inputPaths);
        } catch (Exception $exception) {
            $this->printLine($exception->getMessage());

            return self::EXIT_CODE_ERROR;
        }

        $filesCount = count($filePathList);

        if ($filesCount === 0) {
            $this->printLine("No migration files found in paths:");
            foreach ($inputPaths as $inputPath) {
                $this->printLine('- ' . $inputPath);
            }

            return self::EXIT_CODE_ERROR;
        }

        $errorsCount = 0;

        foreach ($filePathList as $filePath) {
            try {
                $this->validateMigrationFile($filePath);
            } catch (Exception $exception) {
                $this->printLine($exception->getMessage());
                $errorsCount++;
            }
        }

        if ($errorsCount > 0) {
            $this->printLineSeparator();
        }

        $this->printLine("Files analyzed: $filesCount");
        $this->printLine("Errors count: $errorsCount");

        if ($errorsCount > 0) {
            $this->printLineSeparator();
            $this->printRollbackCodeExample();
            return self::EXIT_CODE_ERROR;
        }

        return self::EXIT_CODE_SUCCESS;
    }

    private function getFileCode(
        string $filePath
    ): string {
        return file_get_contents($filePath);
    }

    private function validateMigrationFile(
        string $filePath
    ): void {
        $fileCode = $this->getFileCode($filePath);
        $statements = $this->parseFileCode($filePath, $fileCode);
        $methods = $this->findClassMethods($statements);

        $rollbackMigrationMethod = $this->findRollbackMigrationMethod($methods);

        if ($rollbackMigrationMethod === null) {
            throw new DomainException(
                "Migration `$filePath` missing `" . self::ROLLBACK_MIGRATION_METHOD_NAME . "` method"
            );
        }

        $methodStatements = $rollbackMigrationMethod->stmts;

        $statementsCount = count($methodStatements);

        if ($statementsCount === 0) {
            throw new DomainException(
                "Migration `$filePath` does not have any statements, must throw Exception"
            );
        }

        $statement = $methodStatements[0];

        if ($statementsCount > 1 || !($statement instanceof Throw_)) {
            throw new DomainException(
                "Migration `$filePath` should throw an Exception in `" . self::ROLLBACK_MIGRATION_METHOD_NAME . "` method, nothing more"
            );
        }
    }

    /**
     * @param string $fileCode
     * @param string $filePath
     * @return Stmt[]
     */
    private function parseFileCode(
        string $filePath,
        string $fileCode
    ): array {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

        try {
            return $parser->parse($fileCode, new ErrorHandler\Throwing());
        } catch (Error $error) {
            throw new DomainException(
                "Migration `$filePath` parse error: {$error->getMessage()}"
            );
        }
    }

    /**
     * @param array<Stmt> $statements
     * @return array<ClassMethod>
     */
    private function findClassMethods(
        array $statements
    ): array {
        $nodeFinder = new NodeFinder();

        return $nodeFinder->findInstanceOf($statements, ClassMethod::class);
    }

    /**
     * @param array<ClassMethod> $methods
     * @return ClassMethod|null
     */
    private function findRollbackMigrationMethod(
        array $methods
    ): ?ClassMethod {
        foreach ($methods as $method) {
            if ($method->name->name === self::ROLLBACK_MIGRATION_METHOD_NAME) {
                return $method;
            }
        }

        return null;
    }

    private function printRollbackCodeExample(): void
    {
        $methodName = self::ROLLBACK_MIGRATION_METHOD_NAME;
        $this->printLine('Each irreversible migration file should have following code:');
        $this->printLine('');
        $this->printLine(
            <<<EXAMPLE
public function $methodName(): void
{
    throw new \Exception('This migration is irreversible and cannot be reverted.');
}
EXAMPLE
        );
    }

    private function printLine(
        string $message
    ): void {
        echo $message . PHP_EOL;
    }

    private function printLineSeparator(): void
    {
        $this->printLine(str_pad('', 20, '-'));
    }
}

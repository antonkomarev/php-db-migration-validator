<?php

declare(strict_types=1);

namespace AK\DatabaseMigrationValidator;

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

    public function __invoke(
        string $migrationsDirectoryPath
    ): int {
        $migrationsDirectoryPath = rtrim($migrationsDirectoryPath, '/');

        if (is_dir($migrationsDirectoryPath) === false) {
            $this->printLine("Migrations directory path `$migrationsDirectoryPath` is not a directory");

            return self::EXIT_CODE_ERROR;
        }

        $fileNameList = $this->listFileNames($migrationsDirectoryPath);

        $filesCount = count($fileNameList);
        $errorsCount = 0;

        foreach ($fileNameList as $fileName) {
            $filePath = $this->resolveFilePath($migrationsDirectoryPath, $fileName);
            $fileCode = $this->getFileCode($filePath);

            try {
                $this->analyzeMigrationFileCode($filePath, $fileCode);
            } catch (Exception $exception) {
                $this->printLine($exception->getMessage());
                $errorsCount++;
            }
        }

        if ($filesCount === 0) {
            $this->printLine("No migration files found in path `$migrationsDirectoryPath`");

            return self::EXIT_CODE_ERROR;
        }

        $this->printLine(str_pad('', 20, '-'));
        $this->printLine("Files analyzed: $filesCount");
        $this->printLine("Errors count: $errorsCount");

        if ($errorsCount > 0) {
            $this->printLine(str_pad('', 20, '-'));
            $this->printRollbackCodeExample();
            return self::EXIT_CODE_ERROR;
        }

        return self::EXIT_CODE_SUCCESS;
    }

    /**
     * @param string $fileDirectoryPath
     * @return array<string>
     */
    private function listFileNames(
        string $fileDirectoryPath
    ): array {
        $fileNameList = scandir($fileDirectoryPath);

        return $this->filterMigrationFiles($fileNameList);
    }

    /**
     * @param array<string> $fileNameList
     * @return array<string>
     */
    private function filterMigrationFiles(
        array $fileNameList
    ): array {
        $result = [];

        foreach ($fileNameList as $fileName) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }

            if ($this->isStringEndsWith($fileName, '.php') === false) {
                continue;
            }

            $result[] = $fileName;
        }

        return $result;
    }

    private function resolveFilePath(
        string $fileDirectoryPath,
        string $fileName
    ): string {
        return $fileDirectoryPath . '/' . $fileName;
    }

    private function getFileCode(
        string $filePath
    ): string {
        return file_get_contents($filePath);
    }

    private function analyzeMigrationFileCode(
        string $filePath,
        string $fileCode
    ): void {
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
        $this->printLine("Each irreversible migration file should have following code:" . PHP_EOL);
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

    /**
     * Polyfill of PHP8 method `str_ends_with`.
     */
    private function isStringEndsWith(
        string $haystack,
        string $needle
    ): bool {
        $needleLength = strlen($needle);
        return ($needleLength === 0 || substr_compare($haystack, $needle, -$needleLength) === 0);
    }
}

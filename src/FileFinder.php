<?php

declare(strict_types=1);

namespace AK\DatabaseMigrationValidator;

use RuntimeException;

final class FileFinder
{
    public function findInPath(
        string $inputPath
    ): array {
        if (file_exists($inputPath) === false) {
            throw new RuntimeException(
                "Migration path `$inputPath` is not a directory or file"
            );
        }

        return $this->listFilePaths($inputPath);
    }

    /**
     * @param string $inputPath
     * @return array<string>
     */
    private function listFilePaths(
        string $inputPath
    ): array {
        if (is_file($inputPath)) {
            return [
                $inputPath,
            ];
        }

        $inputPath = rtrim($inputPath, '/');

        $fileNameList = scandir($inputPath);

        $fileNameList = $this->filterPhpFiles($fileNameList);

        return $this->prependDirectoryPathToFileNameList($fileNameList, $inputPath);
    }

    /**
     * @param array<string> $fileNameList
     * @return array<string>
     */
    private function filterPhpFiles(
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

    /**
     * @param array<string> $fileNameList
     * @param string $directoryPath
     * @return array<string>
     */
    private function prependDirectoryPathToFileNameList(
        array $fileNameList,
        string $directoryPath
    ): array {
        $filePathList = [];

        foreach ($fileNameList as $fileName) {
            $filePathList[] = $this->prependDirectoryPath($fileName, $directoryPath);
        }

        return $filePathList;
    }

    private function prependDirectoryPath(
        string $fileName,
        string $fileDirectoryPath
    ): string {
        return $fileDirectoryPath . '/' . $fileName;
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

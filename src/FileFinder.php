<?php

declare(strict_types=1);

namespace AK\DbMigrationValidator;

use RuntimeException;

final class FileFinder
{
    /**
     * @param array<string> $inputPaths
     * @return array<string>
     */
    public function findInPaths(
        array $inputPaths
    ): array {
        $filePathList = [];

        foreach ($inputPaths as $inputPath) {
            $filePathList = array_merge($filePathList, $this->findInPath($inputPath));
        }

        return array_unique($filePathList);
    }

    /**
     * @param string $inputPath
     * @return array<string>
     */
    private function findInPath(
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
        if (is_dir($inputPath)) {
            $inputPath = rtrim($inputPath, '/');
            $inputPath .= '/*.php';
        }

        return glob($inputPath);
    }
}

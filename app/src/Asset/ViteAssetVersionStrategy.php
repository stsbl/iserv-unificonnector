<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Asset;

use Symfony\Component\Asset\Exception\AssetNotFoundException;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ViteAssetVersionStrategy implements VersionStrategyInterface
{
    /** @var null|array<
            string,
            array{file?: string,
                src?: string,
                imports?: list<string>,
                dynamicImports?: list<string>,
                css?: list<string>
            }>
     */
    private ?array $manifestData = null;

    public function __construct(
        private readonly string $manifestPath,
        private readonly string $basePath,
        private readonly bool $strictMode = true,
    ) {
    }

    public function getVersion(string $path): string
    {
        return $this->applyVersion($path);
    }

    public function applyVersion(string $path): string
    {
        return $this->getAssetsPath($path) ?: $path;
    }

    public function getAssetsPath(string $path): ?string
    {
        $manifestData = $this->getData();

        if (isset($manifestData[$path]['file'])) {
            return $this->basePath . $manifestData[$path]['file'];
        }

        if ($this->strictMode) {
            $message = sprintf('assets "%s" not found in manifest file', $path);
            $alternatives = $this->findAlternatives($path, $manifestData);
            if (count($alternatives) > 0) {
                $message .= sprintf(' Did you mean one of these? "%s".', implode('", "', $alternatives));
            }

            throw new AssetNotFoundException($message, $alternatives);
        }

        return null;
    }

    /**
     * @param list<string> $parsedEntries
     *
     * @return list<string>
     */
    public function getCssPaths(string $path, array $parsedEntries = []): array
    {
        $manifestData = $this->getData();

        if (!isset($manifestData[$path])) {
            $message = sprintf('assets "%s" not found in manifest file', $path);
            $alternatives = $this->findAlternatives($path, $manifestData);
            if (count($alternatives) > 0) {
                $message .= sprintf(' Did you mean one of these? "%s".', implode('", "', $alternatives));
            }

            throw new AssetNotFoundException($message, $alternatives);
        }

        $fileData = $manifestData[$path];

        /** @var list<string> $importedCssPaths */
        $importedCssPaths = [];
        if (isset($fileData['imports'])) {
            /** @var list<list<string>> $importedPaths */
            $importedPaths = [];
            foreach ($fileData['imports'] as $path) {
                if (in_array($path, $parsedEntries, true)) {
                    continue;
                }
                $parsedEntries[] = $path;
                $importedPaths[] = $this->getCssPaths($path, $parsedEntries);
            }
            $importedCssPaths = array_merge(...$importedPaths);
        }
        if (isset($fileData['dynamicImports'])) {
            /** @var list<list<string>> $importedPaths */
            $importedPaths = [];
            foreach ($fileData['dynamicImports'] as $path) {
                if (in_array($path, $parsedEntries, true)) {
                    continue;
                }
                $parsedEntries[] = $path;
                $importedPaths[] = $this->getCssPaths($path, $parsedEntries);
            }
            $importedCssPaths = array_merge($importedCssPaths, ...$importedPaths);
        }

        if (!isset($fileData['css'])) {
            return $importedCssPaths;
        }

        return array_merge(
            array_map(fn (string $path) => $this->basePath . $path, $fileData['css']),
            $importedCssPaths,
        );
    }

    /**
     * @param array<string, array{file?: string, src?: string, imports?: list<string>, dynamicImports?: list<string>, css?: list<string>}>|null $manifestData
     * @return string[]
     */
    private function findAlternatives(string $path, ?array $manifestData): array
    {
        $path = strtolower($path);
        $alternatives = [];

        if (is_null($manifestData)) {
            return $alternatives;
        }

        foreach ($manifestData as $key => $value) {
            $lev = levenshtein($path, strtolower($key));
            if ($lev <= strlen($path) / 3 || false !== stripos($key, $path)) {
                $alternatives[$key] = isset($alternatives[$key]) ? min($lev, $alternatives[$key]) : $lev;
            }
        }

        asort($alternatives);

        return array_keys($alternatives);
    }

    /**
     * @return array<string, array{file?: string, src?: string, imports?: list<string>, dynamicImports?: list<string>, css?: list<string>}>
     */
    private function getData(): array
    {
        if ($this->manifestData === null) {
            if (!file_exists($this->manifestPath)) {
                throw new FileNotFoundException("Manifest not found at $this->manifestPath.");
            }

            $jsonString = file_get_contents($this->manifestPath);
            if ($jsonString === false) {
                throw new \LogicException(sprintf('Manifest not readable at %s', $this->manifestPath));
            }

            $content = json_decode($jsonString, true);
            if (!is_array($content)) {
                throw new \LogicException(sprintf('Invalid manifest format at %s', $this->manifestPath));
            }

            /** @var array<string, array{file?: string, src?: string, imports?: list<string>, dynamicImports?: list<string>, css?: list<string>}> $content */
            $this->manifestData = $content;
        }

        return $this->manifestData;
    }
}

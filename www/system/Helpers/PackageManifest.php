<?php


namespace Helpers;



use Helpers\PackageManifest\Generator;
use Helpers\PackageManifest\TargetTranslation;

class PackageManifest
{
    /** @var Generator */
    private $generator;
    private $packageVersion;
    private $timestamp;
    /** @var TargetTranslation[] */
    private $targetTranslations;

    function __construct() {
        $this->generator = new Generator("ts-desktop", 1);
        $this->packageVersion = 2;
        $this->timestamp = 0;
        $this->targetTranslations = [];
    }

    public function setGenerator($generator)
    {
        $this->generator = $generator;
    }

    public function setPackageVersion($packageVersion)
    {
        $this->packageVersion = $packageVersion;
    }

    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * SourceTranslations
     * @param TargetTranslation[] $targetTranslations
     */
    public function setTargetTranslations($targetTranslations)
    {
        $this->targetTranslations = $targetTranslations;
    }

    public function addTargetTranslation($targetTranslation)
    {
        if(!in_array($targetTranslation, $this->targetTranslations))
        {
            $this->targetTranslations[] = $targetTranslation;
        }
    }

    public function output()
    {
        return [
            "generator" => [
                "name" => $this->generator->name(),
                "build" => $this->generator->build()
            ],
            "package_version" => $this->packageVersion,
            "timestamp" => $this->timestamp,
            "target_translations" => array_map(function($targetTranslation) {
                return [
                    "path" => $targetTranslation->path(),
                    "id" => $targetTranslation->id(),
                    "commit_hash" => [
                        "stdout" => $targetTranslation->commitHash()->stdout(),
                        "stderr" => $targetTranslation->commitHash()->stderr(),
                        "error" => $targetTranslation->commitHash()->error(),
                    ],
                    "direction" => $targetTranslation->direction(),
                ];
            }, $this->targetTranslations),
        ];
    }
}

namespace Helpers\PackageManifest;

class Generator
{
    private $name;
    private $build;

    /**
     * Generator constructor.
     * @param string $name
     * @param string $build
     */
    function __construct($name, $build) {
        $this->name = $name;
        $this->build = $build;
    }

    public function name()
    {
        return $this->name;
    }

    public function build()
    {
        return $this->build;
    }
}

class TargetTranslation
{
    private $path;
    private $id;
    private $commitHash;
    private $direction;

    /**
     * TargetTranslation constructor.
     * @param string $path
     * @param string $id
     * @param CommitHash $commitHash
     * @param string $direction
     */
    function __construct($path, $id, $commitHash, $direction) {
        $this->path = $path;
        $this->id = $id;
        $this->commitHash = $commitHash;
        $this->direction = $direction;
    }

    public function path()
    {
        return $this->path;
    }

    public function id()
    {
        return $this->id;
    }

    public function commitHash()
    {
        return $this->commitHash;
    }

    public function direction()
    {
        return $this->direction;
    }
}


class CommitHash
{
    private $stdout;
    private $stderr;
    private $error;

    /**
     * CommitHash constructor.
     * @param string $stdout
     * @param string $stderr
     * @param string $error
     */
    function __construct($stdout, $stderr, $error) {
        $this->stdout = $stdout;
        $this->stderr = $stderr;
        $this->error = $error;
    }

    public function stdout()
    {
        return $this->stdout;
    }

    public function stderr()
    {
        return $this->stderr;
    }

    public function error()
    {
        return $this->error;
    }
}

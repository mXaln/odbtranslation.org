<?php


namespace Helpers;


use Helpers\TsManifest\Generator;
use Helpers\TsManifest\Project;
use Helpers\TsManifest\Resource;
use Helpers\TsManifest\SourceTranslation;
use Helpers\TsManifest\TargetLanguage;
use Helpers\TsManifest\Type;

class TsManifest
{
    private $packageVersion;
    private $format;
    /** @var Generator */
    private $generator;
    /** @var TargetLanguage */
    private $targetLanguage;
    /** @var Project */
    private $project;
    /** @var Type */
    private $type;
    /** @var Resource */
    private $resource;
    /** @var SourceTranslation[] */
    private $sourceTranslations;
    private $parentDraft;
    private $translators;
    private $finishedChunks;

    function __construct() {
        $this->packageVersion = "6";
        $this->format = "usfm";
        $this->generator = new Generator("ts-desktop", 1);
        $this->targetLanguage = new TargetLanguage("", "", "");
        $this->project = new Project("", "");
        $this->type = new Type("", "");
        $this->resource = new Resource("", "");
        $this->sourceTranslations = [];
        $this->parentDraft = "{}";
        $this->translators = [];
        $this->finishedChunks = [];
    }

    public function setPackageVersion($packageVersion)
    {
        $this->packageVersion = $packageVersion;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function setGenerator($generator)
    {
        $this->generator = $generator;
    }

    public function setTargetLanguage($targetLanguage)
    {
        $this->targetLanguage = $targetLanguage;
    }

    public function setProject($project)
    {
        $this->project = $project;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * SourceTranslations
     * @param SourceTranslation[] $sourceTranslations
     */
    public function setSourceTranslations($sourceTranslations)
    {
        $this->sourceTranslations = $sourceTranslations;
    }

    public function addSourceTranslation($sourceTranslation)
    {
        if(!in_array($sourceTranslation, $this->sourceTranslations))
        {
            $this->sourceTranslations[] = $sourceTranslation;
        }
    }

    public function setParentDraft($parentDraft)
    {
        $this->parentDraft = $parentDraft;
    }

    public function setTranslators($translators)
    {
        $this->translators = $translators;
    }

    public function addTranslator($translator)
    {
        if(!in_array($translator, $this->translators))
        {
            $this->translators[] = $translator;
        }
    }

    public function setFinishedChunks($finishedChunks)
    {
        $this->finishedChunks = $finishedChunks;
    }

    public function addFinishedChunk($finishedChunk)
    {
        if(!in_array($finishedChunk, $this->finishedChunks))
        {
            $this->finishedChunks[] = $finishedChunk;
        }
    }

    public function output()
    {
        return [
            "package_version" => $this->packageVersion,
            "format" => $this->format,
            "generator" => [
                "name" => $this->generator->name(),
                "build" => $this->generator->build()
            ],
            "target_language" => [
                "id" => $this->targetLanguage->id(),
                "name" => $this->targetLanguage->name(),
                "direction" => $this->targetLanguage->direction()
            ],
            "project" => [
                "id" => $this->project->id(),
                "name" => $this->project->name(),
            ],
            "type" => [
                "id" => $this->type->id(),
                "name" => $this->type->name(),
            ],
            "resource" => [
                "id" => $this->resource->id(),
                "name" => $this->resource->name(),
            ],
            "source_translations" => array_map(function($sourceTranslation) {
                return [
                    "language_id" => $sourceTranslation->languageId(),
                    "resource_id" => $sourceTranslation->resourceId(),
                    "checking_level" => $sourceTranslation->checkingLevel(),
                    "date_modified" => $sourceTranslation->dateModified(),
                    "version" => $sourceTranslation->version(),
                ];
            }, $this->sourceTranslations),
            "parent_draft" => $this->parentDraft,
            "translators" => $this->translators,
            "finished_chunks" => $this->finishedChunks
        ];
    }
}

namespace Helpers\TsManifest;

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


class TargetLanguage extends BasicCategory
{
    private $direction;

    /**
     * TargetLanguage constructor.
     * @param string $id
     * @param string $name
     * @param string $direction
     */
    function __construct($id, $name, $direction) {
        parent::__construct($id, $name);
        $this->direction = $direction;
    }

    public function direction()
    {
        return $this->direction;
    }
}


class BasicCategory
{
    private $id;
    private $name;

    /**
     * BasicCategory constructor.
     * @param string $id
     * @param string $name
     */
    function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }

    public function id()
    {
        return $this->id;
    }

    public function name()
    {
        return $this->name;
    }
}


class Project extends BasicCategory {}
class Type extends BasicCategory {}
class Resource extends BasicCategory {}


class SourceTranslation
{
    private $languageId;
    private $resourceId;
    private $checkingLevel;
    private $dateModified;
    private $version;

    /**
     * SourceTranslation constructor.
     * @param string $languageId
     * @param string $resourceId
     * @param string $checkingLevel
     * @param string $dateModified
     * @param string $version
     */
    function __construct($languageId, $resourceId, $checkingLevel, $dateModified, $version) {
        $this->languageId = $languageId;
        $this->resourceId = $resourceId;
        $this->checkingLevel = $checkingLevel;
        $this->dateModified = $dateModified;
        $this->version = $version;
    }

    public function languageId()
    {
        return $this->languageId;
    }

    public function resourceId()
    {
        return $this->resourceId;
    }

    public function checkingLevel()
    {
        return $this->checkingLevel;
    }

    public function dateModified()
    {
        return $this->dateModified;
    }

    public function version()
    {
        return $this->version;
    }
}

<?php


namespace Helpers;


use Helpers\Manifest\Language;
use Helpers\Manifest\Project;
use Helpers\Manifest\Source;

class Manifest
{
    private $conformsTo;
    private $contributor;
    private $creator;
    private $description;
    private $format;
    private $identifier;
    private $issued;
    private $modified;
    /** @var Language[] */
    private $language;
    private $publisher;
    private $relation;
    private $rights;
    /** @var Source[] */
    private $source;
    private $subject;
    private $title;
    private $type;
    private $version;
    private $checkingEntity;
    private $checkingLevel;
    /** @var Project[] */
    private $projects;


    function __construct() {
        $this->conformsTo = "rc0.2";
        $this->contributor = [];
        $this->creator = "";
        $this->description = "";
        $this->format = "";
        $this->identifier = "";
        $this->issued = "";
        $this->modified = "";
        $this->language = new Language("", "", "");
        $this->publisher = "";
        $this->relation = [];
        $this->rights = "CC BY-SA 4.0";
        $this->source = [];
        $this->subject = "";
        $this->title = "";
        $this->type = "";
        $this->version = 1;
        $this->checkingEntity = [];
        $this->checkingLevel = 1;
        $this->projects = [];
    }

    public function setConformsTo($conformsTo)
    {
        $this->conformsTo = $conformsTo;
    }

    /**
     * Contributor
     * @param array $contributors
     */
    public function setContributor($contributors)
    {
        $this->contributor = $contributors;
    }

    public function addContributor($contributor)
    {
        if(!in_array($contributor, $this->contributor))
        {
            $this->contributor[] = $contributor;
        }
    }

    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function setIssued($issued)
    {
        $this->issued = $issued;
    }

    public function setModified($modified)
    {
        $this->modified = $modified;
    }

    /**
     * Language
     * @param Language $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * Relation
     * @param array $relations
     */
    public function setRelation($relations)
    {
        $this->relation = $relations;
    }

    public function setRights($rights)
    {
        $this->rights = $rights;
    }

    /**
     * Source
     * @param Source[] $sources
     */
    public function setSource($sources)
    {
        $this->source = $sources;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Checking entity
     * @param array $entities
     */
    public function setCheckingEntity($entities)
    {
        $this->checkingEntity = $entities;
    }

    public function setCheckingLevel($level)
    {
        $this->checkingLevel = $level;
    }

    /**
     * Projects
     * @param Project[] $projects
     */
    public function setProjects($projects)
    {
        $this->projects = $projects;
    }

    /**
     * Add project
     * @param Project $project
     */
    public function addProject($project)
    {
        $this->projects[] = $project;
    }

    public function getProject($identifier)
    {
        foreach ($this->projects as $project)
        {
            if($project->identifier() == $identifier)
            {
                return $project;
            }
        }
        return false;
    }

    public function output()
    {
        return [
            "dublin_core" => [
                "conformsto" => $this->conformsTo,
                "contributor" => $this->contributor,
                "creator" => $this->creator,
                "description" => $this->description,
                "format" => $this->format,
                "identifier" => $this->identifier,
                "issued" => $this->issued,
                "modified" => $this->modified,
                "language" => [
                    "direction" => $this->language->direction(),
                    "identifier" => $this->language->identifier(),
                    "title" => $this->language->title()
                ],
                "publisher" => $this->publisher,
                "relation" => $this->relation,
                "rights" => $this->rights,
                "source" => array_map(function ($source)
                {
                    return [
                        "identifier" => $source->identifier(),
                        "language" => $source->language(),
                        "version" => $source->version()
                    ];
                }, $this->source),
                "subject" => $this->subject,
                "title" => $this->title,
                "type" => $this->type,
                "version" => $this->version

            ],
            "checking" => [
                "checking_entity" => $this->checkingEntity,
                "checking_level" => $this->checkingLevel
            ],

            "projects" => array_map(function ($project)
            {
                return [
                    "title" => $project->title(),
                    "versification" => $project->versification(),
                    "identifier" => $project->identifier(),
                    "sort" => $project->sort(),
                    "path" => $project->path(),
                    "categories" => $project->categories()
                ];
            }, $this->projects)
        ];
    }
}

namespace Helpers\Manifest;

class Language
{
    private $direction;
    private $identifier;
    private $title;

    /**
     * Language constructor.
     * @param string $direction
     * @param string $identifier
     * @param string $title
     */
    function __construct($direction, $identifier, $title) {
        $this->direction = $direction;
        $this->identifier = $identifier;
        $this->title = $title;
    }

    public function direction()
    {
        return $this->direction;
    }

    public function identifier()
    {
        return $this->identifier;
    }

    public function title()
    {
        return $this->title;
    }
}


class Source
{
    private $identifier;
    private $language;
    private $version;

    /**
     * Source constructor.
     * @param string $identifier
     * @param string $language
     * @param string $version
     */
    function __construct($identifier, $language, $version) {
        $this->identifier = $identifier;
        $this->language = $language;
        $this->version = $version;
    }

    public function identifier()
    {
        return $this->identifier;
    }

    public function language()
    {
        return $this->language;
    }

    public function version()
    {
        return $this->version;
    }
}


class Project
{
    private $title;
    private $versification;
    private $identifier;

    private $sort;
    private $path;
    private $categories;

    /**
     * Project constructor.
     * @param string $title
     * @param string $versification
     * @param string $identifier
     * @param integer $sort
     * @param string $path
     * @param array $categories
     */
    function __construct($title, $versification, $identifier, $sort, $path, $categories) {
        $this->title = $title;
        $this->versification = $versification;
        $this->identifier = $identifier;
        $this->sort = $sort;
        $this->path = $path;
        $this->categories = $categories;
    }

    public function title()
    {
        return $this->title;
    }

    public function versification()
    {
        return $this->versification;
    }

    public function identifier()
    {
        return $this->identifier;
    }

    public function sort()
    {
        return $this->sort;
    }

    public function path()
    {
        return $this->path;
    }

    public function categories()
    {
        return $this->categories;
    }
}
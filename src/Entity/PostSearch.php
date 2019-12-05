<?php
namespace App\Entity;

class PostSearch {

    /**
     * @var string|null
     */
    private $query;

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(string $query): PostSearch
    {
        $this->query = $query;
        return $this;
    }

}
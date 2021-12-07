<?php

namespace App\Search;
use Symfony\Component\Validator\Constraints as Assert;

class Search
{
    /**
     * @Assert\NotBlank
     */
    private string $keyword;
    /**
     * @return string
     */
    public function getKeyword(): string
    {
        return $this->keyword;
    }
    /**
     * @param string $keyword
     */
    public function setKeyword(string $keyword): void
    {
        $this->keyword = $keyword;
    }
}
<?php

namespace App\Model;

/**
 * Class Node represents a vertex of a graph, the smallest unit.
 *
 * @package Model
 */
class Node
{
    /** @var int */
    private $id;

    /**
     * Node constructor requires an identifier that must be unique across a graph.
     *
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}

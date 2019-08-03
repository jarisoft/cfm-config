<?php

namespace App\Model;

/**
 * Class Node represents a vertex of a graph, the smallest unit.
 *
 * @package Model
 */
class Node
{
    /** @var mixed */
    private $id;

    /**
     * Node constructor requires an identifier that must be unique across a graph.
     *
     * @param mixed $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}

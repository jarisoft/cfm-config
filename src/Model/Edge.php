<?php

namespace App\Model;

/**
 * Class Edge represents the direct connection between two nodes in a directed graph.
 * Every edge has a two sides, outgoing side and incoming side that is represented by an ID of
 * a node.
 * @package App\Model
 */
class Edge
{
    /** @var int */
    private $fromNodeId;
    /** @var int */
    private $toNodeId;

    /**
     * Edge constructor must be defined by two non zero integers.
     *
     * @param int $from the id of the outgoing node
     * @param int $to the id of the incoming node
     * @throws \Exception
     */
    public function __construct($from, $to)
    {
        if (0 === $from * $to) {
            throw new \Exception('The IDs of both nodes must not be 0', 500);
        }

        $this->fromNodeId = $from;
        $this->toNodeId   = $to;
    }

    /**
     * @return int
     */
    public function getFromNodeId()
    {
        return $this->fromNodeId;
    }

    /**
     * @return int
     */
    public function getToNodeId()
    {
        return $this->toNodeId;
    }
}

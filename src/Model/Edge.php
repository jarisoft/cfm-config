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
    /** @var mixed */
    private $fromNodeId;
    /** @var mixed */
    private $toNodeId;

    /**
     * Edge constructor must be defined by two non null primitive typed
     * identifiers.
     *
     * @param mixed $from the id of the outgoing node
     * @param mixed $to the id of the incoming node
     * @throws \Exception
     */
    public function __construct($from, $to)
    {
        if (null == $from || null == $to) {
            throw new \Exception('The IDs of both nodes must not be null', 500);
        }

        $this->fromNodeId = $from;
        $this->toNodeId   = $to;
    }

    /**
     * @return mixed
     */
    public final function getFromNodeId()
    {
        return $this->fromNodeId;
    }

    /**
     * @return mixed
     */
    public final function getToNodeId()
    {
        return $this->toNodeId;
    }
}

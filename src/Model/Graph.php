<?php

namespace App\Model;

/**
 * Class Graph represents a directed graph. A graph is per definition a set of
 * vertices or nodes and a set of edges.
 *
 * @package Model
 */
class Graph
{
    // This constant avoids infinite recursion when checking the depth of a graph.
    const MAX_GRAPH_DEPTH = 500;
    /** @var Node[] */
    private $nodes;
    /** @var Edge[] */
    private $edges;

    /**
     * Graph constructor.
     * @param array $nodes
     * @param array $edges
     */
    public function __construct(array $nodes = [], array $edges = [])
    {
        $this->edges = $edges;
        $this->nodes = $nodes;
    }

    /**
     * @return array
     */
    public function getEdges()
    {
        return $this->edges;
    }

    /**
     * @return array
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    public function addEdge(Edge $edge)
    {
        $this->edges[] = $edge;
    }

    public function addNode(Node $node)
    {
        $this->nodes[] = $node;
    }

    /**
     * Finds all nodes that have no parent node but one or many child nodes.
     *
     * @return Node[]
     */
    public function getRootNodes()
    {
        $rootNodes = [];
        foreach ($this->nodes as $node) {
            // Fetch all edges and check if current node is in the fromNodeId but not in the toNodeId.
            $isParent = false;
            $isChild  = false;

            /** @var Edge $edge */
            foreach ($this->edges as $edge) {
                if ($node->getId() == $edge->getFromNodeId()) {
                    $isParent = true;
                }

                if ($node->getId() == $edge->getToNodeId()) {
                    $isChild = true;
                }
            }

            if ($isParent && !$isChild) {
                $rootNodes[] = $node;
            }
        }

        return $rootNodes;
    }

    /**
     * Returns all nodes that have one or many parent nodes but no children.
     *
     * @return Node[]
     */
    public function getLeafNodes()
    {
        $leafNodes = [];
        foreach ($this->nodes as $node) {
            // Fetch all edges and check if current node is in the toNodeId but not in the fromNodeId.
            $isParent = false;
            $isChild  = false;
            /** @var Edge $edge */
            foreach ($this->edges as $edge) {
                if ($node->getId() == $edge->getFromNodeId()) {
                    $isParent = true;
                }

                if ($node->getId() == $edge->getToNodeId()) {
                    $isChild = true;
                }
            }

            if ($isChild && !$isParent) {
                $leafNodes[] = $node;
            }
        }
        return $leafNodes;
    }

    /**
     * Returns the length of the longest path in the graph.
     * In a graph we might have circular structures, even a node can be a parent of itself.
     * Those nodes are excluded from the count.
     *
     * @return int
     * @throws \Exception
     */
    public function getMaxDepth()
    {
        $maxPathLength = 0;
        /** @var Node $rootNode */
        foreach ($this->getRootNodes() as $rootNode) {
            if (!$this->findCirclesByNodeId($rootNode->getId())) {

                $pathLength = $this->getPathLength($rootNode->getId(), 0);

                if ($maxPathLength < $pathLength) {
                    $maxPathLength = $pathLength;
                }
            } else {
                throw new \Exception('Graph has at least one circular path. Cannot determine max depth.');
            }
        }

        return $maxPathLength;
    }

    /**
     * Returns the length of the longest sub graph of the node in the graph with given nodeId.
     * This method is called recursively on all child nodes.
     * Care has to be taken before calling this method. If the graph has circular paths, then we easily end
     * up in a infinite recursion. Therefor the Graph::MAX_GRAPH_DEPTH is protecting us from memory exhaustion
     *
     * @param int $nodeId the id of the node to retrieve the longest path length from.
     * @param int $depth the current depths
     *
     * @return int
     * @throws \Exception
     */
    private function getPathLength($nodeId, int $depth)
    {
        if ($this->isLeaf($nodeId)) {
            return $depth;
        }

        // Check if we have reached the depth limit. If so we may have found a circular path
        // and we should throw an error. Otherwise we will run out of memory.
        if ($depth >= self::MAX_GRAPH_DEPTH) {
            throw new \Exception("Potentially found a circular path. Infinite recursion.");
        }

        // Find the outgoing edges of current node
        $edges = $this->findChildrenEdges($nodeId);
        $depth++;
        $max = 0;
        // recursively find the longest path of all children of current node.
        foreach ($edges as $edge) {
            // recursively find the deepest path.
            $pathDepth = $this->getPathLength($edge->getToNodeId(), $depth);
            if ($max < $pathDepth) {
                $max = $pathDepth;
            }
        }

        return $max;
    }

    /**
     * Checks whether a node with given nodeId is a leaf. A leaf is per definition a
     * node that has at least one parent but no children.
     *
     * @param int $nodeId
     * @return bool
     */
    public function isLeaf($nodeId)
    {
        foreach ($this->getLeafNodes() as $leaf) {
            if ($nodeId == $leaf->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Finds all outgoing edges for a node with given nodeId.
     *
     * @param $nodeId
     *
     * @return Edge[]
     */
    private function findChildrenEdges($nodeId)
    {
        $edges = [];
        foreach ($this->edges as $edge) {
            // Find all edges in which the from node id is equal to the argument
            if ($edge->getFromNodeId() == $nodeId) {
                $edges[] = $edge;
            }
        }

        return $edges;
    }

    /**
     * Finds all nodes that have neither a parent nor a child
     */
    public function findOrphans()
    {
        $orphans = [];
        foreach ($this->nodes as $node) {
            $isChild  = false;
            $isParent = false;
            foreach ($this->edges as $edge) {
                if ($edge->getFromNodeId() == $node->getId()) {
                    $isParent = true;
                }
                if ($edge->getToNodeId() == $node->getId()) {
                    $isChild = true;
                }
            }

            if (!$isChild && !$isParent) {
                $orphans[] = $node;
            }
        }

        return $orphans;
    }

    /**
     * Finds all circular paths for each node in the graph.
     * The returned array only stores the IDs of the nodes that belong
     * to the path that describes a circle.
     *
     * @return array
     */
    public function findAllCircularPaths()
    {
        $circles = [];
        foreach ($this->nodes as $node) {
            $circle = $this->findCirclesByNodeId($node->getId());
            if (!empty($circle)) {
                $circles[] = $circle;
            }
        }

        return $circles;
    }

    /**
     * Checks if the current graph has circular paths. Circular paths contains nodes that
     * eventually will repeatedly visited.
     *
     * @return bool
     */
    public function hasCircularPaths()
    {
        foreach ($this->getRootNodes() as $rootNode) {
            $circles = $this->findCirclesByNodeId($rootNode->getId());

            if (!empty($circles)) {

                return true;
            }
        }

        return false;
    }

    /**
     * Finds the circle of a sub graph beginning with a node that has the given
     * nodeId. This will return an array of the node sequence that is referring
     * to already visited nodes or an empty array if no circle has been found.
     *
     * @param $nodeId
     * @return array
     */
    private function findCirclesByNodeId($nodeId, array $visited = [])
    {
        // Case that given nodeId was already visited.
        if (in_array($nodeId, $visited)) {
            $visited[] = $nodeId;
            return $visited;
        }

        // The escape condition. If this node is a leaf we know that there are no
        // circles in the currently analysed path.
        if ($this->isLeaf($nodeId)) {
            return [];
        }

        // Add current nodeId to the set of already visited node IDs and recursively
        // check sub paths.
        foreach ($this->findChildrenEdges($nodeId) as $edge) {
            $visited[] = $nodeId;
            $visited = $this->findCirclesByNodeId($edge->getToNodeId(), $visited);
        }

        return $visited;
    }

    /**
     *
     * @param $nodeId
     * @return array
     */
    public function findChildrenNodes($nodeId)
    {
        $childEdges = $this->findChildrenEdges($nodeId);
        $children   = [];
        foreach ($childEdges as $childEdge) {
            $child = $this->getNode($childEdge->getToNodeId());
            if (!is_null($child)) {
                $children[] = $child;
            }
        }

        return $children;
    }

    /**
     * Returns the node object with given ID.
     *
     * @param int $nodeId
     * @return Node|mixed|null
     */
    private function getNode($nodeId)
    {
        foreach ($this->nodes as $node) {
            if ($node->getId() == $nodeId) {
                return $node;
            }
        }

        return null;
    }
}

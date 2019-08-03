<?php

namespace App\Tests\Model;

use App\Model\Edge;
use App\Model\Graph;
use App\Model\Node;
use PHPUnit\Framework\TestCase;

/**
 * Class GraphTest
 *
 * @group Model
 * @group Model_Graph
 *
 * @package App\Tests\Model
 */
class GraphTest extends TestCase
{
    public function testAddEdge()
    {
        $graph = new Graph();
        $edges = $graph->getEdges();
        $this->assertEmpty($edges);
        $graph->addEdge(new Edge(1, 2));
        $edges = $graph->getEdges();
        $this->assertNotEmpty($edges);
    }

    /**
     * Tests the circularity of graphs.
     * First graph has a circular path 3->6->7->3. The second graph has no
     * circular paths. The third graph references a node within the second sub
     * graph, but also doesn't result in a circle.
     */
    public function testHasCircularPaths()
    {
        /*
         *        1
         *       / \
         *      2   3 <- 7
         *     /   / \  /
         *    4   5   6
         */
        $nodes = [1, 2, 3, 4, 5, 6, 7];
        $edges = [[1, 2], [2, 4], [1, 3], [3, 5], [3, 6], [6, 7], [7, 3]];
        $graph = new Graph($this->createNodes($nodes), $this->createEdges($edges));

        $this->assertTrue($graph->hasCircularPaths(),
            "Graph was expected to have a circular path");

        /*
         *        1
         *       / \
         *      2   3
         *     /   / \
         *    4   5   6
         *             \
         *              7
         */
        $nodes = [1, 2, 3, 4, 5, 6, 7];
        $edges = [[1, 2], [2, 4], [1, 3], [3, 5], [3, 6], [6, 7]];
        $graph = new Graph($this->createNodes($nodes), $this->createEdges($edges));

        $this->assertFalse($graph->hasCircularPaths(),
            'Graph was not expected to have a circular path');

        /*
        *        1
        *       / \
        *      2 - 3
        *     / \ / \
        *    4   5   6
        */
        $nodes = [1, 2, 3, 4, 5, 6];
        $edges = [[1, 2], [2, 4], [2, 5], [2, 3], [1, 3], [3, 5], [3, 6]];
        $graph = new Graph($this->createNodes($nodes), $this->createEdges($edges));

        $this->assertFalse($graph->hasCircularPaths(),
            'Graph was not expected to have a circular path');
    }

    /**
     * Test the isLeaf function, which expects to return true if, and only if
     * a node has a parent but no children. In any other case it returns false.
     *
     * @throws \Exception
     */
    public function testIsLeaf()
    {
        /*
         *  1   2
         *  |
         *  3
         *
         * Node 1 directs to node 3 which is a leaf but node 2 is an orphan and doesn't have neither a child nor
         * a parent and should not be a leaf.
         */
        $nodes = [1, 2, 3];
        $edges = [[1, 3]];
        $graph = new Graph($this->createNodes($nodes), $this->createEdges($edges));
        // Root node
        $this->assertFalse($graph->isLeaf(1));
        // Leaf node
        $this->assertTrue($graph->isLeaf(3));
        // Orphan
        $this->assertFalse($graph->isLeaf(2));
    }

    /**
     * Tests the getRootNodes function which is expected to return only nodes
     * that have no parent but at least one child.
     */
    public function testGetRootNodes()
    {
        /*
         * simple tree structure:
         *  1
         *  |
         *  2
         * expected [1]
         */
        $graph     = new Graph([new Node(1), new Node(2)], [new Edge(1, 2)]);
        $rootNodes = $graph->getRootNodes();

        $this->assertEquals([new Node(1)], $rootNodes, 'Expected root node does not match');

        /*
         * One child with two root nodes
         *   1   2
         *    \ /
         *     3
         * expected [1,2]
         */
        $nodes     = [1, 2, 3];
        $edges     = [[1, 3], [2, 3]];
        $graph     = new Graph($this->createNodes($nodes), $this->createEdges($edges));
        $rootNodes = $graph->getRootNodes();
        $this->assertEquals([new Node(1), new Node(2)], $rootNodes);

        /*
         * No root node. One orphan node and one circular path
         *
         *       1 -> 2 -> 1
         *       3
         */
        $nodes = [1, 2, 3];
        $edges = [[1, 2], [2, 1]];
        $graph = new Graph($this->createNodes($nodes), $this->createEdges($edges));
        $this->assertEmpty($graph->getRootNodes());
    }

    /**
     * Tests the getLeafNodes function which is expected to return only nodes
     * that have at least one parent but no children nodes.
     */
    public function testGetLeafNodes()
    {
        /*
         * simple tree structure:
         *  1
         *  |
         *  2
         * expected [2]
         */
        $graph     = new Graph([new Node(1), new Node(2)], [new Edge(1, 2)]);
        $rootNodes = $graph->getLeafNodes();

        $this->assertEquals([new Node(2)], $rootNodes, 'Expected leaf node does not match');

        /*
         * One child with two root nodes
         *   1   2
         *    \ /
         *     3
         * expected [3]
         */
        $nodes     = [1, 2, 3];
        $edges     = [[1, 3], [2, 3]];
        $graph     = new Graph($this->createNodes($nodes), $this->createEdges($edges));
        $rootNodes = $graph->getLeafNodes();
        $this->assertEquals([new Node(3)], $rootNodes);

        /*
         * No leaf node. One orphan node and one circular path
         *
         *       1 -> 2 -> 1
         *       3
         */
        $nodes = [1, 2, 3];
        $edges = [[1, 2], [2, 1]];
        $graph = new Graph($this->createNodes($nodes), $this->createEdges($edges));
        $this->assertEmpty($graph->getLeafNodes());
    }

    /**
     * Tests the getMaxDepth function.
     * @throws \Exception
     */
    public function testGetMaxDepth()
    {
        /*
         * Simple graph:
         *   1
         *   |
         *   2
         * expected: 1
         */
        $nodes = [1, 2];
        $edges = [[1, 2]];
        $graph = new Graph($this->createNodes($nodes), $this->createEdges($edges));
        $this->assertEquals(1, $graph->getMaxDepth());

        /*
         * Simple graph with two root nodes:
         *   1   2
         *    \ /
         *     3
         * expected: 1
         */
        $nodes = [1, 2, 3];
        $edges = [[1, 3], [2, 3]];
        $graph = new Graph($this->createNodes($nodes), $this->createEdges($edges));
        $this->assertEquals(1, $graph->getMaxDepth());

        /*
         * Simple graph with two sub graphs:
         *       1
         *      / \
         *     2   3
         *          \
         *           4
         * expected: 2
         */
        $nodes = [1, 2, 3, 4];
        $edges = [[1, 2], [1, 3], [3, 4]];
        $graph = new Graph($this->createNodes($nodes), $this->createEdges($edges));
        $this->assertEquals(2, $graph->getMaxDepth());

        /*
         * Expected exception when graph has circular paths
         *  1 -> 2 -> 3 -> 2
         */
        $this->expectExceptionMessage('Graph has at least one circular path. Cannot determine max depth.');
        $nodes = [1, 2, 3];
        $edges = [[1, 2], [2, 3], [3, 2]];
        $graph = new Graph($this->createNodes($nodes), $this->createEdges($edges));
        $graph->getMaxDepth();
    }

    /**
     * Tests findAllCircularPaths method. This method expects all paths that
     * result in a circular graph.
     * @throws \Exception
     */
    public function testFindAllCircularPaths()
    {
        /*
         * Empty array if no circular paths can be found.
         */
        $nodes = $this->createNodes([1, 2, 3, 4]);
        $edges = $this->createEdges([[1, 2], [2, 3], [1, 4]]);
        $graph = new Graph($nodes, $edges);
        $this->assertEmpty($graph->findAllCircularPaths());

        /*
         * A circular graph:
         * 1 -> 2 -> 3 -> 2
         *
         * The paths that are resulting in a circular path are:
         * 1, 2, 3, 2
         * 2, 3, 2
         * 3, 2, 3
         */
        $nodes         = $this->createNodes([1, 2, 3,]);
        $edges         = $this->createEdges([[1, 2], [2, 3], [3, 2]]);
        $graph         = new Graph($nodes, $edges);
        $circularPaths = $graph->findAllCircularPaths();
        $expectedPaths = [
            [1, 2, 3, 2],
            [2, 3, 2],
            [3, 2, 3],
        ];
        $this->assertEquals($expectedPaths, $circularPaths);
    }

    /**
     * Test getEdges.
     * @throws \Exception
     */
    public function testGetEdges()
    {
        $graph = new Graph();
        $this->assertEmpty($graph->getEdges());
        $graph->addEdge(new Edge(1, 2));
        $this->assertEquals([new Edge(1, 2)], $graph->getEdges());
    }

    /**
     * Tests findChildrenNodes which is expected to return all direct children of a
     * node.
     *
     * @throws \Exception
     */
    public function testFindChildrenNodes()
    {
        /*
         * A graph with one root node that has 4 children:
         *          1
         *       / / \  \
         *      2  3  4  5
         */
        $nodes = $this->createNodes([1, 2, 3, 4, 5]);

        $edges         = $this->createEdges([[1, 2], [1, 3], [1, 4], [1, 5]]);
        $graph         = new Graph($nodes, $edges);
        $expectedNodes = $this->createNodes([2, 3, 4, 5]);
        $this->assertEquals($expectedNodes, $graph->findChildrenNodes(1));

        /*
         * A leaf node is expected not to have any children. Therefor the expected
         * array is empty.
         */
        $this->assertEmpty($graph->findChildrenNodes(2));
    }

    /**
     * Tests findOrphans which is expected to return an array of nodes that have neither
     * a parent nor a child.
     *
     * @throws \Exception
     */
    public function testFindOrphans()
    {
        /*
         * No edges between any of the existing nodes. So all nodes are
         * orphans
         */
        $nodes = $this->createNodes([1, 2, 3]);
        $graph = new Graph($nodes, []);
        $graph->findOrphans();
        $this->assertEquals($nodes, $graph->findOrphans());
        // Lets add one edge between 1 and 3. Now we should have only one remaining orphan.
        $graph->addEdge(new Edge(1, 3));
        $this->assertEquals([new Node(2)], $graph->findOrphans());
    }

    /**
     * Tests getNodes method.
     */
    public function testGetNodes()
    {
        $nodes = $this->createNodes([1, 2, 3]);
        $graph = new Graph($nodes, []);
        $this->assertEquals($nodes, $graph->getNodes());
    }

    /**
     * Helper function to create an array of Edge objects by the given array of
     * 2-element arrays.
     *
     * @param array $edgeArray
     * @return array
     * @throws \Exception
     */
    private function createEdges(array $edgeArray)
    {
        $edges = [];
        foreach ($edgeArray as $edge) {
            if (is_array($edge) && count($edge) == 2) {
                $edges[] = new Edge($edge[0], $edge[1]);
            }
        }

        return $edges;
    }

    /**
     * Helper function to create an array of Node objects by the a given
     * array of IDs.
     *
     * @param array $nodeIds
     * @return array
     */
    private function createNodes(array $nodeIds)
    {
        $nodes = [];
        foreach ($nodeIds as $nodeId) {
            $nodes[] = new Node($nodeId);
        }

        return $nodes;
    }
}

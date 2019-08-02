<?php
namespace App\Command;

use App\Model\Edge;
use App\Model\Graph;
use App\Model\Node;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TestGraphCommand a command to test end to end some of the graph features.
 * Execute bin/console app:create-graph to run this command.
 *
 * @package App\Command
 */
class TestGraphCommand extends Command
{
    /** @var Graph */
    private $graph;
    /** @var string the name of this command */
    protected static $defaultName = 'app:create-graph';

    public function __construct()
    {
        parent::__construct();
        $this->setName(self::$defaultName);
    }

    /**
     * Override parent execute. This method creates a graph, adds a few nodes and edges.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Create a simple graph object.
        $this->graph = new Graph();
        // Node 1 is a root node
        $this->graph->addNode(new Node(1));
        $this->graph->addNode(new Node(2));
        $this->graph->addNode(new Node(3));
        // Node 4 is a new root node.
        $this->graph->addNode(new Node(4));
        $this->graph->addNode(new Node(5));
        $this->graph->addNode(new Node(6));
        $this->graph->addNode(new Node(7));

        $this->graph->addEdge(new Edge(1,2));
        $this->graph->addEdge(new Edge(2, 3));
        $this->graph->addEdge(new Edge(4, 2));
        $this->graph->addEdge(new Edge(4,5));
        $this->graph->addEdge(new Edge(5,6));
        $this->graph->addEdge(new Edge(6,7));
        // Create a circular path
        // $this->graph->addEdge(new Edge(7, 6));

        var_dump($this->graph->getRootNodes());
        var_dump($this->graph->getLeafNodes());
        var_dump($this->graph->getMaxDepth());
        var_dump($this->graph->hasCircularPaths());
    }
}

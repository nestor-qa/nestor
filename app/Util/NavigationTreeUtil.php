<?php

namespace Nestor\Util;

use Fhaculty\Graph\Graph as Graph;
use Fhaculty\Graph\Vertex;
use Graphp\Algorithms\Search\BreadthFirst;
use HTML;
use Nestor\Model\Nodes;

/**
 * Utility methods for the NavigationTree.
 *
 * @author Bruno P. Kinoshita
 * @since 0.12
 */
class NavigationTreeUtil
{
    /**
     * Hidden constructor.
     */
    private function __construct()
    {
    }
    
    /**
     * Create a graph given an array of nodes, and a root node.
     *
     * @param Array $nodes            
     * @param string $root            
     * @return Array
     */
    public static function createNavigationTree($nodes, $root)
    {
        list ( $graph, $vertices ) = static::createGraph($nodes);
        // Do a breadth first search to construct the desired set of vertices
        $rootVertex = new BreadthFirst($vertices [$root]);
        $bfsVertices = $rootVertex->getVertices();
        // $tree is the result object. Some of its elements are modified by other function, by reference.
        $tree = array ();
        $node = $vertices [$root]->data;
        $tree [] = $node;
        // Here $vertices gets its data element modified. The data element will get a children object, with all the
        // childreno nodes of the root vertex.
        static::createTreeFromVertex($vertices [$root]);
        return $tree;
    }
    
    /**
     * Create a graph with the given nodes.
     *
     * @param Array $nodes            
     */
    private static function createGraph($nodes)
    {
        $graph = new Graph();
        $vertices = array ();
        // first add all the nodes of the graph/tree
        foreach ( $nodes as $node )
        {
            $node = (object) $node;
            if ($node->length ==0)
            {
                $vertex = $graph->createVertex($node->descendant, /* returnDuplicate */ TRUE);
                $vertex->data = $node;
                $vertices [$node->descendant] = $vertex;
            }
        }
        // now create the edges
        foreach ( $nodes as $node )
        {
            $node = (object) $node;
            if ($node->length !=0)
            {
                $from = $vertices [$node->ancestor]; // get the parent node
                $to = $vertices [$node->descendant]; // the destination node
                
                $from->createEdgeTo($to);
            }
        }
        return array (
                $graph,
                $vertices 
        );
    }
    
    /**
     * Recursive function, that creates a tree from one single vertex/node.
     * It modifies the
     * given vertex, adding its edges as children in its data attribute.
     *
     * @param Vertex $vertex            
     */
    private static function createTreeFromVertex($vertex)
    {
        $node = $vertex->data;
        $node->children = array ();
        foreach ( $vertex->getEdgesOut() as $edge )
        {
            $childVertex = $edge->getVertexEnd();
            $node->children [] = $childVertex->data;
            static::createTreeFromVertex($childVertex);
        }
    }
    public static function filterNavigationTree(&$filtered, $tree, $nodesToFilter)
    {
        foreach ( $tree as $key => $node )
        {
            if ($node->node_type_id ==Nodes::PROJECT_TYPE)
            {
                $filtered [$key] = $node;
                if (!empty($node->children))
                {
                    static::filterNavigationTree($filtered, $node->children, $nodesToFilter);
                }
            } else if ($node->node_type_id ==Nodes::TEST_CASE_TYPE &&array_key_exists($node->node_id, $nodesToFilter))
            {
                $filtered [$key] = $node;
            }
        }
    }
    
    // --- HTML
    public static function createNavigationTreeHtml($navigationTree = array(), $nodeId, $themeName = '', $nodesSelected = array())
    {
        $buffer = '';
        if (is_null($navigationTree) ||empty($navigationTree))
            return $buffer;
        foreach ( $navigationTree as $node )
        {
            $extra_classes = "";
            if ($node->descendant ==$nodeId &&$node->ancestor ==$nodeId)
            {
                $extra_classes .= " active";
            }
            $nodeTypeId = $node->node_type_id;
            if ($nodeTypeId ==Nodes::TEST_CASE_TYPE &&array_key_exists($node->node_id, $nodesSelected))
            {
                $extra_classes .= " selected";
            }
            if ($node->node_type_id ==Nodes::PROJECT_TYPE)
            {
                $buffer .= "<ul id='treeData' style='display: none;'>";
                $buffer .= sprintf("<li data-icon='places/folder.png' id='%s' data-node-type='%s' data-node-id='%s' class='expanded%s'>%s", $node->descendant, $node->node_type_id, $node->node_id, $extra_classes, HTML::link('/specification/nodes/' .$node->descendant, $node->display_name, array (
                        'target' => '_self' 
                )));
                if (!empty($node->children))
                {
                    $buffer .= "<ul>";
                    $buffer .= static::createNavigationTreeHtml($node->children, $nodeId, $themeName, $nodesSelected);
                    $buffer .= "</ul>";
                }
                $buffer .= "</li></ul>";
            } else if ($node->node_type_id ==Nodes::TEST_SUITE_TYPE)
            { // test suite
                $buffer .= sprintf("<li data-icon='actions/document-open.png' id='%s' data-node-type='%s' data-node-id='%s' class='expanded%s'>%s", $node->descendant, $node->node_type_id, $node->node_id, $extra_classes, HTML::link('/specification/nodes/' .$node->descendant, $node->display_name, array (
                        'target' => '_self' 
                )));
                if (!empty($node->children))
                {
                    $buffer .= "<ul>";
                    $buffer .= static::createNavigationTreeHtml($node->children, $nodeId, $themeName, $nodesSelected);
                    $buffer .= "</ul>";
                }
                $buffer .= "</li>";
            } else
            {
                $buffer .= sprintf("<li data-icon='mimetypes/text-x-generic.png' id='%s' data-node-type='%s' data-node-id='%s' class='%s'>%s</li>", $node->descendant, $node->node_type_id, $node->node_id, $extra_classes, HTML::link('/specification/nodes/' .$node->descendant, $node->display_name, array (
                        'target' => '_self' 
                )));
            }
        }
        return $buffer;
    }
    public static function createExecutionNavigationTreeHtml($navigationTree = array(), $nodeId, $themeName = '', $nodesSelected = array(), $filter = array(), $testRunId)
    {
        $buffer = '';
        if (is_null($navigationTree) ||empty($navigationTree))
            return $buffer;
        foreach ( $navigationTree as $node )
        {
            $extra_classes = "";
            if ($node->descendant ==$nodeId &&$node->ancestor ==$nodeId)
            {
                $extra_classes .= " active";
            }
            $nodeTypeId = $node->node_type_id;
            if ($nodeTypeId ==Nodes::TEST_CASE_TYPE &&array_key_exists($node->node_id, $nodesSelected))
            {
                $extra_classes .= " selected";
            }
            if ($node->node_type_id ==Nodes::PROJECT_TYPE)
            {
                $buffer .= "<ul id='treeData' style='display: none;'>";
                $buffer .= sprintf("<li data-icon='places/folder.png' id='%s' data-node-type='%s' data-node-id='%s' class='expanded%s'>%s", $node->descendant, $node->node_type_id, $node->node_id, $extra_classes, HTML::link('/specification/nodes/' .$node->descendant, $node->display_name, array (
                        'target' => '_self' 
                )));
                if (!empty($node->children))
                {
                    $buffer .= "<ul>";
                    $buffer .= static::createExecutionNavigationTreeHtml($node->children, $nodeId, $themeName, $nodesSelected, $filter, $testRunId);
                    $buffer .= "</ul>";
                }
                $buffer .= "</li></ul>";
            } else if ($node->node_type_id ==Nodes::TEST_SUITE_TYPE)
            { // test suite
                $buffer .= sprintf("<li data-icon='actions/document-open.png' id='%s' data-node-type='%s' data-node-id='%s' class='expanded%s'>%s", $node->descendant, $node->node_type_id, $node->node_id, $extra_classes, HTML::link('/specification/nodes/' .$node->descendant, $node->display_name, array (
                        'target' => '_self' 
                )));
                if (!empty($node->children))
                {
                    $buffer .= "<ul>";
                    $buffer .= static::createExecutionNavigationTreeHtml($node->children, $nodeId, $themeName, $nodesSelected, $filter, $testRunId);
                    $buffer .= "</ul>";
                }
                $buffer .= "</li>";
            } else
            {
                if (empty($filter))
                {
                    $buffer .= sprintf("<li data-icon='mimetypes/text-x-generic.png' id='%s' data-node-type='%s' data-node-id='%s' class='%s'>%s</li>", $node->descendant, $node->node_type_id, $node->node_id, $extra_classes, HTML::link('/execution/testruns/' .$testRunId .'/run/testcase/' .$node->node_id, $node->display_name, array (
                            'target' => '_self' 
                    )));
                } else if (array_key_exists($node->node_id, $filter))
                {
                    $buffer .= sprintf("<li data-icon='mimetypes/text-x-generic.png' id='%s' data-node-type='%s' data-node-id='%s' class='%s'>%s</li>", $node->descendant, $node->node_type_id, $node->node_id, $extra_classes, HTML::link('/execution/testruns/' .$testRunId .'/run/testcase/' .$node->node_id, $node->display_name, array (
                            'target' => '_self' 
                    )));
                }
            }
        }
        return $buffer;
    }
    public static function containsNode($tree, $node)
    {
        if ($node ==NULL ||!isset($node))
            return FALSE;
        foreach ( $tree as $entry )
        {
            if ($entry->ancestor ===$node ['ancestor'] &&$entry->descendant ===$node ['descendant'])
            {
                return TRUE;
            }
            if (isset($entry->children) &&!empty($entry->children))
            {
                if (static::containsNode($entry->children, $node))
                {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    public static function getAncestorExecutionType($ancestor)
    {
        list ( $executionType, $nodeId ) = explode("-", $ancestor);
        return (int) $executionType;
    }
    public static function getAncestorNodeId($ancestor)
    {
        list ( $executionType, $nodeId ) = explode("-", $ancestor);
        return $nodeId;
    }
    public static function getDescendantExecutionType($descendant)
    {
        list ( $executionType, $nodeId ) = explode("-", $descendant);
        return (int) $executionType;
    }
    public static function getDescendantNodeId($descendant)
    {
        list ( $executionType, $nodeId ) = explode("-", $descendant);
        return $nodeId;
    }
}

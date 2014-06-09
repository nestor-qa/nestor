<?php namespace Nestor\Repositories;

use Auth, Hash, Validator, DB, Log;
use Magniloquent\Magniloquent\Magniloquent;
use \NavigationTreeNode;

//http://www.mysqlperformanceblog.com/2011/02/14/moving-subtrees-in-closure-table/
class DbNavigationTreeRepository implements NavigationTreeRepository {

	/**
	 * Get all navigation tree nodes
	 *
	 * @return NavigationTreeNode
	 */
	public function all()
	{
		return NavigationTreeNode::all();
	}

	/**
	 * Get a NavigationTreeNode by its ancestor and length, thus returning all
	 * its children, itself included.
	 *
	 * @param string $ancestor
	 * @param int    $length
	 * @return NavigationTreeNode
	 */
	public function children($ancestor, $length = 1)
	{
		Log::info(sprintf('Retrieving children for %s, length %d', $ancestor, $length));

		$children = DB::table('navigation_tree AS a')
			->select(DB::raw("a.*"))
			->leftJoin('navigation_tree AS b', 'a.ancestor', '=', 'b.descendant')
			->where('b.ancestor', '=', "$ancestor")
			->where('a.length', '<=', $length)
			->groupBy('a.ancestor')->groupBy('a.descendant')->groupBy('a.length')
			->orderBy('a.ancestor')
			->get();

		$r = array();
		Magniloquent::unguard();
		foreach ($children as $child)
		{
			$r[] = new NavigationTreeNode(get_object_vars($child));
		}

		return $r;
	}

	/**
	 * Get a list of parents of a NavigationTreeNode and itself.
	 *
	 * @param int $descendant
	 * @return NavigationTreeNode
	 */
	public function parents($descendant)
	{
		Log::info(sprintf('Retrieving parents for %s', $descendant));
// 		return NavigationTreeNode::
// 			where('descendant', $descendant)->
// 			get();
/*
select a.* from navigation_tree a
left join navigation_tree b on b.descendant = a.ancestor
where a.descendant = '2-8'
group by a.ancestor, a.descendant, a.length
order by a.length
 */
		return DB::table('navigation_tree AS a')
				->select(DB::raw("a.*"))
				->leftJoin('navigation_tree AS b', 'b.descendant', '=', 'a.ancestor')
				->where('a.ancestor', '=', "$descendant")
				->groupBy('ancestor')->groupBy('descendant')->groupBy('length')
				->get();
	}

	/**
	 * Get the direct parent of the node.
	 *
	 * @param string $descendant
	 * @return NavigationTreeNode
	 */
	public function parent($descendant)
	{
		return DB::table('navigation_tree AS a')
			->select(DB::raw("a.*"))
			->where('descendant', '=', $descendant)
			->where('ancestor', '<>', $descendant)
			->where('length', '=', 1)
			->first();
	}

	/**
	 * Get a NavigationTreeNode by their primary key.
	 *
	 * @param  int   $ancestorId
	 * @param  int   $descendantId
	 * @return NavigationTreeNode
	 */
	public function find($ancestorId, $descendantId)
	{
		return NavigationTreeNode::
				where('ancestor', '=', $ancestorId)->
				where('descendant', '=', $descendantId)->
				firstOrFail();
	}

	/**
	 * Create a navigation tree node
	 *
	 * @param  string  $ancestor
	 * @param  string  $descendant
	 * @param  int     $node_id
	 * @param  int     $node_type_id
	 * @param  string  $display_name
	 * @return NavigationTreeNode
	 */
	public function create($ancestor, $descendant, $node_id, $node_type_id, $display_name)
	{
		$created_at = new \DateTime();
		$created_at = $created_at->format('Y-m-d H:m:s');
		$updated_at = $created_at;
		//return NavigationTreeNode::create(compact('ancestor', 'descendant', 'length', 'node_id', 'node_type_id', 'parent_id', 'display_name'));
		return DB::insert(sprintf(
				"INSERT INTO %s(" .
				"ancestor, descendant, length, node_id, node_type_id, display_name, created_at, updated_at) " .
				"SELECT t.ancestor, '%s', t.length+1, %d, %d, '%s', '%s', '%s' " .
				"FROM %s AS t " .
				"WHERE t.descendant = '%s' " .
				"UNION ALL " .
				"SELECT '%s', '%s', 0, %d, %d, '%s', '%s', '%s'",

				'navigation_tree',
				$descendant,
				$node_id,
				$node_type_id,
				$display_name,
				$created_at,
				$updated_at,
				'navigation_tree',
				$ancestor,
				$descendant,
				$descendant,
				$node_id,
				$node_type_id,
				$display_name,
				$created_at,
				$updated_at
			));
	}

	/**
	 * Update a navigation tree node
	 *
	 * @param  string  $ancestor
	 * @param  string  $descendant
	 * @param  int     $node_id
	 * @param  int     $node_type_id
	 * @param  string  $display_name
	 * @return NavigationTreeNode
	*/
	public function update($ancestor, $descendant, $node_id, $node_type_id, $display_name)
	{
		$navigation_tree_node = $this->find($ancestor, $descendant);

		$navigation_tree_node->fill(compact('ancestor', 'descendant', 'node_id', 'node_type_id', 'display_name'))->save();

		return $navigation_tree_node;
	}

	/**
	 * Update a navigation tree node by the descendant ID
	 *
	 * @param  string  $descendant
	 * @param  int     $node_id
	 * @param  int     $node_type_id
	 * @param  string  $display_name
	 * @return int
	*/
	public function updateDisplayNameByDescendant($descendantId, $display_name)
	{
		$affectedRows = NavigationTreeNode::where('descendant', '=', $descendantId)
			->update(array('display_name' => $display_name));
		return $affectedRows;
	}

	/**
	 * Delete a navigation tree node
	 *
	 * @param string $descendant
	 */
	public function delete($descendant)
	{
		return NavigationTreeNode::where('descendant', $descendant)->delete();
	}

	/**
	 * Delete a navigation tree node with all its children nodes
	 *
	 * @param int $ancestor
	 * @param int $descendant
	 */
	public function deleteWithAllChildren($ancestor, $descendant)
	{
		return NavigationTreeNode::where('ancestor', $ancestor)
			->orWhere('descendant', $descendant)
			->delete();
	}

	public function move($descendant, $ancestor)
	{
		DB::beginTransaction();
		try 
		{
			$node = $this->find($descendant, $descendant);
			Log::debug($node);
			$this->delete($descendant);
			// $ancestor, $descendant, $node_id, $node_type_id, $display_name
			$this->create($ancestor, $descendant, $node->node_id, $node->node_type_id, $node->display_name);
			DB::commit();
		}
		catch (\Exception $e)
		{
			Log::error($e);
			DB::rollback();
			throw $e;
		}
	}

}

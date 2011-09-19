<?php

	/**
	* PQLite - PHP Query Lite
	* Copyright (c) 2009 Karthik Viswanathan (http://www.pqlite.com).  
	* All rights reserved.
	*
	* Redistribution and use in source form, with or without
	* modification, are permitted provided that the following conditions
	* are met:
	*
	* 1. Redistributions of source code must retain the above copyright
	*    notice, this list of conditions and the following disclaimer.
	*
	* 2. Redistributions must also retain the following acknowledgment:
	*    "This product includes software developed by Karthik Viswanathan
	*    (http://www.pqlite.com/)."
	*
	* THIS SOFTWARE IS PROVIDED BY KARTHIK VISWANATHAN (http://www.pqlite.com)
	* "AS IS" AND ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING, BUT NOT 
	* LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A 
	* PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL KARTHIK VISWANATHAN OR
	* OTHER CONTRIBUTORS TO PQLITE BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
	* SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
	* NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	* LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
	* HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
	* STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	* ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
	* OF THE POSSIBILITY OF SUCH DAMAGE.
	*/
	
	/**
	 * TreeNode represents a node of a tree of any 
	 * given depth.
	 * 
	 * @package default
	 * @author Karthik Viswanathan
	 */
	class TreeNode {
		private $value; // the value stored in this node
		private $parent; // the parent node
		private $children; // the array of child nodes
		
		/**
		 * Constructor for a TreeNode object.
		 *
		 * @param mixed $value - the value to store in the 
		 * TreeNode
		 * @param TreeNode $parent - the parent of this 
		 * TreeNode or null if there is no parent
		 * @param array $children - an array of children of
		 * this TreeNode
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function TreeNode( $value, $parent = null, $children = array() ) {
			$this->value = $value;
			$this->parent = $parent;
			$this->children = $children;
		}
		
		/**
		 * Function to get the stored value of this TreeNode.
		 *
		 * @return mixed - the value of this TreeNode
		 * @author Karthik Viswanathan
		 */
		public function getValue() {
			return $this->value;
		}
		
		/**
		 * Function to set the value of this TreeNode.
		 *
		 * @param mixed $value - the new value of this 
		 * node
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function setValue( $value ) {
			$this->value = $value;
		}
		
		/**
		 * Function to get the parent of this TreeNode.
		 *
		 * @return TreeNode - the parent of this TreeNode
		 * @author Karthik Viswanathan
		 */
		public function getParent() {
			return $this->parent;
		}
		
		/**
		 * Function to set the parent of this TreeNode.
		 *
		 * @param TreeNode $parent - the new parent of 
		 * this TreeNode
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function setParent( $parent ) {
			$this->parent = $parent;
		}
		
		/**
		 * Function to get the children of this TreeNode.
		 *
		 * @return array - the array of all children of
		 * this TreeNode
		 * @author Karthik Viswanathan
		 */
		public function getChildren() {
			return $this->children;
		}
		
		/**
		 * Function to get the child of this TreeNode 
		 * at a specified index.
		 *
		 * @param int $index - the index of the child
		 * @return TreeNode - the child at the given index
		 * @author Karthik Viswanathan
		 */
		public function getIthChild( $index ) {
			return $this->children[ $index ];
		}
		
		/**
		 * Function to set the children array of this 
		 * TreeNode
		 *
		 * @param array $children - the new children 
		 * array of this TreeNode.
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function setChildren( $children ) {
			$this->children = $children;
		}
		
		/**
		 * Function to add a child to this TreeNode.
		 *
		 * @param TreeNode $node - the child to add
		 * to this TreeNode
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function addChild( $node ) {
			$this->children[] = $node;
		}
		
		/**
		 * Function to add a set of children to this 
		 * TreeNode at a specified start index.
		 *
		 * @param array $nodes - the array of nodes 
		 * to add
		 * @param int $indexStart - the index to 
		 * start adding them
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function addChildren( $nodes, $indexStart ) {
			// use splice and merge to add the nodes
			$this->children = array_merge( array_slice( $this->children, 0, $indexStart + 1 ), $nodes, 
			 	array_slice( $this->children, $indexStart + 1 ) );
		}
		
		/**
		 * Function to remove a child of this TreeNode at
		 * a specified index.
		 * 
		 * @param int $index - the index of the child
		 * @return TreeNode - the old child that was located
		 * at the given index
		 * @author Karthik Viswanathan
		 */
		public function remIthChild( $index ) {
			$old = isset( $this->children[ $index ] ) ? $this->children[ $index ] : null;
			
			if( $old ) // if a child existed, remove it
				unset( $this->children[ $index ] );
			return $old;
		}
		
		/**
		 * Function to remove a given child node from 
		 * this TreeNode
		 *
		 * @param TreeNode $node - the node to remove
		 * @return mixed - the key of the removed node, 
		 * or -1 if node was not in the array of children
		 * @author Karthik Viswanathan
		 */
		public function remChild( $node ) {
			if( ( $key = array_search( $node, $this->children ) ) !== false ) { // if the child is present
				unset( $this->children[ $key ] );
				return $key;
			}
			
			return -1;
		}
		
		/**
		 * Function to remove all the children from this 
		 * TreeNode.
		 *
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function remAllChildren() {
			foreach( $this->children as $child )
				$child->setParent( null ); // unbind the parent from all children
			
			$this->children = array();
		}
		
		/**
		 * Function to represent this TreeNode as a string.
		 *
		 * @return string - the representation of this 
		 * TreeNode
		 * @author Karthik Viswanathan
		 */
		function __toString() {
			return print_r( $this, true ); // the second parameter makes print_r return the output
		}
	}

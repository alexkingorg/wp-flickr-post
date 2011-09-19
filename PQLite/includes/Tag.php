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
	 * Class that represents an HTML tag.
	 *
	 * @package default
	 * @author Karthik Viswanathan
	 */
	class Tag {
		private $name; // the tag name
		private $attr = array(); // the attributes and their values
		private $node; // the node this tag corresponds with
		
		private $closing; // ends an open tag
		private $selfClosed; // closed by itself with "/>"
		
		/**
		 * Constructor for a tag object.
		 *
		 * @param string $name - the name of this tag
		 * @param array $attr - the array of key/value attribute pairs
		 * @param int $closing - true if this tag closes another tag or false otherwise
		 * @param int $selfClosed - true if this tag closes itself or false otherwise
		 * @param TreeNode $node - the TreeNode that corresponds with this tag
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function Tag( $name, $attr, $closing = false, $selfClosed = false, &$node = null ) {
			$this->name = $name;
			$this->attr = $attr;
			
			$this->closing = $closing;
			$this->selfClosed = $selfClosed;
			$this->node = $node;
		}
		
		/**
		 * Function to get the name of this tag.
		 *
		 * @return string - the name
		 * @author Karthik Viswanathan
		 */
		public function getName() {
			return $this->name;
		}
		
		/**
		 * Function to get a certain attribute of 
		 * this tag.
		 *
		 * @param string $name - the name of the attribute
		 * @return string - the value of the attribute
		 * @author Karthik Viswanathan
		 */
		public function getAttr( $name ) {
			return isset( $this->attr[ $name ] ) ? $this->attr[ $name ] : '';
		}
		
		/**
		 * Function to get the array of all attributes
		 * of this tag.
		 *
		 * @return array - the array of all attributes
		 * @author Karthik Viswanathan
		 */
		public function getAllAttr() {
			return $this->attr;
		}
		
		/**
		 * Function to set an attribute of this tag.
		 * 
		 * @param string $name - the name of the attribute
		 * @param string $value - the value of the attribute
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function setAttr( $name, $value ) {
			$this->attr[ $name ] = $value;
		}
		
		/**
		 * Function to remove an attribute from this tag.
		 * 
		 * @param string $name - the name of the attribute 
		 * to remove
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function remAttr( $name ) {
			unset( $this->attr[ $name ] );
		}
		
		/**
		 * Function to add a class to this tag.
		 * 
		 * @param string $class - the class to add
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function addClass( $class ) {
			if( isset( $this->attr[ 'class' ] ) )
				$this->attr[ 'class' ] .= " $class";
			else
				$this->attr[ 'class' ] = $class;
		}
		
		/**
		 * Function to remove a class from this tag.
		 * 
		 * @param string $class - the class to remove
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function remClass( $class ) {
			if( isset( $this->attr[ 'class' ] ) ) {
				$classes = explode( ' ', $this->attr[ 'class' ] ); // get the array of classes
				
				if( ( $key = array_search( $class, $classes ) ) !== false )
					unset( $classes[ $key ] ); // unset the one we are looking for
				
				if( count( $classes ) > 0 )
					$this->attr[ 'class' ] = implode( ' ', $classes ); // recreate the classes
				else
					unset( $this->attr[ 'class' ] );
			}
		}
		
		/**
		 * Function to check if this Tag closes another 
		 * Tag.
		 *
		 * @return int - true if this Tag closes another 
		 * Tag or false otherwise
		 * @author Karthik Viswanathan
		 */
		public function isClosing() {
			return $this->closing;
		}
		
		/**
		 * Function to set whether this Tag closes another
		 * Tag
		 *
		 * @param int $closing - true if this Tag closes 
		 * another Tag or false otherwise
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function setClosing( $closing ) {
			$this->closing = $closing;
		}
		
		/**
		 * Function to check if this Tag closes itself
		 *
		 * @return int - true if this Tag closes itself
		 * or false otherwise
		 * @author Karthik Viswanathan
		 */
		public function isSelfClosed() {
			return $this->selfClosed;
		}
		
		/**
		 * Function to set whether this Tag closes itself.
		 *
		 * @param int $closing - true if this Tag closes 
		 * itself or false otherwise
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function setSelfClosed( $selfClosed ) {
			$this->selfClosed = $selfClosed;
		}
		
		/**
		 * Function to get the TreeNode corresponding 
		 * with this tag.
		 *
		 * @return TreeNode - the TreeNode that corresponds
		 * with this Tag
		 * @author Karthik Viswanathan
		 */
		public function getNode() {
			return $this->node;
		}
		
		/**
		 * Function to set the TreeNode that corresponds 
		 * with this tag.
		 *
		 * @param TreeNode $node - the new TreeNode that 
		 * corresponds with this tag
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function setNode( &$node ) {
			$this->node = $node;
		}
		
		/**
		 * Function to get the HTML of this tag based 
		 * on its name and attributes.
		 *
		 * @return string - the HTML of this tag
		 * @author Karthik Viswanathan
		 */
		public function getTagHTML() {
			$str = '<'; // tag begin
			if( $this->closing )
				$str .= '/';
		
			$str .= $this->getName(); // name of tag
			foreach( $this->attr as $name => $value ) {
				$str .= " $name=\"$value\""; // adding attriutes
			}
		
			if( $this->selfClosed ) {
				$str .= ' />';
				return $str;
			}
			else
				$str .= '>'; // tag end
			
			return $str;
		}
		
		/**
		 * Function to get the string representation of
		 * this Tag.
		 *
		 * @return string - the representation of this 
		 * Tag
		 * @author Karthik Viswanathan
		 */
		public function __toString() {
			return $this->getTagHTML();
		}
	}

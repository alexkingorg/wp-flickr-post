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
	 * Class that represents a set of tags.
	 *
	 * @package default
	 * @author Karthik Viswanathan
	 */
	class TagArray {
		private $pq; // the corresponding PQLite object
		private $numTags; // number of tags
		private $tagArray; // array of tags
		private $firstTag; // the first tag in the array
		
		/**
		 * @desc Constructor for a TagArray object.
		 * @slug Tag Array Constructor
		 *
		 * @param string $pq - the corresponding PQLite object
		 * @param string $tagArray - the array of tags
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function TagArray( $pq, $tagArray ) {
			$this->pq = $pq;
			$this->numTags = count( $tagArray );
			$this->tagArray = $tagArray;
			$this->firstTag = ( $this->numTags > 0 ) ? $tagArray[0] : null; // only set this if it exists
		}
		
		/**
		 * @desc Function to only target one Tag at a given index in this TagArray.
		 * @slug Get
		 *
		 * @param int $index - the index of the Tag, starting from 0
		 * @return TagArray - a TagArray containing only the Tag at the specified index.
		 * @author Karthik Viswanathan
		 */
		public function get( $index ) {
			if( $index < $this->numTags ) // inside bounds
				return new TagArray( $this->pq, array( $this->tagArray[ $index ] ) );
			return new TagArray( $this->pq, array() );
		}
		
		/**
		 * @desc Function to get the children of this Tag.
		 * @slug Get Children
		 * 
		 * @return TagArray - the children tags
		 * @author Karthik Viswanathan
		 */
		public function getChildren() {
			if( $this->numTags > 0 ) {
				$node = $this->firstTag->getNode(); // the corresponding node contains the children
				$tags = array();
				
				foreach( $node->getChildren() as $child ) {
					$value = $child->getValue();
					if( $value instanceof Tag )
						$tags[] = $value; // get the Tag objects and append them to the array
				}
				return new TagArray( $this->pq, $tags );
			}
			
			return new TagArray( $this->pq, array() );
		}
		
		/**
		 * @desc Function to get the inner HTML of the first tag in this TagArray.
		 * @slug Get Inner HTML
		 *
		 * @return string - the inner HTML of the first tag
		 * @author Karthik Viswanathan
		 */
		public function getInnerHTML() {
			if( $this->numTags > 0 )
				return $this->pq->requestHTML( $this->firstTag->getNode() );
			return '';
		}
		
		/**
		 * @desc Function to get the outer HTML (inner HTML + the tag itself) of the first tag in this TagArray.
		 * @slug Set Outer HTML
		 *
		 * @return string - the outer HTML of the first tag.
		 * @author Karthik Viswanathan
		 */
		public function getOuterHTML() {
			if( $this->numTags > 0 )
				return $this->pq->requestHTML( $this->firstTag->getNode(), false );
			return '';
		}
		
		/**
		 * @desc Function to set the inner HTML of each Tag in this TagArray.
		 * @slug Set Inner HTML
		 *
		 * @param string $html - the new HTML
		 * @return TagArray - this object for chaining
		 * @author Karthik Viswanathan
		 */
		public function setInnerHTML( $html ) {
			foreach( $this->tagArray as $tag )
				$this->pq->updateNode( $tag->getNode(), $html );
			return $this;
		}
		
		/**
		 * @desc Function to set the outer HTML of each Tag in this TagArray. This is equivalent to completely replacing each tag.
		 * @slug Set Outer HTML
		 *
		 * @param string $html - the new outer HTML
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function setOuterHTML( $html ) {
			foreach( $this->tagArray as $tag )
				$this->pq->updateNode( $tag->getNode(), $html, true );
			// don't return $this because the 
			// selector won't be valid
		}
		
		/**
		 * @desc Function to remove all Tags in this TagArray from the HTML.
		 * @slug Remove Self
		 * 
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function remSelf() {
			$this->setOuterHTML( '' );
		}
		
		/**
		 * @desc Function to get a specified attribute from the first Tag in this TagArray.
		 * @slug Get Attribute
		 * 
		 * @param string $name - the name of the attribute
		 * @return string - the value of the attribute
		 * @author Karthik Viswanathan
		 */
		public function getAttr( $name ) {
			if( $this->numTags > 0 )
				return $this->firstTag->getAttr( $name );
			return '';
		}
		
		/**
		 * @desc Function set an attribute of every Tag in this TagArray.
		 * @slug Set Attribute
		 *
		 * @param string $name - the name of the attribute
		 * @param string $value - the value of this attribute
		 * @return TagArray - this object for chaining
		 * @author Karthik Viswanathan
		 */
		public function setAttr( $name, $value ) {
			foreach( $this->tagArray as $tag )
				$tag->setAttr( $name, $value );
			return $this;
		}
		
		/**
		 * @desc Function to remove an attribute from every Tag in this TagArray.
		 * @slug Remove Attribute
		 * 
		 * @param string $name - the name of the attribute to remove.
		 * @return TagArray - this object for chaining
		 * @author Karthik Viswanathan
		 */
		public function remAttr( $name ) {
			foreach( $this->tagArray as $tag )
				$tag->remAttr( $name );
			return $this;
		}
		
		/**
		 * @desc Function to add a class to each Tag in this TagArray.
		 * @slug Add Class
		 *
		 * @param string $class - the class to add
		 * @return TagArray - this object for chaining
		 * @author Karthik Viswanathan
		 */
		public function addClass( $class ) {
			foreach( $this->tagArray as $tag )
				$tag->addClass( $class );
			return $this;
		}
		
		/**
		 * @desc Function to remove a class from each Tag in this TagArray.
		 * @slug Remove Class
		 *
		 * @param string $class - the class to remove
		 * @return TagArray - this object for chaining
		 * @author Karthik Viswanathan
		 */
		public function remClass( $class ) {
			foreach( $this->tagArray as $tag )
				$tag->remClass( $class );
			return $this;
		}
		
		/**
		 * @desc Function to get the number of Tags in this TagArray.
		 * @slug Get Number of Tags
		 *
		 * @return int - the number of tags
		 * @author Karthik Viswanathan
		 */
		public function getNumTags() {
			return $this->numTags;
		}
		
		/**
		 * @desc This applies a specific function to each element of this TagArray.
		 * @slug Each
		 *
		 * @param string $function - the function name which takes 1 parameter (the TagArray object)
		 * @return TagArray - this object for chaining
		 * @author Karthik Viswanathan
		 */
		public function each( $function ) {
			for( $i = 0; $i < $this->numTags; $i++ )
				call_user_func( $function, $this->get( $i ) ); // call the function with one parameter
			return $this;
		}
		
		/**
		 * @desc Function to get a string representation of this TagArray.
		 *
		 * @return string - the representation of this TagArray
		 * @author Karthik Viswanathan
		 */
		public function __toString() { // @nodoc
			return 'TagArray Object [you may find methods associated with it at http://pqlite.com/docs/]'; // users generally shouldn't print this out--direct them to help if they do
		}
	}

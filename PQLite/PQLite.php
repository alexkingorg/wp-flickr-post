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

	// dependencies
	require_once( 'includes/Interval.php' );
	require_once( 'includes/Tag.php' );
	require_once( 'includes/TagArray.php' );
	require_once( 'includes/TreeNode.php' );
	
	/**
	 * PQLite is used to traverse HTML in order to 
	 * to manipulate and gain information from it.
	 * 
	 * @package default
	 * @author Karthik Viswanathan
	 */
	class PQLite {
		private $html; // actual html content
		private $root; // root node of tree
		
		// arrays to store pre-processed information
		private $allTags = array();
		private $tagsByName = array();
		private $tagsByID = array();
		private $tagsByClass = array();
		
		/**
		 * @desc Constructor for a PQLite object. This requires a string of HTML to parse.
		 * @slug PQLite Constructor
		 * 
		 * @param string $html - the HTML
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function PQLite( $html ) {
			$this->html = $html;
			$this->root = new TreeNode( 'root' );
			$this->extractTags( $html, $this->root );
		}
		
		/**
		 * @desc This is the main selector function. Pass in any tag/id/class (or combination) CSS selector and this will return a TagArray of the elements inside the HTML that match it.
		 * @slug Find
		 *
		 * @param string $str - the selector string
		 * @return TagArray - a TagArray that contains the targeted elements
		 * @author Karthik Viswanathan
		 */
		public function find( $str ) {
			$parts = explode( " ", $str );
			$size = count( $parts );
			
			$inside = array();
			$selector = array();
			
			foreach( $parts as $key => $part ) {
				if( $key == $size - 1 )
					$this->parseSelector( $part, $selector );
				else
					$inside[] = $this->parseSelector( $part );
			}
			
			return $this->getTagArray( $inside, $selector );
		}
		
		/**
		 * @desc This function gets the HTML from this PQLite object. It will also include any modifications.
		 * @slug Get HTML
		 *
		 * @return string - the new HTML
		 * @author Karthik Viswanathan
		 */
		public function getHTML() {
			return $this->requestHTML( $this->root );
		}
		
		/**
		 * @desc Function to get the original, unchanged HTML from this PQLite object.
		 * @slug Get Original HTML
		 *
		 * @return string - the original HTML
		 * @author Karthik Viswanathan
		 */
		public function getOriginalHTML() {
			return $this->html;
		}
		
		/**
		 * @desc This is used to generate HTML by passing in a given node in the tree. Currently, it is a public function because the TagArray class uses it. Users will generally not need to call this function.
		 *
		 * @param TreeNode $node - the node
		 * @param int $onlyChildren - if the node itself should be used in the generation, set this to false. otherwise, only the children will be used. 
		 * @return string - the generated HTML
		 * @author Karthik Viswanathan
		 */
		public function requestHTML( $node, $onlyChildren = true ) { // @nodoc
			if( $onlyChildren ) {
				$str = '';
				foreach( $node->getChildren() as $child ) {
					$str .= $this->generateCode( $child );
				}
			}
			else
				$str = $this->generateCode( $node );
		
			return $str;
		}
		
		/**
		 * @desc Function to update a node with new HTML. This is a public function because the TagArray class uses it. Users will not need to use this function.
		 * 
		 * @param TreeNode $node - the node to update
		 * @param string $html - the new HTML
		 * @param string $fullReplace - replace the node itself (true) or just its children (false)
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function updateNode( &$node, $html, $fullReplace = false ) { // @nodoc
			$root = new TreeNode( 'root' ); // holder node
			$this->extractTags( $html, $root );
			
			if( $fullReplace ) {
				$newNode = $node->getParent();
				
				$index = $newNode->remChild( $node );
				$node->setParent( null );
				
				// array elements were shifted back one
				// thus, it is necessary to use $index - 1
				// to insert the new nodes in the right place
				$newNode->addChildren( $root->getChildren(), $index - 1 );
				foreach( $root->getChildren() as $child )
					$child->setParent( $newNode ); // these children haven't had their parent set
			}
			else {
				$node->remAllChildren();
				
				// copy all the child nodes
				foreach( $root->getChildren() as $child ) {
					$node->addChild( $child );
					$child->setParent( $node );
				}
			}
		}
		
		/**
		 * Helper function to get a TagArray given info about the 
		 * selector.
		 *
		 * @param string $inside - array of information dealing with 
		 * multiple space-separated selectors
		 * @param string $selector - the last selector in the set
		 * @return TagArray - the set of elements that match the 
		 * selector
		 * @author Karthik Viswanathan
		 */
		private function getTagArray( $inside, $selector ) {
			$parents = array();
			foreach( $inside as $subSelector ) {
				$parents[] = $this->getTagsFromSelector( $subSelector );
			}
			
			$len = count( $parents );
			$actualTags = $this->getTagsFromSelector( $selector );
			
			$tagArray = array();
			foreach( $actualTags as $tag ) {
				$parentIndex = $len - 1; // work backwards
				
				$node = $tag->getNode();
				$parentNode = $node->getParent();
				
				while( $parentIndex >= 0 ) {
					while( $parentNode != null && !in_array( $parentNode->getValue(), $parents[ $parentIndex ] ) )
						$parentNode = $parentNode->getParent();
					
					if( $parentNode == null )
						break; // didn't find the selector
					
					$parentNode = $parentNode->getParent(); // start the next search from the parent
					$parentIndex--; // look for the next parent on the list
				}
				
				if( $parentIndex < 0 ) {
					$tagArray[] = $tag;
				}
			}
			
			return new TagArray( $this, $tagArray );
		}
		
		/**
		 * Auxiliary function to parse out each individual tag
		 * from a selector and return them as an array.
		 * 
		 * @param array $selector - the array of selector information
		 * @return array - the set of tags that match the given
		 * selector
		 * @author Karthik Viswanathan
		 */
		private function getTagsFromSelector( $selector ) {
			$interArr = array();
			$size = count( $selector );
			
			if( isset( $selector[ 'tag' ] ) ) { // there is a tag involved
				if( isset( $this->tagsByName[ $selector[ 'tag' ] ] ) )
					$interArr[] = $this->tagsByName[ $selector[ 'tag' ] ]; // find the tag in the list of tags
				else
					$interArr[] = array(); // there were no matching tags
			}
			
			if( isset( $selector[ 'id' ] ) ) {
				if( isset( $this->tagsByID[ $selector[ 'id' ] ] ) )
					$interArr[] = $this->tagsByID[ $selector[ 'id' ] ];
				else
					$interArr[] = array();
			}
			
			if( isset( $selector[ 'class' ] ) ) {
				if( isset( $this->tagsByClass[ $selector[ 'class' ] ] ) )
					$interArr[] = $this->tagsByClass[ $selector[ 'class' ] ];
				else
					$interArr[] = array();
			}
			
			if( $size > 1 ) {
				$eval = '$array = array_intersect(';
				for( $i = 0; $i < $size; $i++ )
					$eval .= ' $interArr[' . $i . '], ';
				
				$eval = substr( $eval, 0, strlen( $eval ) - 2 ) . ' );';
				eval( $eval ); // intersecting the arrays will return the set of tags
			}
			else
				$array = $interArr[0]; // only one array--no need to intersect
			
			return $array;
		}
		
		/**
		 * Function to parse a selector into an array of information
		 * 
		 * @param string $str - the selector string
		 * @param array $array - the array to put the information in
		 * @return array - the information is also returned
		 * @author Karthik Viswanathan
		 */
		private function parseSelector( $str, &$array = array() ) {
			if( !preg_match( '/^(#|\.)?[a-zA-Z0-9_\-]+((#|\.)[a-zA-Z0-9_\-]+){0,2}$/', $str ) )
				return false;
			
			$parts = preg_split( '/(#|\.)/', $str, -1, PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_DELIM_CAPTURE );
			
			if( count( $parts ) > 5 ) // 3 parts of split, 2 delims
				return false; // too many selectors
			
			foreach( $parts as $part ) {
				if( !$part[0] ) // selector starts with # or .
					continue;
				if( $part[0] == '#' ) // id selector
					$key = 'id';
				else if( $part[0] == '.' ) // class selector
					$key = 'class';
				else {
					if( !isset( $key ) )
						$key = 'tag'; // no selector must mean tag
					
					// fill the area with selector type and 
					// value
					$array[ $key ] = substr( $str, $part[1], strlen( $part[0] ) );
					unset( $key );
				}
			}
			
			return $array;
		}
		
		/**
		 * Main helper function to extract the tags out of a given 
		 * string of HTML.
		 *
		 * @param string $html - the HTML 
		 * @param string $node - the root node to extract the tags into
		 * @return void
		 * @author Karthik Viswanathan
		 */
		private function extractTags( $html, &$node ) {
			// $html = stripComments( $html ); // don't want comments while parsing!
			$commentLocations = $this->findComments( $html );
			$commentIndex = 0;
			$locationLen = count( $commentLocations );
		
			$offset = 0; // current index to start searching from
			$start = '<'; // find opening tag
			$end = '>'; // find close tag
		
			$len = strlen( $html );
			$parentNode = $node;
			
			while( $offset < $len ) {
				$startPos = strpos( $html, $start, $offset );
			
				if( $commentIndex < $locationLen && $commentLocations[ $commentIndex ]->contains( $startPos ) ) {
					$interval = $commentLocations[ $commentIndex ];
					$parentNode = $this->createTextNode( substr( $html, $offset, $interval->getEnd() - $offset + 1 ), $parentNode );
				
					$offset = $interval->getEnd() + 1;
					$commentIndex++;
				
					continue;
				}
			
				// validation
				if( $startPos === false )
					break;
			
				if( $startPos - $offset > 0 )
					// don't capture the '<' of the next tag
					$parentNode = $this->createTextNode( substr( $html, $offset, $startPos - $offset ), $parentNode );
			
				$offset = $startPos + 1; // start searching past this position
				$endPos = strpos( $html, $end, $offset );
			
				if( $endPos === false )
					exit( 'The HTML did not contain a correct tag (the closing sign (>) was not present)' );
			
				$offset = $endPos + 1;
			
				// need to add one because indicies start at 0
				$tagLen = $endPos - $startPos + 1;
				$tagText = substr( $html, $startPos, $tagLen );
			
				$parentNode = $this->parseTag( $tagText, $parentNode );
			}
			
			if( $offset < $len )
				$parentNode = $this->createTextNode( substr( $html, $offset ), $parentNode );
		}
	
		/**
		 * Helper function to generate the code using a given 
		 * root node.
		 *
		 * @param TreeNode $root - the root node
		 * @return string - the generated code
		 * @author Karthik Viswanathan
		 */
		private function generateCode( $root ) {
			$node = $root->getValue();
			if( $node instanceof Tag ) {
				$str = $node->getTagHTML(); // open tag
			
				foreach( $root->getChildren() as $child ) {
					$str .= $this->generateCode( $child, $str ); // everything inside tag
				}
			
				if( !$node->isSelfClosed() )
					$str .= '</' . $node->getName() . '>'; // end tag
			}
			else
				$str = $node; // node is just text
		
			return $str;
		}
	
		/**
		 * Helper function to parse a tag given the text and 
		 * the parent node in the tree.
		 *
		 * @param string $tag - the text
		 * @param TreeNode $parentNode - the parent node
		 * @return TreeNode - the new parent node to use
		 * @author Karthik Viswanathan
		 */
		private function parseTag( $tag, $parentNode ) {
			$regex = '%</?(\w+)(((\s+[a-zA-Z0-9_\-]+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+)\s*|\s*)/?>%s';
		
			if( preg_match( $regex, $tag, $matches ) ) {
				// find the tag name and attribute string 
				// to pass to createTag()
				$name = $matches[1];

				$attr = '';
				if( isset( $matches[3] ) )
					$attr = $matches[3];
			
				$tag = $this->createTag( $name, $attr, $matches[0] );
				$parentNode = $this->addTag( $tag, $parentNode ); // parse the tag into the tree
			}
			else {
				$parentNode = $this->createTextNode( $tag, $parentNode );
			}
		
			return $parentNode;
		}
	
		/**
		 * Auxiliary function to create a tag based off of a name, 
		 * attribute text, and full tag text.
		 *
		 * @param string $name - the name of the tag
		 * @param string $attr - the string of attributes
		 * @param string $tagText - the full tag text
		 * @return Tag - a tag that corresponds with the information
		 * given
		 * @author Karthik Viswanathan
		 */
		private function createTag( $name, $attr, $tagText ) {
			$quote = false; // whether a quote is active or not
		
			$attr = trim( $attr );
			$len = strlen( $attr );
		
			$splitOn = array(); // positions to split the string on
			for( $pos = 0; $pos < $len; $pos++ ) {
				$char = $attr[ $pos ];
			
				if( $char == '"' || $char == "'" ) {
					if( !isset( $lastQuote ) ) {
						$lastQuote = $char;
						$quote = !$quote;
					}
					else if( $char == $lastQuote ) { // lastQuote not set and correct end quote
						unset( $lastQuote );
						$quote = !$quote;
					}
				}
				else if( ctype_space( $char ) && !$quote )
					$splitOn[] = $pos;
			}
		
			// have to split on the second to last character
			// to include the final attribute
			$splitOn[] = strlen( $tagText ) - 1;

			// reset pos to find all substrings
			$tag = new Tag( $name, array() );
			$pos = 0;
		
			foreach( $splitOn as $splitPos ) {
				$part = substr( $attr, $pos, $splitPos - $pos + 1 );
				$delimPos = strpos( $part, '=' ); // first equal sign signifies name/value relationship
			
				// ignore = sign when parsing
				$attrName = substr( $part, 0, $delimPos );
				$attrVal = substr( $part, $delimPos + 1 ); 
			
				if( $attrName )
					$tag->setAttr( trim( $attrName ), $this->removeQuotes( trim( $attrVal ) ) ); // add the attributes
			
				$pos = $splitPos;
			}
		
			// set the status of the tag (closed, self-closed)
			if( strpos( $tagText, '/' ) == 1 )
				$tag->setClosing( true );
			else if( strrpos( $tagText, '/' ) == strlen( $tagText ) - 2 )
				$tag->setSelfClosed( true );
		
			return $tag;
		}
	
		/**
		 * This function is used to remove quotes from the beginning
		 * and end of a given string if they exist.
		 *
		 * @param string $str - the string
		 * @return string - the new string with quotes stripped
		 * @author Karthik Viswanathan
		 */
		private function removeQuotes( $str ) {
			// strip quotes from beginning and end if present
			if( strpos( $str, '"' ) === 0 || strpos( $str, "'" ) === 0 )
				$str = substr( $str, 1, strlen( $str ) - 2 );
			return $str;
		}
	
		/**
		 * Helper function to add a tag to a tree.
		 *
		 * @param Tag $tag - the Tag to add
		 * @param TreeNode $parent - the current parent node
		 * @return TreeNode - the new parent node
		 * @author Karthik Viswanathan
		 */
		private function addTag( $tag, $parent ) {
			if( $tag->isClosing() ) {
				// if there is some discrepency on what the tag closes,
				// just move up the tree until a similar opening tag is found
				// if no similar opening tag can be found, assume that 
				// the parent tag is still unclosed
				$findParent = $parent;
				while( $findParent != null && $findParent->getValue()->getName() != $tag->getName() ) {
					$findParent = $findParent->getParent();
					if( $findParent->getValue() != 'root' )
						break;
				}
			
				if( $findParent != null )	
					return $findParent->getParent();
				return $parent;
			}
		
			$node = $this->appendToTree( $tag, $parent );
			$tag->setNode( $node );
			$this->populateTagArray( $tag );
		
			// self closed means the parent is still unclosed
			if( $tag->isSelfClosed() )
				return $parent;
			return $node;
		}
		
		/**
		 * Auxiliary function to populate the pre-processed 
		 * arrays given a certain tag.
		 *
		 * @param Tag $tag - the Tag to process
		 * @return void
		 * @author Karthik Viswanathan
		 */
		private function populateTagArray( $tag ) {
			$this->allTags[] = $tag;
			
			$this->updateSpecialArray( $tag, $tag->getName(), 1 );
			
			$id = $tag->getAttr( 'id' );
			$class = $tag->getAttr( 'class' );
			
			if( $id )
				$this->updateSpecialArray( $tag, $id, 2 );
			if( $class ) {
				$parts = explode( ' ', $class ); // multiple classes
				
				foreach( $parts as $value )
					$this->updateSpecialArray( $tag, $value, 3 ); // update with each class
			}
		}
		
		/**
		 * Helper function to update a given array with 
		 * information.
		 *
		 * @param Tag $tag - the Tag to add to the array
		 * @param string $value - the key to use in the array
		 * @param int $which - which array to put the information
		 * into (1 corresponds to tags by name, 2 to tags by id, 
		 * and 3 to tags by class)
		 * @return void
		 * @author Karthik Viswanathan
		 */
		private function updateSpecialArray( $tag, $value, $which ) {
			$array = $this->tagsByName;
			switch( $which ) {
				case 1:
					$array = &$this->tagsByName;
					break;
				case 2:
					$array = &$this->tagsByID;
					break;
				case 3:
					$array = &$this->tagsByClass;
					break;
			}
			
			if( isset( $array[ $value ] ) )
				$array[ $value ][] = $tag; // update the already existant array
			else
				$array[ $value ] = array( $tag ); // create the array
		}
	
		/**
		 * Function to create a TreeNode based off the 
		 * given value and add it to the tree with a 
		 * set parent.
		 *
		 * @param mixed $value - the value of the created
		 * TreeNode
		 * @param TreeNode $parent - the parent node or null
		 * if there is no parent
		 * @return void
		 * @author Karthik Viswanathan
		 */
		private function appendToTree( $value, $parent ) {
			$node = null;
		
			// if there is no parent, make the tag 
			// without one.
			if( $parent == null )
				$node = new TreeNode( $value );
			else {
				$node = new TreeNode( $value, $parent );
				$parent->addChild( $node );
			}
		
			return $node;
		}
	
		/**
		 * Auxiliary function to create a TreeNode with a 
		 * given string value.
		 *
		 * @param string $value - the value stored inside the 
		 * TreeNode
		 * @param TreeNode $parent - the parent of the created 
		 * TreeNode or null if there is no parent
		 * @return void
		 * @author Karthik Viswanathan
		 */
		private function createTextNode( $value, $parent ) {
			$this->appendToTree( $value, $parent );
			return $parent;
		}
	
		/**
		 * Helper Function to check if a given string is an
		 * HTML comment. This is currently not in use, but 
		 * will be applied in a future version of PQLite.
		 *
		 * @param string $str - the string to check
		 * @return int - true if it is a comment or false 
		 * otherwise
		 * @author Karthik Viswanathan
		 */
		private function isComment( $str ) {
			$openComment = strpos( $str, '<!--' );
			$closeComment = strpos( $str, '-->' );
		
			// use or (||) instead of and (&&) so this also takes care of IE conditional comments
			return $openComment === 0 || ( $closeComment >= 0 && $closeComment === ( strlen( $str ) - 3 ) );
		}
	
		/**
		 * Auxiliary function to find all the comments in 
		 * the HTML and return an array with their positions.
		 *
		 * @param string $html - the HTML to find comments for
		 * @return array - the positions of the comments
		 * @author Karthik Viswanathan
		 */
		private function findComments( $html ) {
			$offset = 0; // current index to start searching from
			$start = '<!--'; // find opening comment
			$end = '-->'; // find close comment
		
			$startLen = strlen( $start );
			$endLen = strlen( $end );
		
			$len = strlen( $html );
			$locations = array();
			
			while( $offset < $len ) {
				$startPos = strpos( $html, $start, $offset );
			
				// validation
				if( $startPos === false )
					break;
			
				$offset = $startPos + $startLen; // start searching past this position
				$endPos = strpos( $html, $end, $offset );
			
				if( $endPos === false )
					exit( 'The HTML contained an unclosed comment (no -->)' );
			
				$offset = $endPos + 1;
				$locations[] = new Interval( $startPos, $endPos + $endLen );
			}
		
			return $locations;
		}
	
		/**
		 * Helper function to strip the comments out of a 
		 * string of HTML. This is currently not in use, but 
		 * will be applied in a future version of PQLite.
		 *
		 * @param string $html - the HTML to strip comments from
		 * @return string - the new HTML with no comments
		 * @author Karthik Viswanathan
		 */
		private function stripComments( $html ) {
			$offset = 0; // current index to start searching from
			$start = '<!--'; // find opening comment
			$end = '-->'; // find close comment
		
			$startLen = strlen( $start );
			$endLen = strlen( $end );
		
			while( $offset < strlen( $html ) ) {
				$startPos = strpos( $html, $start, $offset );
			
				// validation
				if( $startPos === false )
					break;
			
				$offset = $startPos + $startLen; // start searching past this position
			
				$endPos = strpos( $html, $end, $offset );
			
				if( $endPos === false )
					exit( 'The HTML contained an unclosed comment (no -->)' );
			
				$tagLen = $endPos + $endLen - $startPos;
				$html = substr( $html, 0, $startPos ) . substr( $html, $startPos + $tagLen );
			
				$offset = $startPos; // token has been removed
			}
		
			return trim( $html );
		}
	}

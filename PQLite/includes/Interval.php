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
	 * Class that represents an interval that 
	 * has a given start and end.
	 *
	 * @package default
	 * @author Karthik Viswanathan
	 */
	class Interval {
		// bounds of the interval
		private $start;
		private $end;
	
		/**
		 * Constructor for an Interval object.
		 * 
		 * @param int $start - the start of the interval
		 * @param int $end - the end of the interval
		 * @return void
		 * @author Karthik Viswanathan
		 */
		public function Interval( $start, $end ) {
			$this->start = $start;
			$this->end = $end;
		}
		
		/**
		 * Function to get the start of this Interval.
		 * 
		 * @return int - the start of this Interval
		 * @author Karthik Viswanathan
		 */
		public function getStart() {
			return $this->start;
		}
		
		/**
		 * Function to get the end of this Interval.
		 * 
		 * @return int - the end of this Interval
		 * @author Karthik Viswanathan
		 */
		public function getEnd() {
			return $this->end;
		}
		
		/**
		 * Function to get the length of this Interval.
		 * 
		 * @return int - the length of this interval
		 * @author Karthik Viswanathan
		 */
		public function getLength() {
			return $this->end - $this->start + 1; // +1 because getLength() is used in string manipulations
		}
		
		/**
		 * Function to check whether a value in contained 
		 * in this Interval.
		 * 
		 * @param int $value - the value to check
		 * @return int - true if the value is contained 
		 * or false otherwise
		 * @author Karthik Viswanathan
		 */
		public function contains( $value ) {
			return $value >= $this->start && $value <= $this->end;
		}
		
		/**
		 * Function to represent this Interval object as a 
		 * string.
		 * 
		 * @return string - the representation of this 
		 * Interval.
		 * @author Karthik Viswanathan
		 */
		public function __toString() {
			return "Interval Object [$start => $end]";
		}
	}

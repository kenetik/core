<?php
/*
PHPDoctor: The PHP Documentation Creator
Copyright (C) 2004 Paul James <paul@peej.co.uk>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require_once('linkPlainTag.php');

/** Represents an inline link tag.
 *
 * @package PHPDoctor\Tags
 */
class LinkTag extends LinkPlainTag
{

	/**
	 * Constructor
	 *
	 * @param str text The contents of the tag
	 * @param str[] data Reference to doc comment data array
	 * @param RootDoc root The root object
	 */
	function linkTag($text, &$data, &$root, &$formatter)
    {
		parent::linkPlainTag($text, $data, $root, $formatter);
		$this->_name = '@link';
	}
	
	/** Get the plain text value of the tag.
	 *
	 * @return str
	 */
	function plainText()
    {
		return '<code>'.parent::plainText().'</code>';
	}
	
	/** Get the value of this tag, as formatted text.
	 *
	 * @return str
	 */
	function formattedText()
    {
		return '<code>'.parent::formattedText().'</code>';
	}

	/** Get the value of the tag as raw data, without any text processing applied.
	 *
	 * @return str
	 */
	function rawText()
    {
		return '<code>'.parent::rawText().'</code>';
	}
	
	/** Return true if this Taglet is used in constructor documentation.
     *
     * @return bool
     */
	function inConstructor()
    {
		return TRUE;
	}

	/** Return true if this Taglet is used in field documentation.
     *
     * @return bool
     */
	function inField()
    {
		return TRUE;
	}

	/** Return true if this Taglet is used in method documentation.          
     *
     * @return bool
     */
	function inMethod()
    {
		return TRUE;
	}

	/** Return true if this Taglet is used in overview documentation.
     *
     * @return bool
     */
	function inOverview()
    {
		return TRUE;
	}

	/** Return true if this Taglet is used in package documentation.
     *
     * @return bool
     */
	function inPackage()
    {
		return TRUE;
	}

	/** Return true if this Taglet is used in class or interface documentation.
     *
     * @return bool
     */
	function inType()
    {
		return TRUE;
	}

	/** Return true if this Taglet is an inline tag.
     *
     * @return bool
     */
	function isInlineTag()
    {
		return TRUE;
	}

}

?>

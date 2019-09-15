<?php
/**
 * This file is part of the EmbedCategory Extension to MediaWiki
 * https://www.mediawiki.org/wiki/Extension:EmbedCategory
 *
 * @section LICENSE
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Extensions
 * @author Chris Page <chris@starforge.co.uk>
 * @copyright Copyright © 2019 Chris Page
 * @license GNU General Public Licence 2.0 or later
 */

class EmbedCategory {

	/* =========================================================================
	 *  General convenience functions
	 */

	/**
	 * Determine whether a parameter has been set, and if so return its value,
	 * otherwise return a default.
	 *
	 * @param array  $args    The array containing the parameters.
	 * @param string $name    The name of the parameter to fetch.
	 * @param mixed  $default The value to return if the parameter is not set.
	 * @return mixed The value set for the parameter, or the default.
	 */
	public static function getParameter( &$args, $name, $default = false) {

		if( isset( $args[$name] ) ) {
			return $args[$name];
		}

		return $default;
	}


	/**
	 * Given the body of an embedcategory tag, generate an array of
	 * page titles to ignore.
	 *
	 * @todo Potential enhancement would be to ignore lines that
	 *       are not valid titles. Not sure if it's worth the
	 *       time and overhead, though.
	 *
	 * @param string body The body of the embedcategory tag
	 * @return array An array of page titles to ignore.
	 */
	public static function buildExcludeList( $body ) {

		$lines = explode( "\n", $body );

		# TODO: check lines are valid titles?

		return $lines;
	}


	/**
	 * Create an array containing sort parameters to pass to the
	 * getMembersSortable() function.
	 *
	 * @param string|boolean $byupdated If true or a non-empty string, the
	 *        sort will be set to be done by timestamp. If the string contains
	 *        "ASC" then the sort will be ascending, otherwise it will default
	 *        to descending. If false, the sort will be by sortkey ascending.
	 * @return array An array containing the sort parameters.
	 */
	public static function getSortParams( $byupdated ) {

		$sort = array( 'order' => 'cl_sortkey',
					   'dir'   => 'ASC' );

		if( $byupdated ) {
			$sort['order'] = 'cl_timestamp';
			$sort['dir']   = 'DESC';

			if( $byupdated == 'ASC' ) {
				$sort['dir'] = 'ASC';
			}
		}

		return $sort;
	}


	/* =========================================================================
	 *  HTML output convenience functions
	 */

	/**
	 * A convenience function to generate an embedcategory error message
	 * <div> element.
	 *
	 * @param string $message The error message to show in the div.
	 * @return string A HTML element containing the error message.
	 */
	public static function errorDiv( $message ) {

		return Html::rawelement( 'div',
			array( 'class' => 'embedcategory error' ),
			$message );
	}


	/**
	 * A convenience function to generate a string containing a HTML link with
	 * bold link text.
	 *
	 * @param string $link The URL to set as the link href.
	 * @param string $text The text to show as the link.
	 * @return string A string containing the HTML <a> element.
	 */
	public static function strongLink( $link, $text ) {

		return Html::rawElement( 'a',
			array( 'href' => $link ),
			Html::element( 'strong',
				[],
				$text
			)
		);
	}


	/**
	 * A function to generate a string containing a HTML list item with
	 * link with the specified text.
	 *
	 * @param string $link The URL to set as the link href.
	 * @param string $text The text to show as the link.
	 * @return string A string containing the HTML list item and link
	 *                elements.
	 */
	public static function listItem( $link, $text ) {

		return Html::rawElement( 'li',
			[],
			Html::element( 'a',
				array( 'href' => $link ),
				$text
			)
		);
	}


 	/**
	 * A function to generate a string containing a HTML list item with
	 * link to the specified page as a bold "More" link.
	 *
	 * @param Title $title The title of the page to link to.
	 * @return string A string containing the HTML list item and link
	 *                elements.
	 */
   public static function moreLink( $title ) {

		return Html::rawElement( 'li',
			[],
			self::strongLink( $title->getLinkURL(),
				wfMessage( 'embedcategory-more' )
			)
 		);
	}


 	/**
	 * A function to generate HTML indicating that the category is empty.
	 *
	 * @param Title $title The title of the category.
	 * @return string A string containing the HTML indicating that the
	 *                category is empty.
	 */
	public static function emptyCategory( $title ) {

		return Html::rawElement( 'div',
			[],
			wfMessage( 'embedcategory-empty' ) .
				self::strongLink( $title->getLinkURL(),
					$title->getText()
				)
		);
	}


	/* =========================================================================
	 *  Implementation functions
	 */

	/**
	 * Generate a string of HTML containing links to pages in a category,
	 * optionally including a 'more' link at the end.
	 *
	 * @param string $name The name of the category, not including the
	 *					   Category: namespace
	 * @param integer $limit The number of pages to link to.
	 * @param boolean $showMore If there are more pages in the category than
	 *							$limit, and this is true, a link to the
	 *							category is added to the list of links.
	 * @param array $exclude An
	 * @return string The HTML containing the page links.
	 */
	public static function buildCategoryList( $name, $limit = false, $showMore = true, $byupdated = false, $exclude = array() ) {
		global $wgEmbedCategoryLinkEmpty;
		$result = "";

		// Fetch the category information
		$category = Category::newFromName( $name );
		if( !$category ) {
			return self::errorDiv( wfMessage( 'embedcategory-bad' ) );
		}

		// If there are no pages, and an empty cateogry link should be
		// generated, do so.
		if( $wgEmbedCategoryLinkEmpty && !$category->getPageCount() ) {
			self::emptyCategory( $category->getTitle() );
		}

		$sort = self::getSortParams( $byupdated );

		// Convert the list of category members to a string of HTML elements
		$members  = self::getMembersSortable( $category -> getName(), $limit, '', $sort );
		foreach ( $members as $member ) {

			// Ignore pages if they are in the ignore list.
			if( !in_array( $member->getText(), $exclude, true ) ) {
				$result .= self::listItem( $member->getLinkURL(), $member->getText() );
			}
		}

		// If there are more pages in the category than the limit, and the
		// 'show more' option is enabled, output a 'More' link at the end.
		if( $showMore && $limit && $category->getPageCount() > $limit ) {
			$result .= self::moreLink( $category->getTitle() );
		}

		return Html::rawelement( 'div',
			array( 'class' => 'categorylist' ),
			Html::rawElement( 'ul',
				[ 'class' => 'category_members' ],
				$result )
		);
	}


	/* =========================================================================
	 *  MediaWiki hook and interaction functions
	 */

	/**
	 * Parser hook handler for <embedcategory>
	 *
	 * @param string $input	 The content of the tag.
	 * @param array	 $args	 The attributes of the tag.
	 * @param Parser $parser Parser instance available to render wikitext into html,
	 *						 or parser methods.
	 * @param PPFrame $frame Can be used to see what template arguments ({{{1}}})
	 *						 this hook was used with.
	 * @return string HTML to insert in the page.
	 */
	public static function parserHook( $input, $args = array(), $parser, $frame ) {
		global $wgOut;

		// Only generate the list if the category is specified
		if( self::getParameter( $args, 'category' ) ) {
			// Obtain the body of the tag (with template expansions) and
			// convert it to an exclude list
			$body    = $parser->recursiveTagParse( $input, $frame );
			$exclude = self::buildExcludeList($body);

			if( self::getParameter( $args, 'columns' ) ) {

			} else {
				return self::buildCategoryList( $args['category'],
					self::getParameter( $args, 'limit' ),
					self::getParameter( $args, 'showmore' ),
					self::getParameter( $args, 'byupdated' ),
					$exclude
				);
			}
		} else {
			return self::errorDiv( wfMessage( 'embedcategory-none' ) );
		}
	}


	/**
	 * Register the <embedcategory> tag with the Parser.
	 *
	 * @param $parser Parser instance of Parser
	 * @return boolean Always returns true
	 */
	public static function onParserFirstCallInit( &$parser ) {
		// Adds the <embedcategory>...</embedcategory> tag to the parser.
		$parser->setHook( 'embedcategory', 'EmbedCategory::parserHook' );

		return true;
	}


	/**
	 * Modified version of Category::getMembers() that supports changing the
	 * sort order of pages returned.
	 *
	 * @param string $name The name of the category
	 * @param int|boolean $limit Optional limit to the number of results
	 * @param string $offset If set, fetch results starting at this page.
	 * @param array $sort An array containing the 'order' and 'dir' to sort
	 *                    results by.
	 * @return TitleArray The array of category members
	 *
	 * @note The _nice_ way to do this would be to subclass Category and
	 *       either override getMemebers() or add this. However, all of
	 *       Category's member variables are private, so we can't do that.
	 *       So, in normal PHP-pile-of-kludges-fashion, we have this mess.
	 */
	public static function getMembersSortable( $name, $limit = false, $offset = '',
		$sort = array ( 'order' => 'cl_sortkey', 'dir' => 'ASC' ) ) {

		$dbr = wfGetDB( DB_REPLICA );

		$conds = [ 'cl_to' => $name, 'cl_from = page_id' ];
		$options = [ 'ORDER BY' => $sort['order'] . ' ' . $sort['dir'] ];

		if ( $limit ) {
			$options['LIMIT'] = $limit;
		}

		if ( $offset !== '' ) {
			$conds[] = 'cl_sortkey > ' . $dbr->addQuotes( $offset );
		}

		$result = TitleArray::newFromResult(
			$dbr->select(
				[ 'page', 'categorylinks' ],
				[ 'page_id', 'page_namespace', 'page_title', 'page_len',
				  'page_is_redirect', 'page_latest' ],
				$conds,
				__METHOD__,
				$options
			)
		);

		return $result;
	}
}

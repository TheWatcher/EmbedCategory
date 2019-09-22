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
	static function getParameter( &$args, $name, $default = false) {

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
	static function buildExcludeList( $body ) {

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
	static function getSortParams( $byupdated ) {

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


	/**
	 * Given an object containing a category member query row, generate the
	 * text, url, and fulltext to be shown in links.
	 *
	 * @param object A category query row.
	 * @return An array containing the text, url, and fulltext to show for
	 *         this row.
	 */
	static function getResultData( $data ) {

		$title = Title::newFromID( $data->page_id );
		$fulltext = $title -> getText();

		// If a sort key has been specified, use it as the text
		// From the mediawiki tech docs, "This is either the empty string if
		// a page is using the default sortkey (aka the sortkey is unspecified).
		// Otherwise it is the human readable version of cl_sortkey)"
		if( $data->cl_sortkey_prefix ) {
			$fulltext = $data->cl_sortkey_prefix;
		}

		return array(
			'text'     => $title -> getText(),
			'url'      => $title -> getLinkURL(),
			'fulltext' => $fulltext,
			'char'     => substr($fulltext, 0, 1)
		);
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
	static function errorDiv( $message ) {

		return Html::rawelement( 'div',
			array( 'class' => 'embedcategory error' ),
			$message );
	}


	/**
	 * A convenience function to generate a string containing a HTML link
	 *
	 * @param string $link The URL to set as the link href.
	 * @param string $text The text to show as the link.
	 * @return string A string containing the HTML <a> element.
	 */
	static function link( $link, $text ) {

		return Html::element( 'a',
			array( 'href' => $link ),
			$text
		);
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
   static function moreLink( $title ) {

		return Html::rawElement( 'div',
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
	static function emptyCategory( $title ) {

		return Html::rawElement( 'div',
			[],
			wfMessage( 'embedcategory-empty' ) .
				self::strongLink( $title->getLinkURL(),
					$title->getText()
				)
		);
	}


	/**
	 * Generate the list of pages in the category as a column-compatible
	 * format similar to CategoryViewer::columnList().
	 *
	 * @note This will royally mess up ordering if the rows are not
	 *       in ascending alphanumeric order. You have been warned.
	 *
	 * @param array $rows An array of IResultWrapper objects to show.
	 * @param array $args An array of control arguments.
	 * @param array $exlcude A list of page names to exclude from the displayed
	 *                        items.
	 * @return string The HTML containing the page links.
	 */
	static function buildCategoryColumns( $rows, $args, $exclude ) {

		# Convert the rows to alphanumerically-ordered sublists
		$charlist = [];

		foreach ( $rows as $row ) {
 			$values = self::getResultData( $row );

			// Ignore pages if they are in the ignore list.
			if( !in_array( $values['text'], $exclude, true ) ) {
				if( !isset( $charlist[$values['char']] ) ) {
					$charlist[$values['char']] = [];
				}

				$charlist[$values['char']][] = $values;
			}
		}

		$result = "";
		foreach ( $charlist as $char => $rows ){
			$h3char = $char === ' ' ? "\u{00A0}" : htmlspecialchars( $char );

			$result .= '<div class="mw-category-group">';

			if( $args['headers'] ) {
				$result .= '<h3>' . $h3char . "</h3>\n";
			}

			$result .= '<ul>';

			foreach ( $rows as $values ) {
				$result .= self::listItem( $values['url'], $values['fulltext'] );
			}

			$result .= '</ul></div>';
		}

		return $result;
	}


	/**
	 * Generate the list of pages in the category as a sorted list. Note that
	 * this preserves the order of the rows specified, so it can be used to
	 * display results when `byupdated` is enabled. The downside to this is
	 * that column display is not supported.
	 *
	 * @param array $rows An array of IResultWrapper objects to show.
	 * @param array $args An array of control arguments.
	 * @param array $exlcude A list of page names to exclude from the displayed
	 *                        items.
	 * @return string The HTML containing the page links.
	 */
	public static function buildCategorySorted( $rows, $args, $exclude ) {

		$result = "";

		foreach ( $rows as $row ) {
 			$values = self::getResultData( $row );

			// Ignore pages if they are in the ignore list.
			if( !in_array( $values['text'], $exclude, true ) ) {
				$result .= self::listItem( $values['url'], $values['fulltext'] );
			}
		}

		return Html::rawelement( 'ul', [], $result );
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
	 * @param array  $args An array containing parameters to control limit,
	 *                     sort direction, and more link visibility.
	 * @param array  $exclude A list of page names to exclude from the displayed
	 *                        items.
	 * @return string The HTML containing the page links.
	 */
	static function buildCategoryList( $name, $args, $exclude = array() ) {
		global $wgEmbedCategoryLinkEmpty;

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

		$sort = self::getSortParams( $args['byupdated'] );

		// Convert the list of category members to a string of HTML elements
		$rows  = self::getMembersSortable( $category -> getName(),
										   $args['limit'],
										   '',
										   $sort );

		if( $args['format']  == 'columns' ) {
			$result = self::buildCategoryColumns( $rows, $args, $exclude );
		} else {
			$result = self::buildCategorySorted( $rows, $args, $exclude );
		}

		// If there are more pages in the category than the limit, and the
		// 'show more' option is enabled, output a 'More' link at the end.
		if( $args['showmore'] && $args['limit'] && $category->getPageCount() > $args['limit'] ) {
			$result .= self::moreLink( $category->getTitle() );
		}

		return Html::rawelement( 'div',
			array( 'class' => 'mw-category' ),
			$result
		);
	}


	/**
	 * Generate a string of HTML containing links to pages in a category,
	 * suitable for insertion into a Navbox list.
	 *
	 * @param string $name The name of the category, not including the
	 *					   Category: namespace
	 * @param array  $args An array containing parameters to control limit
	 *                     and sort direction.
	 * @param array  $exclude A list of page names to exclude from the displayed
	 *                        items.
	 * @return string The HTML containing the page links.
	 */
	static function buildCategoryNavlist( $name, $args, $exclude = array() ) {

		$category = Category::newFromName( $name );
		if( !$category ) {
			return self::errorDiv( wfMessage( 'embedcategory-bad' ) );
		}

		$sort = self::getSortParams( $args['byupdated'] );

		// Convert the list of category members to a string of HTML elements
		$rows  = self::getMembersSortable( $category -> getName(),
										   $args['limit'],
										   '',
										   $sort );

		$result = array();
		foreach ( $rows as $row ) {
			$values = self::getResultData( $row );

			// Ignore pages if they are in the ignore list.
			if( !in_array( $values['text'], $exclude, true ) ) {
				$result[] = self::link($values['url'], $values['fulltext'] );
			}
		}

		// Navlist entries are middot-separated
		return implode( '&nbsp;<span style="font-weight:bold;">&middot;</span> ',
						$result );
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

			$params  = array(
				'limit'     => self::getParameter( $args, 'limit' ),
				'showmore'  => self::getParameter( $args, 'showmore' ),
				'byupdated' => self::getParameter( $args, 'byupdated' ),
				'format'    => self::getParameter( $args, 'format' , 'list' ),
				'headers'   => self::getParameter( $args, 'headers', true )
			);

			if( $params['format'] == 'navlist' ) {
				return self::buildCategoryNavlist( $args['category'],
					$params,
					$exclude
				);

			} else {
				return self::buildCategoryList( $args['category'],
					$params,
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
	 * sort order of pages returned, and returning the raw query results
	 * rather than a TitleArray, so the cl_sortkey_prefix is accessible.
	 *
	 * @note Ideally, this would be done with Category::getMembers(), but that
	 *       doesn't expose the cl_sortkey_prefix field, and doesn't support
	 *       alternative sorting methods.
	 *
	 * @param string $name The name of the category
	 * @param int|boolean $limit Optional limit to the number of results
	 * @param string $offset If set, fetch results starting at this page.
	 * @param array $sort An array containing the 'order' and 'dir' to sort
	 *                    results by.
	 * @return IResultWrapper The category members
	 */
	static function getMembersSortable( $name, $limit = false, $offset = '',
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

		$result = $dbr->select(
			[ 'page', 'categorylinks' ],
			[ 'page_id', 'page_namespace', 'page_title', 'page_len',
			  'page_is_redirect', 'page_latest', 'cl_sortkey_prefix' ],
			$conds,
			__METHOD__,
			$options
		);

		return $result;
	}
}

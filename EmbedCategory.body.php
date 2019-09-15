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
 * @copyright Copyright © 219 Chris Page
 * @license GNU General Public Licence 2.0 or later
 */

class EmbedCategory {

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
	public static function buildCategoryList( $name, $limit = false, $showMore = true, $exclude = array() ) {
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
			$catTitle = $category->getTitle();

			return Html::rawElement( 'div',
				[],
				wfMessage( 'embedcategory-empty' ) .
					self::strongLink( $catTitle->getLinkURL(),
						$catTitle->getText()
					)
			);
		}

		// Convert the list of category members to a string of HTML elements
		$members  = $category->getMembers( $limit );
		while( $members->valid() ) {
			$member = $members->current();

			// Ignore pages if they are in the ignore list.
			if( !in_array( $member->getText(), $exclude, true ) ) {
				$result .= Html::rawElement( 'li',
					[],
					Html::element( 'a',
						array( 'href' => $member->getLinkURL() ),
						$member->getText()
					)
				);
			}

			$members->next();
		}

		// If there are more pages in the category than the limit, and the
		// 'show more' option is enabled, output a 'More' link at the end.
		if( $showMore && $limit && $category->getPageCount() > $limit ) {
			$catTitle = $category->getTitle();

			$result .= Html::rawElement( 'li',
				[],
				self::strongLink( $catTitle->getLinkURL(),
					wfMessage( 'embedcategory-more' )
				)
 			);
		}

		return Html::rawelement( 'div',
			array( 'class' => 'categorylist' ),
			Html::rawElement( 'ul',
				[ 'class' => 'category_members' ],
				$result )
		);
	}


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
		if($args['category']) {
			// Obtain the body of the tag (with template expansions) and
			// convert it to an exclude list
			$body    = $parser->recursiveTagParse( $input, $frame );
			$exclude = self::buildExcludeList($body);

			if($args['columns']) {

			} else {
				return self::buildCategoryList( $args['category'],
					$args['limit'],
					$args['showmore'],
					$exclude
				);
			}
		} else {
			return self::errorDiv( wfMessage( 'embedcategory-none' ) );
		}
	}
}

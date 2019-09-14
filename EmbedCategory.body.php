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
		$parser -> setHook( 'embedcategory', 'EmbedCategory::parserHook' );

		return true;
	}

	/**
	 * Generate a string of HTML containing links to pages in a category, 
	 * optionally including a 'more' link at the end.
	 *
	 * @param string $name The name of the category, not including the 
	 *                     Category: namespace
	 * @param integer $limit The number of pages to link to.
	 * @param boolean $showMore If there are more pages in the category than 
	 *                          $limit, and this is true, a link to the 
	 *                          category is added to the list of links.
	 * @return string The HTML containing the page links.
	 */
	public static function buildCategoryList( $name, $limit = false, $showMore = true ) {
		global $wgEmbedCategoryLinkEmpty;
		$result = "";

		// Fetch the category information
		$category = Category::newFromName( $name );
		if( !$category ) {
			return Html::rawelement( 'div',
									 array( 'class' => 'categorylist error' ),
									 wfMessage( 'embedcategory-bad' ) );
		}

		// If there are no pages, and an empty cateogry link should be
		// generated, do so.
		if( $wgEmbedCategoryLinkEmpty && !$category -> getPageCount() ) {
			$catTitle = $category -> getTitle();
			
			return Html::rawElement( 'div', [],
										 wfMessage( 'embedcategory-empty' ) .
										 Html::rawElement( 'a',
														   array( 'href' => $catTitle -> getLinkURL()),
														   Html::element( 'strong',
																		  [],
																		  $catTitle -> getText())
										 )										 
			);
		}
		
		// Convert the list of category members to a string of HTML elements
		$members  = $category -> getMembers( $limit );
		while( $members -> valid() ) {
			$member = $members -> current();

			// TODO: Is <li> the best option here?
			$result .= Html::rawElement( 'li', [],
										 Html::element( 'a',
														array( 'href' => $member -> getLinkURL() ),
														$member -> getText() )
			);

			$members -> next();
		}

		// If there are more pages in the categroy than the limit, and the
		// 'show more' option is enabled, output a 'More' link at the end.
		if( $showMore && $limit && $category -> getPageCount() > $limit ) {
			$catTitle = $category -> getTitle();
			
			$result .= Html::rawElement( 'li', [],
										 Html::rawElement( 'a',
														   array( 'href' => $catTitle -> getLinkURL()),
														   Html::element( 'strong',
																		  [],
																		  wfMessage( 'embedcategory-more' ))
										 )										 
			);
		}
		
		return Html::rawElement( 'ul', [ 'class' => 'category_members' ], $result );
	}

  	/**
	 * Parser hook handler for <embedcategory>
	 *
	 * @param string $input  The content of the tag.
	 * @param array  $args   The attributes of the tag.
	 * @param Parser $parser Parser instance available to render wikitext into html,
	 *                       or parser methods.
	 * @param PPFrame $frame Can be used to see what template arguments ({{{1}}})
	 *                       this hook was used with.
	 * @return string HTML to insert in the page.
	 */
	public static function parserHook( $input, $args = array(), $parser, $frame ) {
		global $wgOut;

		if($args['category']) {
			return Html::rawelement( 'div',
									 array( 'class' => 'categorylist' ),
									 self::buildCategoryList( $args['category'], $args['limit'], $args['showmore'] ) );			
		} else {
			return Html::rawelement( 'div',
									 array( 'class' => 'categorylist error' ),
									 wfMessage( 'embedcategory-none' ) );
		}
	}
}

The EmbedCategory extension adds a new <embedcategory> parser tag that allows
the list of pages in a given category to be included in any page.

The generated page list includes pages in all subcategories of the specified
category.

## Installation

This extension is not currently available via the standard MediaWiki extension
distributor. To install the extension, you should clone the Git repository
into your extensions directory as shown here:

```
cd extensions/
git clone https://github.com/TheWatcher/EmbedCategory.git
```

Once you have cloned the repository, add the following code at the bottom of
your LocalSettings.php:

`wfLoadExtension(EmbedCategory);`

## Configuration

The following configuration variables can be included in your LocalSettings.php
after loading the extension:

`$wgEmbedCategoryLinkEmpty = true | false`
If set to true (the default), if the category specified in an <embedcategory>
tag has no pages in it, a message to that effect will be included in the page.
If this is set to false, an empty list is included in the page if the category
is empty.

## Usage

Insert a &lt;embedcategory&gt; tag at the location you want to embed the list of
pages in a category. The following parameters are supported:

<dl>
<dt><code>category="&lt;categoryname&gt;"</code></dt>
<dd>This parameter is required. Use this to specify the category you want to
embed the page list for. Only include the category name; the Category: namespace
should not be included.</dd>
</dl>

<dl>
<dt><code>format="&lt;list|columns|navlist%gt;"</code></dt>
<dd>This parameter is optional, and defaults to <code>list</code>. If specified,
you can either set <code>list</code>, in which case pages in the category are
listed in a single vertical list; <code>columns</code> in which case the pages
are listed in a way that will automatically form them into columns when enough
pages are in the category; or <code>navlist</code> where the pages are listed
as links separated by &middot; dots suitable for insertion into Navbox lists.</dd>
</dl>

<dl>
<dt><code>headers=&lt;0|1&gt;</code></dt>
<dd>This parameter is optional and defaults to 1. If set to 1, and <code>format</code>
is set to <code>columns</code>, this will insert headers for each set of first
page characters. If set to 0, no headers will be output. Note that this is ignored
for <code>list</code> and <code>navlist</code> formats.</dd>
</dl>

<dl>
<dt><code>limit=&lt;integer&gt;</code></dt>
<dd>This parameter is optional. If specified, this will limit the length of the
page list to the value given here. If you do not specify a limit, all the
pages in the category and its subcategories will be listed.</dd>
</dl>

<dl>
<dt><code>showmore=&lt;0|1&gt;</code></dt>
<dd>This parameter is optional, and defaults to 1. If you set the <code>limit</code> parameter,
and the category contains more pages than that, and <code>showmore</code> is set to 1, a
<code>More</code> link will be appended to the list of pages that will take the reader to
the category page. If set to 0, no link will be added even if the full list of
pages in the category is not shown.</dd>
</dl>

<dl>
<dt><code>byupdated="0|1|ASC"</code></dt>
<dd>This parameter is optional, and defaults to 0. If set to 1, the pages in the
category are listed according to how recently updated their entry in the category
was. Typically this will be when they were added to the category, not when the page
itself was updated! By default they are sorted most recently added/updated first,
to least recently added/updated last. If <code>byupdated</code> is set to <code>ASC</code>
then the order is reversed: the oldest page is shown first, and the newest last.
<strong>NOTE:</strong> Enabling byupdated will override <code>format</code> to
<code>list</code> if <code>format</code> has been set to <code>columns</code></dd>
</dl>

The body of the &lt;embedcategory&gt; tag may contain a list of pages to exclude from
the list, which one line per page title to exclude. For example:


```
<embedcategory category="cats">
Short Hair
Cheshire
</embedcategory>
```

will show a list of all pages in the `cats` category, except the pages for `Short Hair`
or `Cheshire`, if they exist in the category.

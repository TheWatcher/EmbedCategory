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

Insert a <embedcategory /> tag at the location you want to embed the list of
pages in a category. The following parameters  are supported:

<dl>
<dt>`category="categoryname"`</dt>
<dd>This parameter is required. Use this to specify the category you want to
embed the page list for. Only include the category name; the Category: namespace
should not be included.</dd>
</dl>

<dl>
<dt>`limit=<integer>`</dt>
<dd>This parameter is optional. If specified, this will limit the length of the
page list to the value given here. If you do not specify a limit, all the
pages in the category and its subcategories will be listed.</dd>
</dl>

<dl>
<dt>`showmore=0|1`</dt>
<dd>This parameter is optional, and defaults to 1. If you set the `limit` parameter,
and the category contains more pages than that, and `showmore` is set to 1, a
'More' link will be appended to the list of pages that will take the reader to
the category page. If set to 0, no link will be added even if the full list of
pages in the category is not shown.</dd>
</dl>

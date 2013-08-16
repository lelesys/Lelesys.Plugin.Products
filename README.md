Lelesys Products Plugin
======================

This is a products catalog plugin which displays products by categories.

Warning: This plugin is experimental.

Quick start
-----------

* include the plugin's TypoScript definitions to your own one's (located in, for example,Packages/Sites/Your.Site/Resources/Private/TypoScripts/Library/ContentElements.ts2) with:

```
include: resource://Lelesys.Plugin.Products/Private/TypoScripts/Library/NodeTypes.ts2
```

* include the plugin's Stylesheet to your own one's where you add other stylesheets of the site.

```
<link href="{f:uri.resource(path: 'resource://Lelesys.Plugin.Products/Public/Styles/Products.css')}" rel="stylesheet" media="screen">
```

* add the plugin content element "Catalog" to the position of your choice,
 the catalog plugin will enable to add new categories and products.
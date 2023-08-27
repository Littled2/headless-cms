# Headless CMS

## About

A very simple PHP based headless CMS for simple static websites.

Webpages on your website are represented in the /webpages directory, following the file structure used there.

For example: A website with three webpages, at paths: **/**, **/contact-us**, and **/projects/my-first-project** would look like:

```
│
├── page.html
│
├── contact-us/
│   │
│   └── page.html
│
└── projects/
    │
    └── my-first-project
        │
        └── page.html

```

## How to structure a page.html file
In the most simple example, the page.html file's content is inserted as HTML into the page body of the root 'index.php' document.

If you wish to add settings such as a title/description that is individual to the webpage, then these should be added at the top of the file as shown below:

```html
Title: My Webpage
Description: This is the description of the webpage
=================

<h1>This is a heading</h1>
<p>This is a paragraph</p>

```

Read more about page settings later on.


## Stylesheets and Other Resources
To use stylesheets, images or any other resources from the same domain, store them in the **/resources** directory. Make sure when they are referenced in the webpage to include the **/resources** directory name.

For Example: importing a stylesheet called 'styles.css' would look like:

```html
<link rel="stylesheet" href="/resources/styles.css">
```

### Importing Scripts
For JavaScript scripts, there is also the option of storing the, in the **/scripts** directory.

## Custom Error Pages
If you wish for your website to have custom error pages, then create a file with the name: **{HTTP Error Code}.html** in the **/errors** directory.

For example, a custom 404 page would be created at: **/errors/404.html**

These pages are inserted into the **index.php** file in the same way as any other page.

## Page Settings
Page settings are the key-value pairs that you put at the top of your .html file.
To use page settings, put them at the top of the page, and separate them from the rest of the html file using a line of
only equals '=' characters.

```html
<!-- These are the page settings -->
Title: My Webpage
Description: This is the description of the webpage
Hide_Heading
=================

<h1>This is a heading</h1>
<p>This is a paragraph</p>

```

**Note:** Page Settings supports *inline* HTML comments. Not multiline!

### Supported page settings:
| Setting Name | Value | Description |          
| ------------ | ----- | ----------- |
| Title        | 'Page Title' | This will set the page's ```<title/>``` tag |
| Description  | 'Page Description' | This will set the page's ```<meta  name="description" />``` tag |
| Favicon      | /resources/... Path to image | This will set the shortcut 'favicon' for this page. The default favicon value is: /resources/favicon.png |
| og-image | /resources/... Path to image | Use this to set the Open Graph Protocol image, so a link to this page renders well when shared on social media. Learn more about the Open Graph Protocol [here](https://ogp.me/). |
| Hide_Heading   | - |Just adding this key will hide the heading from showing on this page |
| Hide_Footer    | - |Just adding this key will hide the footer from showing on this page |

*Page setting names (keys) are NOT case-sensitive. Eg. to set the title both 'Title' and 'title' will work fine*

## Other Points to Note
- By default, AlpineJS is imported to every webpage, if you don't with to use AlpineJS in your website, simply remove the import tag from the **index.php** file.

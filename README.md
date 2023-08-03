# Headless CMS

## About

A very simple PHP based headless CMS for simple static websites.

Webpages on your website are represented in the /webpages directory, following the file structure used there.

For example: A website with three webpages, at paths: **/**, **/contact-us**, and **/projects/my-first-project** would look like:

```
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

## Stylesheets and Other Resources
To use stylesheets, images or any other resources from the same domain, store them in the **/resources** directory. Make sure when they are referenced in the webpage to include the **/resources** directory name.

For Example: importing a stylesheet called 'styles.css' would look like:

```html
<link rel="stylesheet" href="/resources/styles.css">
```

## Custom Error Pages
If you wish for your website to have custom error pages, then create a file with the name: **{HTTP Error Code}.html** in the **/errors** directory.

For example, a custom 404 page would be created at: **/errors/404.html**

These pages are inserted into the **index.php** file in the same way as any other page.

## Other Points to Note
- By default, AlpineJS is imported to every webpage, if you don't with to use AlpineJS in your website, simply remove the import tag from the **index.php** file.
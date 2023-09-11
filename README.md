# Headless CMS

## About

A very simple PHP based headless CMS for simple static websites. Complete with a client side router.

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


## Stylesheets
Headless CMS supports two ways of using stylesheets, depending on whether you want the CSS styles to be available globally across all page, or just on a specific page.

### 1. Global Styles
Store these stylesheets the same as any other resource in the **/resources** directory. Link to these stylesheets from the <head> tag of the index.php file, so they are loaded with every page.

### 2. Page-Specific Styles
If styles are only intended to be used on one webpage, reduce unused CSS by adding a **styles.css** file to that page's directory.

For example: If the page at **/test** required some page-specific styles you would create the file **/webpages/test/styles.css**. The styles in this file will *automatically* be inserted with the page content from the **page.html** file.


## General Resources
To store any resources such as stylesheets or images, store them in the **/resources** directory.

Make sure when you reference them in a webpage, you include the **/resources/...** directory name.

For Example: Referencing an image stored at **/resources/images/photo.png** would look like:

```html
<img src="/resources/images/photo.png">
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
=================

<h1>This is a heading</h1>
<p>This is a paragraph</p>

```

**Note:** Page Settings supports *inline* HTML comments. Not multiline!

### Supported Page Settings
Note: *Page setting names (keys) are NOT case-sensitive. Eg. to set the title both 'Title' and 'title' will work fine*

#### Basic Setting
| Setting Name | Value | Description |          
| ------------ | ----- | ----------- |
| title        | 'Page Title' | This will set the page's ```<title/>``` tag. *Note this also sets the og-title property*. |
| description  | 'Page Description' | This will set the page's ```<meta  name="description" />``` and ```<meta  property="og:description" />``` tags. |
| favicon      | /resources/... Path to image | This will set the shortcut 'favicon' for this page. The default favicon value is: /resources/favicon.png |
| og-image | Full URL image | Sets the content of the ```<meta  name="og:image" />``` tag. |
| og-url | Full URL of this resource | Sets the content of the ```<meta  name="og:url" />``` tag. |
| og-type | Type of this resource | Sets the content of the ```<meta  name="og:type" />``` tag. See [reference](https://ogp.me/#types) for a list of valid Open Graph types. |


### Open Graph Protocol
A lot of the page settings start with 'og-....' This is because these are used to set the content of the ```<meta>``` tags used by the Open Graph Protocol.

The OGP is used to make your links look good well when shared on social media. [Learn more about the OGP.](https://ogp.me/)



## Client Side Router (CSR)
All websites built with this headless CMS use the built-in client side router by default.

This means that when the user navigates, rather than reloading the entire page the CSR will instead fetch this page and replace the existing page, without performing a full-page navigation.

The CSR also caches the 5 most recently visited pages, this makes navigating back appear almost instant.

### Using JavaScript with the CSR
The CSR effectivley makes your webpage into a single page application. **This means that javascript that is loaded in for specific pages may not work as expected!**

Generally, to avoid complications add ```<script>``` tags to the ```<head>``` of the *index.php* file.

Keep in mind that these scripts will **only be loaded in once**, when the user first arrives at the website. If you want to use JavaScript when a page loads, then make sure it is using a 'load' event listener on the ```window``` object!

For JavaScript that you want to be used on a **specific page(s) only**, headless-cms provides a solution. The two functions ```onPageLoad``` and ```onPagesLoad``` can be used to attach callbacks that will be run when a specific page is loaded.

**Example:**

```js
import { onPageLoad, onPagesLoad } from "/headless-cms-scripts/client-side-router.js";


onPageLoad('/', () => {
    console.log('Homepage is loaded!')
})


onPagesLoad([ '/about', '/contact' ], () => {
    console.log('/about OR /contact is loaded')
})
```


#### Triggering the CSR
By default the CSR will override the default navigation behavior of any link that points to a page that is from the same origin (Same domain, sub-domains and protocol).

**Example:**

```js
// Trigger the Client Side Router to navigate to a URL
// using the navigate_to_page() function.

window.router.navigate_to_page(url)

```

*It should be noted that the CSR is mounted **after** The page has fully loaded.*


### Disabling the CSR
If the above does not sound right for your website no problem! Simply comment out/remove the ```<script>``` tag that imports the CSR from the *index.php* file.

It looks like this:
```html
<!-- Import the Client Side Router -->
<!-- Remove this if you don't wish to use the client-side routing function of the Headless CMS -->
<script type="module" defer src="/headless-cms-scripts/client-side-router.js"></script>
```



## Other Points to Note
- By default, AlpineJS is imported to every webpage, if you don't with to use AlpineJS in your website, simply remove the import tag from the **index.php** file.

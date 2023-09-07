// After the initial page load, this client side router (CSR) takes over
// Used to prevent pages from needing to be entirely reloaded
// On relocate request, prevent the default relocate and check the cache

// The CSR can support the following page settings:
// - title
// - description
// - favicon
// NOT og-image

// (These settings use the same names as specified in the README file of the headless-cms repository)

class Client_Side_Router {
    constructor() {

        const content = document.querySelector("main").innerHTML

        const description = document.querySelector('meta[name="description"]')?.getAttribute('content')

        const favicon = document.querySelector('link[rel=icon]')?.getAttribute('href')


        // Initialise the page cache with the content from the current page
        this.cache = {
            [window.location.href]: new Cache_Item({
                settings: {
                    title: document?.title,
                    description: description ? description : '',
                    favicon: favicon ? favicon : ''
                },
                content: content
            })
        }

        // The maximum number of pages allowed to be stored in the cache
        this.max_cache_size = 5

        this.init_links()

        history.pushState({ url: window.location.href }, document.title, window.location.href)

        window.addEventListener("popstate", event => this.navigate_to_page(event.state.url, true))
    }

    /**
     * Overrides the default navigation behavior of links that point to urls within the same origin
     */
    init_links() {
        // Find the nav links
        // If link is an internal link and not supposed to open in a new tab
        const links = [...document.querySelectorAll("a")].filter(a => a.href.startsWith(window.location.origin) && a.target !== "_blank")
        for (const a of links) {
            a.onclick = (e) => {
                e.preventDefault()
                this.navigate_to_page(a.href)
            }
        }
    }

    /**
     * Navigates the user to a new webpage.
     * Attempts to navigate via the client-side router but defaults to normal
     * full-page navigation after a certain time
     */
    navigate_to_page(url, navigateBack=false) {

        // Stores the state of the navigation attempt
        let navigationComplete = false

        // Check in 5 Seconds if navigation has completed
        const check = setTimeout(() => {
            if(!navigationComplete) {
                this.full_page_navigate(url)
            }
        }, 5_000)

        // Attempt to load the page via the CSR
        this.load_page(url, navigateBack)
        .then(() => {
            navigationComplete = true
            // Cancel the check
            clearTimeout(check)
            console.log("CSR Navigated to", url)
        })
        .catch(err => {
            console.error("CSR could not navigate:", err)
            this.full_page_navigate(url)
        })



    }

    /**
     * Navigates the user to a new page via the CSR
     * @param {String} url The URL of the page to navigate too
     * @param {Boolean} navigateBack Is the user navigating back
     */
    async load_page(url, navigateBack=false) {

        // Attempt to read data from cache
        let pageData = this.retrieve_from_cache(url)

        if (!pageData) {
            pageData = await this.fetch_page_content(url)   
            // Add new page to cache
            this.add_to_cache(url, pageData)
        }

        // Write html to page
        document.querySelector("main").innerHTML = pageData.content

        this.apply_page_settings(pageData.settings)

        // Do not add page to history if navigating back
        if(!navigateBack) history.pushState({ url: url }, pageData?.settings?.title, url)

        this.init_links()

        // Run anything that is listening to the 'onload' event
        dispatchEvent(new Event('load'))
    }

    /**
     * Used as a fallback if all else fails so the user can still navigate
     * @param {String} url The URL of the intended page
     */
    full_page_navigate(url) {
        console.log("Navigation failed, ")
        window.location.href = url
    }

    /**
     * Applies the page settings for the page just loaded
     * @param {Object} settings The settings object for the page just loaded
     */
    apply_page_settings(settings) {
        
        // Set the document's title
        document.title = settings?.title ? settings.title : ''

        // Set the document's description on both the description meta tag and the og: description tag
        document.querySelectorAll('meta[name=description], meta[name="og:description]"')
        .forEach(el => el.setAttribute('content', settings?.description ? settings.description : ''))

        // Set the document's favicon
        document.querySelectorAll('link[rel=icon]')
        .forEach(el => el.setAttribute('href', settings?.favicon ? settings.favicon : ''))

    }

    /**
     * Fetches a page and its information from the web server
     * @param {String} url The URL of the requested page
     * @returns {Promise} An object representing the new page
     */
    async fetch_page_content(url) {
        try {
            const res = await fetch(url + "?" + new URLSearchParams({ csr: true }))
            if(!res.ok) throw res.status
            const pageData = await res.json()
            return pageData
        } catch (error) {
            console.error('CSR page fetch failed:', error)
            throw new Error('CSR page fetch failed')
        }
    }

    /**
     * Adds a page to the cache in the correct format
     * @param {String} url The URL of the page being added to the cache
     * @param {Object} page_data The parsed object just returned from the web server
     */
    add_to_cache(url, page_data) {
        // Ensure cache does not get too large by removing the least recently used element
        if(Object.keys(this.cache).length === this.max_cache_size) this.remove_oldest_from_cache()

        this.cache[url] = new Cache_Item({
            settings: page_data.settings,
            content: page_data.content
        })
    }


    /**
     * Deletes the least recently accessed page from the cache
     */
    remove_oldest_from_cache() {

        let oldestTime = new Date().getTime()
        let oldestIndex

        // Iterate over all pages in the cache to find the oldest item
        for (const k in this.cache) {
            if(this.cache[k] < oldestTime) {
                oldestTime = this.cache[k].lastAccessed
                oldestIndex = k
            }
        }

        delete this.cache[oldestIndex]
    }

    /**
     * 
     * @param {String} path The URL of the item hoped to be found in the cache
     * @returns {Cache_Item} The 
     */
    retrieve_from_cache(path) {
        const item = this.cache[path]
        if(item) this.cache[path].lastAccessed = new Date().getTime()
        return this.cache[path]
    }

}


class Cache_Item {
    constructor({ settings, content }) {
        this.content = content
        this.settings = settings
        this.lastAccessed = new Date().getTime()
    }

}


// Initialise the client router
window.router = new Client_Side_Router
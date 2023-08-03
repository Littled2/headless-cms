// After the initial page load, this client side router takes over
// Used to prevent pages from needing to be entirely reloaded
// On relocate request, prevent the default relocate and check the cache

class Client_Side_Router {
    constructor() {

        const content = document.querySelector("main").innerHTML

        const description = document.querySelector('meta[name="description"]').getAttribute("content")

        // Initialise the page cache with the content from the current page
        this.cache = {
            [window.location.href]: new Cache_Item({
                settings: {
                    title: document.title,
                    description: description ? description : ''
                },
                content: content
            })
        }

        // The maximum number of pages allowed to be stored in the cache
        this.max_cache_size = 5

        this.init_links()

        history.pushState({ url: window.location.href }, document.title, window.location.href)

        window.addEventListener("popstate", event => this.load_page(event.state.url, true))
    }

    init_links() {
        // Find the nav links
        // If link is an internal link and not supposed to open in a new tab
        const links = [...document.querySelectorAll("a")].filter(a => a.href.startsWith(window.location.origin) && a.target !== "_blank")
        for (const a of links) {
            a.onclick = (e) => {
                e.preventDefault()
                this.load_page(a.href)
            }
        }
    }

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

        const title = pageData?.settings?.title !== undefined ? pageData.settings.title : ''
        const description = pageData?.settings?.description !== undefined ? pageData.settings.description : ''

        // Set title
        document.title = title
        // Set Description
        document.querySelector('meta[name="description"]').setAttribute("content", description)        

        // Do not add page to history if navigating back
        if(!navigateBack) history.pushState({ url: url }, title, url)

        this.init_links()
    }

    async fetch_page_content(url) {
        try {
            const res = await fetch(url + "?" + new URLSearchParams({ csr: true }))
            const pageData = await res.json()
            return pageData
        } catch (error) {
            console.error("Error fetching page", error)
            return "Error fetching page"
        }
    }

    add_to_cache(path, page_data) {
        // Ensure cache does not get too large by removing the least recently used element
        if(Object.keys(this.cache).length === this.max_cache_size) this.remove_oldest_from_cache()

        this.cache[path] = new Cache_Item({
            settings: {
                title: page_data.settings.title,
                description: page_data.settings.description  
            },
            content: page_data.content
        })
    }

    remove_oldest_from_cache() {
        // Remove the least recently used page from the cache
        let oldestTime = new Date().getTime()
        let oldestIndex
        for (const k in this.cache) {
            if(this.cache[k] < oldestTime) {
                oldestTime = this.cache[k].lastAccessed
                oldestIndex = k
            }
        }

        delete this.cache[oldestIndex]
    }

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
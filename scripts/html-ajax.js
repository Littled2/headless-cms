window.addEventListener("load", ajax_init)


function ajax_init() {

    window.ajax = {}
    window.ajax.listeners = {}

    document.querySelectorAll("*[ajax-get]").forEach(el => {

        // Get the URL of the requested resource
        let url = el.getAttribute("ajax-get")
    
        // Get and parse any options provided by the ajax-options attribute
        let options = el.getAttribute("ajax-options")
        if(options) {
            options = parse_options(options)
        }

        // Get the parser function if there is a parser function
        let parser = get_valid_function(el.getAttribute("ajax-parser"))

        // If this request is to be repeated, then a trigger will be used
        let listener = el.getAttribute("ajax-listener")
        if(listener) {
            // Add this element to its respective listener

            // Check if listener already exists
            if(!Object(window.ajax.listeners).hasOwnProperty(listener)) {
                window.ajax.listeners[listener] =  []
            }

            window.ajax.listeners[listener].push([ url, el, parser, options ])
        }    
        
        // Exit early if the ajax query is not supposed to be run initially
        if(el.hasAttribute("ajax-defer")) return

        // Make the AJAX request
        ajax_get(url, el, parser, options)
    })

    // Get elements that trigger ajax events
    document.querySelectorAll("*[ajax-update]").forEach(el => {

        // Get the name of this trigger
        let trigger_name =  el.getAttribute("ajax-update")
        if(!trigger_name) return

        // Get the event that causes the trigger, default to click
        let event_name = el.getAttribute("ajax-trigger")
        if(!event_name) {
            // Default is 'click' unless element is a <form>
            // In which case, teh default trigger is 'ajax-post-complete' event
            // This will run after the POST request completes
            event_name = el.tagName !== "FORM" ? "click" : "ajax-post-complete"
        }

        el.addEventListener(event_name, () => ajax_event(trigger_name))

    })

    // Get elements that trigger ajax events
    document.querySelectorAll("*[ajax-post]").forEach(el => {

        // Get the endpoint that the data will be submitted to
        const url = el.getAttribute("ajax-post")

        if(el.tagName !== "FORM") {
            console.warn("ajax-post must be defined on a <form> tag")
            return
        }

        // Get the input elements that need to be submitted
        const inputs = el.querySelectorAll("*[ajax-data]")

        let options = el.getAttribute("ajax-options")
        if(options) {
            options = parse_options(options)
        }
            
        // Handle the submission
        el.addEventListener("submit", e => ajax_submit(e, url, el, inputs, options))

    })



    function ajax_event(name) {
        
        // Executes an ajax event
        if(!Object(window.ajax.listeners).hasOwnProperty(name)) return

        for (let i = 0; i < window.ajax.listeners[name].length; i++) {
            const [ url, el, parser, options ] = window.ajax.listeners[name][i]
            // Gets the parameters for this listening element
            ajax_get(url, el, parser, options)
        }
    }
    
    
    async function ajax_get(url, element, parser, options) {
        let response
        try {
            response = await (await fetch(url)).text()
        } catch (error) {
            report_error(`something went wrong fetching data from ${url}`, error)
        }
    
        // Check if there is a parser function to parse the data
        // Otherwise just write the exact response
        if(parser) {
            // If the user requested JSON data, then pass the response to the parser function as parsed JSON
            response = options?.json ? execute_parser(parser, JSON.parse(response), url) : execute_parser(parser, response, url)        
        }
        element.innerHTML = response
    }
    

    async function ajax_post(url, body) {
        try {
            let response = await fetch(url, {
                method: "POST",
                body: body
            })

            // Read the response message from the server
            return await response.text()
        } catch (error) {
            report_error("Could not submit form", error)
            return "Error Submitting"
        }
    }
    
    
    function parse_options(options_string) {
        // Returns an object with the key-values contained in the options_string
        let options = {}
    
        try {
    
            options_string.trim()
    
            // Split options into key-value pairs
            let key_vals = options_string.replace(/ /g, "").split(",")
    
            for (let i = 0; i < key_vals.length; i++) {
    
                // Get key and value pairs from each option
                let [k, v] = key_vals[i].split(":")
    
                // Handle boolean values
                if(v === "true") {
                    v = true
                } else if (v === "false") {
                    v = false
                }
    
                // Assign the value to this key on the options object
                options[k] = v
            }
            
            // Ensure options is an object
            return options !== '' ? options : {}
    
        } catch (error) {
            report_error("Malformed options string", error)
            return {}
        }
    }
    
    
    
    function get_valid_function(parser_name) {
        if(parser_name && Object(window).hasOwnProperty(parser_name)  && typeof window[parser_name] === "function") {
            return window[parser_name]
        }
        return null
    }
    
    
    
    function execute_parser(parser, data, url) {
        try {
            return parser(data)
        } catch (error) {
            report_error(`error parsing data from ${url}`, error)
            return 'An error occurred'
        }
    }
    
    
    
    function report_error(message, error) {
        if(error) console.error(error)
        console.error(`${error ? 'html-ajax: INFO ABOUT THE ABOVE ERROR \n' : ''} ajax-get error: ${message}`)
    }


    async function ajax_submit(event, url, form_element, input_elements, options) {
        // Disable the default form submission behavior
        event.preventDefault()

        let form_data = new FormData()

        // Extract the data from the form
        for (const input of input_elements) {
            form_data.append(input.getAttribute("ajax-data"), input.value)
        }

        // Post the data to the server
        let response = await ajax_post(url, form_data)


        // Dispatch the 'ajax-post-complete' event when the POST request is complete
        const submit_complete_event = new Event("ajax-post-complete")
        form_element.dispatchEvent(submit_complete_event)

        let shouldOverwrite = true

        // Check if the user does not want the form contents to be overwritten
        if(options && ("overwrite" in options)) {
            shouldOverwrite = options["overwrite"]
        }

        if(!shouldOverwrite) return
        
        // Default: Write the response over the form
        form_element.innerHTML = response


    }
}
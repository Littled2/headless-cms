<?php
/**
 * Called to process every request to the server
 */
function handle_request() {

    $page = get_page();

    // If request comes from the client side router, then the whole page does not need to be sent
    if(isset($_GET["csr"]) && $_GET["csr"] == 'true') {
        header('Content-type: application/json');
        echo json_encode($page);
        exit;
    }

    return $page;
}

function get_page() {
    // Gets the path of the webpage file on the local system
    $requested_path = get_requested_path();

    // Ensure there is a trailing slash
    if(!substr($requested_path, -1) !== '/') {
        $requested_path = $requested_path . '/';
    }

    // Absolute path to root of webpage file
    $dir_path = __DIR__ . '/webpages' . $requested_path;

    if(!does_page_exist($dir_path)) {
        return handle_error(404);
    }

    $page = get_parsed_page($dir_path);
    
    if($page == false) {
        return handle_error(500);
    }

    return $page;

}

function get_parsed_page($dir_path) {
    try {

        // If there is a template.php file, run it to get the webpage content

        if(is_file(($dir_path . 'template.php'))) {


            // Attempt to get a cached file if it exists

            $cached_page = get_cached_page($dir_path);

            // If cache miss, generate the page
            if($cached_page === false) {
                ob_start();

                // Run the script
                include $dir_path . 'template.php';

                // Get the output and store it as a string
                $template_content = ob_get_clean();

                $page = new Page($dir_path, $template_content);


                if(isset($page->settings["cache-for"]) && is_numeric($page->settings["cache-for"])) {
                    save_cached_page($dir_path, $template_content, time() + intval($page->settings["cache-for"]));
                }

                return new Page($dir_path, $template_content);

            } else {

                return $cached_page;
            }

        } else {
            // Get the page.html content
            return new Page($dir_path, file_get_contents($dir_path . 'page.html'));
        }

    } catch (\Throwable $th) {
        
        return false;

    }
}

function does_page_exist($dir_path) {
    if(is_file(($dir_path . 'page.html')) || is_file($dir_path . 'template.php')) {
        return true;
    }
    return false;
}

function get_requested_path() {

    $requested_file_name = parse_url($_SERVER['REQUEST_URI'])["path"];
    
    return $requested_file_name;
}

function handle_error($error_code) {

    // Set the correct HTTP response code
    http_response_code($error_code);

    $error_dir_path = __DIR__ . '/errors' . '/' . $error_code . '/' ;
    
    // Is there an error page
    if(does_page_exist($error_dir_path)) {

        $page = get_parsed_page($error_dir_path);

        // Then something went wrong with reading the file
        if($page == false) exit;

        return $page;
    }

    return new Page($error_dir_path, "<p style='text-align:center;>'Error {$error_code}</p>");
}




class Page {

    public $content;
    public $settings;
    private $dir_path;

    function __construct($dir_path, $raw_page_content) {

        $this->dir_path = $dir_path;


        list($hasSettings, $page_parts) = $this->parse_page_content($raw_page_content);

        if($hasSettings && count($page_parts) == 1) {

            // If the page has only settings and no content
            $this->content = '';
            $this->settings = $this->parse_raw_settings_block($page_parts[0]);

        } elseif(!$hasSettings && count($page_parts) === 1) {

            // If the page has no settings and just content
            $this->content = $page_parts[0];
            $this->settings = null;

        } else {

            // If the page has both settings and content
            $this->content = $page_parts[1];
            $this->settings = $this->parse_raw_settings_block($page_parts[0]);

        }

        // Path to possible styles.css file
        $styles_path = $this->dir_path . 'styles.css';

        // If there is a styles.css file, then include the styles in the page
        if(is_file($styles_path)) {
            $this->content = "<style>\n\n" . file_get_contents($styles_path) . "\n</style>\n\n" . $this->content;
        }

    }

    private function parse_page_content($page_content) {

        // Does the page have page settings
        $hasSettings = preg_match('/^.[=]+([\s]+)?$/m', $page_content) > 0;    

        // Split the file on a line of equals characters (The separator between setting and the page content)
        // From now on, this line will be referred to as a 'splitter' line.
        $split = preg_split('/^.[=]+([\s]+)?$/m', $page_content);

        return array($hasSettings, $split);
    }

    private function parse_raw_settings_block($raw_settings_block) {

        $raw_settings_block = preg_replace("/<!--(.*?)-->/", "", $raw_settings_block);

        $temp_settings = array();

        // Iterate over each new line
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $raw_settings_block) as $line) {
            // Split the line on the first ':' character
            $parts = explode(':', $line);

            if(count($parts) == 1) {
                // Then set as key-only setting
                $keyName = strtolower(trim($parts[0]));

                // Then key name is invalid
                if($keyName === '') continue;

                $temp_settings[$keyName] = true;
                continue;
            }

            if(count($parts) !== 2) {
                // Then is malformed settings line
                continue;
            }

            // Extract the name (key) of the setting and its respective value
            $key = strtolower(trim($parts[0]));
            $value = trim($parts[1]);

            $temp_settings[$key] = $value;

        }

        return $temp_settings;
    }

    function get_property($property_name) {

        // Check this setting exists
        if(isset($this->settings[$property_name])) {

            switch ($property_name) {
                case 'title':
                    return "<title>{$this->settings[$property_name]}</title><meta property='og:title' content='{$this->settings[$property_name]}' />";
                case 'description':
                    return "<meta name='description' content='{$this->settings[$property_name]}'><meta name='og:description' content='{$this->settings[$property_name]}'>";
                case 'og-image':
                    return "<meta property='og:image' content='{$this->settings[$property_name]}' />";
                case 'og-url':
                    return "<meta property='og:url' content='{$this->settings[$property_name]}' />";
                case 'og-type':
                    return "<meta property='og:type' content='{$this->settings[$property_name]}' />";
                case 'favicon':
                    return "<link rel='shortcut icon' type='image' href='{$this->settings[$property_name]}' />";
            }

            return $this->settings[$property_name];
        }


        // Page setting is NOT set

        switch ($property_name) {
            case 'favicon':
                return "<link rel='shortcut icon' type='image' href='/resources/favicon.png' />";
        }

        return '';
    }

}


/**
 * Returns the contents of an in-date cached template.php page if it exists
 */
function get_cached_page($dir_path) {

    $GET_hash = hash("sha256", $_SERVER["QUERY_STRING"]);

    $file_path = $dir_path . "template.cached." . $GET_hash;

    if(is_file($file_path)) {

        $cache_content = file_get_contents($file_path);

        // Check the TTL against the generated time. There will always be a settings block as pages are only cached
        // if the cache-for setting is set

        $page = new Page($dir_path, $cache_content);

        // Test if current time is before expiry
        if(time() < intval($page->settings["cache-expires"])) {

            return $page;

        } else {
            
            // Delete invalid cache file
            unlink($file_path);

            return false;
        }

    }
    
    // If file does not exist
    return false;
}



/**
 * Saves the executed template page
 */
function save_cached_page($dir_path, $page_content, $cache_invalid_at) {

    $GET_hash = hash("sha256", $_SERVER["QUERY_STRING"]);

    $file_path = $dir_path . "template.cached." . $GET_hash;

    $page_content = "generated: " . time() . "\n" . "cache-expires: " . $cache_invalid_at . "\n\n" . $page_content;

    // Save the cached file
    file_put_contents($file_path, $page_content);

}

?>
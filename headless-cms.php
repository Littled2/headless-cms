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

    // Path to page.html file
    $dir_path = __DIR__ . '/webpages' . $requested_path;

    if(!does_page_exist($dir_path)) {
        return handle_error(404);
    }

    $raw_page_content = get_page_content($dir_path);
    
    if($raw_page_content == false) {
        return handle_error(500);
    }

    if($raw_page_content == '') {
        return new Page($dir_path, '', null);
    }

    list($hasSettings, $page_parts) = parse_page_content($raw_page_content);

    if($hasSettings && count($page_parts) == 1) {
        return new Page($dir_path, '', $page_parts[0]);
    }

    if(count($page_parts) !== 2) {
        // Then there is no splitter line
        return new Page($dir_path, $page_parts[0], null);
    }

    return new Page($dir_path, $page_parts[1], $page_parts[0]);

}

function get_page_content($dir_path) {
    try {

        // Get the page.html content
        return file_get_contents($dir_path . 'page.html');

    } catch (\Throwable $th) {
        
        return false;

    }
}

function does_page_exist($dir_path) {
    if(is_file(($dir_path . 'page.html'))) {
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

        $raw_page_content = get_page_content($error_dir_path);

        // Then something went wrong with reading the file
        if($raw_page_content == false) exit;

        list($hasSettings, $page_parts) = parse_page_content($raw_page_content);

        if($hasSettings && count($page_parts) == 1) {
            return new Page($error_dir_path, '', $page_parts[0]);
        }

        if(count($page_parts) !== 2) {
            // Then there is no splitter line
            return new Page($error_dir_path, $page_parts[0], null);
        }
        
        return new Page($error_dir_path, $page_parts[1], $page_parts[0]);
    }

    return new Page($error_dir_path, "<p style='text-align:center;>'Error {$error_code}</p>", null);
}

function parse_page_content($page_content) {

    // Does the page have page settings
    $hasSettings = preg_match('/^.[=]+([\s]+)?$/m', $page_content) > 0;    

    // Split the file on a line of equals characters (The separator between setting and the page content)
    // From now on, this line will be referred to as a 'splitter' line.
    $split = preg_split('/^.[=]+([\s]+)?$/m', $page_content);

    return array($hasSettings, $split);
}

function parse_raw_settings_block($raw_settings_block) {

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

class Page {

    public $content;
    public $settings;
    private $dir_path;

    function __construct($dir_path, $page_content, $raw_settings_block) {

        $this->content = $page_content;
        $this->dir_path = $dir_path;


        // Path to possible styles.css file
        $styles_path = $this->dir_path . 'styles.css';

        // If there is a styles.css file, then include the styles in the page
        if(is_file($styles_path)) {
            $this->content = "<style>\n\n" . file_get_contents($styles_path) . "\n</style>\n\n" . $this->content;
        }

        if($raw_settings_block !== null) {
            $this->settings = parse_raw_settings_block($raw_settings_block);
        } else {
            $this->settings = null;
        }
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

?>
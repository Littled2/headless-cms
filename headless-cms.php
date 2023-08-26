<?php
/**
 * Called to process every request to the server
 */
function handle_request() {

    // Gets the path of the webpage file on the local system
    $requested_path = get_requested_path();

    // Ensure there is a trailing slash
    if(!substr($requested_path, -1) !== '/') {
        $requested_path = $requested_path . '/';
    }

    $real_path = __DIR__ . '/webpages' . $requested_path . 'page.html' ;

    if(!does_page_exist($real_path)) {
        return handle_error(404);
    }

    $raw_page_content = get_page_content($real_path);
    
    if($raw_page_content == false) {
        return handle_error(500);
    }

    if($raw_page_content == '') {
        return new Page('', null);
    }

    $page_parts = parse_page_content($raw_page_content);

    if(count($page_parts) !== 2) {
        // Then there is no splitter line
        return new Page($page_parts[0], null);
    }

    return new Page($page_parts[1], $page_parts[0]);


}

function get_page_content($path) {
    try {
        
        // Get the page content
        return file_get_contents($path);

    } catch (\Throwable $th) {
        
        return false;

    }
}

function does_page_exist($path) {
    if(is_file(($path))) {
        return true;
    }
    return false;
}

function get_requested_path() {
    $requested_file_name = '';

    if(array_key_exists('PATH_INFO', $_SERVER)) {
        $requested_file_name = $_SERVER['PATH_INFO'];
    } else {
        $requested_file_name = explode('?', $_SERVER['REQUEST_URI'])[0];
    }
    
    return $requested_file_name;
}

function handle_error($error_code) {

    // Set the correct HTTP response code
    http_response_code($error_code);

    $real_path = __DIR__ . '/errors' . '/' . $error_code . '.html' ;

    // Is there an error page
    if(is_file($real_path)) {

        $raw_page_content = get_page_content($real_path);

        // Then something went wrong with reading the file
        if($raw_page_content == false) exit;

        $page_parts = parse_page_content($raw_page_content);

        if(count($page_parts) !== 2) {
            // Then there is no splitter line
            return new Page($page_parts[0], null);
        }
        
        return new Page($page_parts[1], $page_parts[0]);
    }

    return new Page('Error ' . $error_code, 'hide_footer');
}


function parse_page_content($page_content) {

    // Split the file on a line of equals characters (The separator between setting and the page content)
    // From now on, this line will be referred to as a 'splitter' line.
    $split = preg_split('/^.[=]+[\s]+$/m', $page_content);

    return $split;
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

        // switch ($key) {
        //     case 'title':
        //         $temp_settings['title'] = $value;
        //         break;
        //     case 'description':
        //         $temp_settings['description'] = $value;
        //         break;
        //     default:
        //         // Then setting name is not recognised
        //         break;
        // }
    }

    return $temp_settings;
}

class Page {

    public $content;
    public $settings;

    function __construct($page_content, $raw_settings_block) {

        $this->content = $page_content;

        if($raw_settings_block !== null) {
            $this->settings = parse_raw_settings_block($raw_settings_block);
        } else {
            $this->settings = null;
        }
    }

}

?>
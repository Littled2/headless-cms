<?php

    require_once "headless-cms.php";

    // Requests must be GET
    if($_SERVER["REQUEST_METHOD"] !== "GET") {
        http_response_code(405);
        exit;
    }

    $page = handle_request();

    // If request comes from the client side router, then the whole page does not need to be sent
    if(isset($_GET["csr"]) && $_GET["csr"] == 'true') {
        header('Content-type: application/json');
        echo json_encode($page);
        exit;
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Import the Client Side Router -->
    <script defer src="/scripts/client-side-router.js"></script>

    <!-- Import Alpine JS -->
    <!-- Remove this if you don't wish to use Alpine JS within you webpages -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Page Title -->
    <title><?php
        echo isset($page->settings['title']) ? $page->settings['title'] : 'Default Title'
    ?></title>

    <!-- Page Description -->
    <meta name="description" content="<?php
        echo isset($page->settings['description']) ? $page->settings['title'] : 'Default Description'
    ?>">

    <!-- Add script imports here -->


    <!-- Add stylesheet imports here -->
    <link rel="stylesheet" href="/resources/stylesheets/globals.css">

</head>
<body>

    <header>
        <div class="logo">
            <span class="accent">{</span>
            <span class="logo-text">eb.</span>
            <span class="accent">}</span>
        </div>
        <nav class="flex-1">
            <ul>
                <li>
                    <small class="mono accent">01.</small>
                    <span>Blog</span>
                </li>
            </ul>
        </nav>
        <div>
            <button class="button">Learn More</button>
        </div>
    </header>

    <main>
        <!-- Insert the page body in here -->
        <?php echo $page->content; ?>
    </main>

    <footer>

    </footer>

</body>
</html>
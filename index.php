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
    <script defer src="/headless-cms-scripts/client-side-router.js"></script>

    <!-- Import Alpine JS -->
    <!-- Remove this if you don't wish to use Alpine JS within you webpages -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Page Title -->
    <title><?php
        echo isset($page->settings['title']) ? $page->settings['title'] : ''
    ?></title>

    <!-- Page Description -->
    <meta name="description" content="<?php
        echo isset($page->settings['description']) ? $page->settings['description'] : ''
    ?>">
    <meta name="og:description" content="<?php
        echo isset($page->settings['description']) ? $page->settings['description'] : ''
    ?>">

    <?php
        if(isset($page->settings["og-image"])) {
            echo "<meta property='og:image' content='{$page->settings["og-image"]}' />";
        }
    ?>


    <?php
        if(isset($page->settings['favicon'])) {
            echo "<link rel='shortcut icon' type='image' href='{$page->settings['favicon']}' />";
        } else {
            echo '<link rel="shortcut icon" type="image" href="/resources/favicon.png" />';
        }
    ?>


    <!-- Add stylesheet imports here -->






</head>
<body>
    <header <?php echo isset($page->settings['hide_heading']) ? 'style="display: none"' : '' ?>>

    </header>

    <main>
        <!-- Insert the page body in here -->
        <?php echo $page->content; ?>
    </main>

    <footer <?php echo isset($page->settings['hide_footer']) ? 'style="display: none"' : '' ?> >

    </footer>

</body>
</html>
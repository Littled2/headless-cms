<?php

    require_once "headless-cms.php";

    $page = handle_request();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Import the Client Side Router -->
    <!-- Remove this if you don't wish to use the client-side routing function of the Headless CMS -->
    <!-- <script defer src="/headless-cms-scripts/client-side-router.js"></script> -->

    <!-- Import html-ajax -->
    <script src="/scripts/html-ajax.js"></script>
    <script src="/scripts/global.js"></script>

    <!-- Import Alpine JS -->
    <!-- Remove this if you don't wish to use Alpine JS across you webpages -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>


    <!-- If the title property is set, insert here. -->
    <?php echo $page->get_property('title') ?>

    <!-- If the description property is set, insert here. -->
    <?php echo $page->get_property('description') ?>

    <!-- If the og-image property is set, insert here. -->
    <?php echo $page->get_property('og-image') ?>

    <!-- If the favicon property is set, insert here. -->
    <?php echo $page->get_property('favicon') ?>



    <!-- Add stylesheet imports here -->
    <link rel="stylesheet" href="/resources/stylesheets/globals.css">
    <link rel="stylesheet" href="/resources/stylesheets/default-styles.css">
    <link rel="stylesheet" href="/resources/stylesheets/utils.css">



</head>
<body>



    <header>
        
        <a class="top-logo" href="/">
            <img src="/resources/images/excs-final.svg">
        </a>

        <nav>
            <a class="link" href="/committee">Committee</a>
            <a class="link" href="/stash">Stash</a>
            <a class="link" href="/events">Events</a>
            <a target="_blank" class="link" href="https://my.exeterguild.com/groups/QRM97/computer-science-society/memberships">
                <span>Memberships</span>
                <img class="icon" src="/resources/images/icons/external-link-icon.svg">
            </a>
        </nav>

    </header>




    
    <main>
        <!-- Insert the page content in here -->
        <?php echo $page->content; ?>
    </main>



    <footer class="flex col gap-m">

        <p class="text-center">
            <span class="text-strong">Spotted an issue?</span>
            Please
            <a class="accent underline" href="mailto:excs@groups.exeterguild.com">Let us know</a>
             or
            <a class="accent inline-flex align-center underline" style="gap: 1em;" href="https://github.com/Exeter-Computer-Science-Society/website">
                fix it yourself!
            </a>!
        </p>

        <p class="text-center">
            <small>
                <span>Website built using open source</span>
                <a target="_blank" class="inline-flex align-center underline" style="gap: 0.5em;" href="https://github.com/Littled2/headless-cms">
                    <span>headless-cms</span>
                    <img class="icon" src="/resources/images/icons/external-link-icon.svg">
                </a>
            </small>
        </p>
    </footer>


</body>
</html>
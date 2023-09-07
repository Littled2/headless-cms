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
    <script defer src="/headless-cms-scripts/client-side-router.js"></script>

    <!-- Import Alpine JS -->
    <!-- Remove this if you don't wish to use Alpine JS across you webpages -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>


    <!-- If the title property is set, insert here. -->
    <?php $page->get_property('title') ?>

    <!-- If the description property is set, insert here. -->
    <?php $page->get_property('description') ?>

    <!-- If the og-image property is set, insert here. -->
    <?php $page->get_property('og-image') ?>

    <!-- If the favicon property is set, insert here. -->
    <?php $page->get_property('favicon') ?>



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
            <a class="link" href="/mentorships">
                <span class="accent">new</span>
                <span>Mentoring</span>
            </a>
            <a class="link" href="/events">Events</a>
            <a class="link" href="https://my.exeterguild.com/groups/QRM97/computer-science-society/memberships">
                <span>Memberships</span>
                <img class="icon" src="/resources/images/icons/external-link-icon.svg">
            </a>
        </nav>

    </header>




    <main>
        <!-- Insert the page content in here -->
        <?php echo $page->content; ?>
    </main>




    <footer class="flex col gap-m text-center">

        <p><span class="text-strong">Spotted an issue?</span> Please <a class="accent" href="mailto:excs">Let us know</a> or <a class="accent flex align-center" style="gap: 1em;" href="https://github.com/Exeter-Computer-Science-Society/website"><img class="icon" src="/resources/images/icons/github.svg"> it yourself!</a>!</p>

        <p><small>Website built using open source <a href="https://github.com/Littled2/headless-cms" class="underline">headless-cms</a></small></p>

    </footer>


</body>
</html>
<?php
if (!isset($pageTitle)) {
    $pageTitle = "Powercabs Dispatcher";
}
?>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <?php if (!empty($extraHeadHtml)) { echo $extraHeadHtml; } ?>

    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://maps.googleapis.com">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="/global.css" />
    <script src="js/global-loader.js"></script>
    <script src="js/status-badge.js" defer></script>
    <script src="js/rides-poller.js" defer></script>
</head>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'FINONEST TeleCRM'; ?></title>
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/icons.css">
    <meta name="description" content="Modern, free, and open-source telecalling CRM with auto dialer, lead management, and WhatsApp integration for growing businesses.">
</head>
<body>
    <div class="app-container">
        <!-- Desktop Layout -->
        <div class="desktop-layout">
            <?php include 'components/sidebar.php'; ?>
            <div class="main-content">
                <?php include 'components/header-bar.php'; ?>
                <main class="content-area">
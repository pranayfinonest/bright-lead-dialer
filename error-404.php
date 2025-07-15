<?php
session_start();
$pageTitle = '404 - Page Not Found - FINONEST TeleCRM';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="assets/images/logo.png" alt="FINONEST Logo" class="auth-logo">
                <h1>FINONEST</h1>
                <p>trust comes first</p>
            </div>
            
            <div class="auth-form">
                <div class="error-container">
                    <div class="error-icon">
                        <i class="icon-search"></i>
                    </div>
                    <h2>Page Not Found</h2>
                    <p class="error-message">
                        The page you're looking for doesn't exist or has been moved.
                    </p>
                    
                    <div class="error-actions">
                        <a href="javascript:history.back()" class="btn btn-outline">
                            <i class="icon-arrow-left"></i>
                            Go Back
                        </a>
                        <a href="index.php" class="btn btn-primary">
                            <i class="icon-home"></i>
                            Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
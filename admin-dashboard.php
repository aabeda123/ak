<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        display: flex;
    }

    .main-content {
        margin-left: 220px;
        /* keep sidebar width */
        padding: 20px;
        width: calc(100% - 220px);
        height: 100vh;
        /* make full viewport height */
        overflow-y: auto;
        /* enable vertical scrolling */
    }


    .sidebar {
        width: 220px;
        background: #2c3e50;
        color: white;
        height: 100vh;
        position: fixed;
    }

    .sidebar .profile {
        text-align: center;
        padding: 20px;
    }

    .profile-pic {
        width: 80px;
        border-radius: 50%;
    }

    .online {
        color: limegreen;
    }

    .sidebar .menu {
        list-style: none;
        padding: 0;
    }

    .sidebar .menu li a {
        display: block;
        padding: 12px 20px;
        color: white;
        text-decoration: none;
    }

    .sidebar .menu li a:hover {
        background-color: #34495e;
    }

    .main-content {
        margin-left: 220px;
        padding: 20px;
        width: 100%;
    }

    .dashboard-widgets,
    .social-widgets {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .widget {
        flex: 1;
        min-width: 180px;
        background: #ecf0f1;
        padding: 20px;
        border-radius: 10px;
    }

    .fb {
        background: #3b5998;
        color: white;
    }

    .tw {
        background: #00aced;
        color: white;
    }

    .in {
        background: #007bb6;
        color: white;
    }

    .gp {
        background: #dd4b39;
        color: white;
    }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="profile">
            <img src="assets/images/admin.jpg" alt="Admin" class="profile-pic">
            <h3>AK store</h3>
            <p class="online">● Online</p>
        </div>
        <ul class="menu">
            <li><a href="index.php"><i class="fa fa-home"></i> Dashboard</a></li>
            <li><a href="manage_user.php"><i class="fa fa-users"></i> Manage Users</a></li>
            <li><a href="upload_notification.php"><i class="fa fa-file-alt"></i> notification upload</a></li>
            <li><a href="manage_posts.php"><i class="fa fa-file-alt"></i> Manage Posts</a></li>
            <li><a href="view_order.php"><i class="fa fa-shopping-cart"></i> View Orders</a></li>
            <li><a href="upload_clothes.php"><i class="fa fa-upload"></i> Upload Clothes</a></li>
            <li><a href="manage_clothes.php"><i class="fa fa-upload"></i>Manage Clothes</a></li>
            <li><a href="payment_notification.php"><i class="fa fa-upload"></i>Paymnet Notification</a></li>
            <li><a href="uploadclothes_recommendation.php"><i class="fa fa-upload"></i>Upload Clothes for
                    recommendation</a></li>
            <li><a href="manageclothes_recommendation.php"><i class="fa fa-users"></i> Manage clothes for
                    recommendation</a></li>
            <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
            <li><a href="#"><i class="fa fa-cog"></i> Settings</a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h2>Dashboard</h2>
        </header>
        <div class="dashboard-widgets">
            <div class="widget">👤 <h3>2500</h3>
                <p>Welcome</p>
            </div>
            <div class="widget">⏱ <h3>123.50</h3>
                <p>Average Time</p>
            </div>
            <div class="widget">📦 <h3>1805</h3>
                <p>Collections</p>
            </div>
            <div class="widget">💬 <h3>54</h3>
                <p>Comments</p>
            </div>
        </div>
        <div class="social-widgets">
            <div class="widget fb">Facebook<br><small>35k Friends • 128 Feeds</small></div>
            <div class="widget tw">Twitter<br><small>584k Followers • 978 Tweets</small></div>
            <div class="widget in">LinkedIn<br><small>758+ Contacts • 365 Feeds</small></div>
            <div class="widget gp">Google+<br><small>450 Followers • 57 Circles</small></div>
        </div>
        <!-- You can add charts here -->
    </div>
</body>

</html>
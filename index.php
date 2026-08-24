<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lostNfound");

$isLoggedIn = isset($_SESSION['logged_in']);
$userName = "";
$email = "";
if(isset($_GET['msg']))
{
    if($_GET['msg'] == 'name_updated')
        echo "<p style='color:green;'>✅ Name updated successfully</p>";

    if($_GET['msg'] == 'contact_updated')
        echo "<p style='color:green;'>✅ Contact updated successfully</p>";
}
if($isLoggedIn)
{
    $email = $_SESSION['user'];
    if(isset($_SESSION['user_name']))
        $userName = $_SESSION['user_name'];
    else
    {
        $res = mysqli_query($conn, "SELECT name FROM users WHERE email='$email'");
        if($row = mysqli_fetch_assoc($res))
        {
            $userName = $row['name'];
            $_SESSION['user_name'] = $userName;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Smart Lost & Found</title>
</head>

<body>

<header>
    <h1>🔍 Smart Lost & Found System</h1>
    <p>Particular for IIIT Manipur</p>

    <?php if(!$isLoggedIn): ?>
        <a href="login.php"><button>Login</button></a>
    <?php else: ?>
        <p>Welcome, <b><?php echo $userName; ?></b></p>

        <a href="change_name.php"><button>Change Name</button></a>
        <a href="change_contact.php"><button>Change Phone</button></a>
        <a href="forgot_password.php"><button>Change Password</button></a>
        <a href="logout.php"><button>Logout</button></a>
    <?php endif; ?>
</header>

<hr>

<nav>
    <a href="lost.php">Lost</a> |
    <a href="found.php">Found</a> |
    <a href="contact.php">Contact Us</a>
</nav>

<hr>

<div>
    <h2>Did you lose or find anything?</h2>
    <a href="lost.php"><button>Report Lost Item</button></a>
    <a href="found.php"><button>Report Found Item</button></a>
</div>

<hr>

<div style="display:flex; gap:20px;">

    <!-- LOST ITEMS -->
    <div style="width:25%;">
        <h3>📌 My Lost Items</h3>
        <?php
        if($isLoggedIn)
        {
            $res = mysqli_query($conn, "SELECT * FROM lost_items WHERE user_email='$email'");
            if(mysqli_num_rows($res) == 0)
                echo "No items reported";
            else
            {
                while($row = mysqli_fetch_assoc($res))
                {
                    echo "<div style='border:1px solid #ccc; padding:8px; margin:5px;'>";
                    echo "<b>{$row['item_type']}</b><br>";
                    echo "Location: {$row['location']}<br>";
                    echo "<a href='edit_lost.php?id={$row['id']}'><button>Edit</button></a>";
                    echo "</div>";
                }
            }
        }
        else echo "Login to see your data";
        ?>
    </div>

    <!-- FOUND ITEMS -->
    <div style="width:25%;">
        <h3>📦 My Found Items</h3>
        <?php
        if($isLoggedIn)
        {
            $res = mysqli_query($conn, "SELECT * FROM found_items WHERE user_email='$email'");
            if(mysqli_num_rows($res) == 0)
                echo "No found items";
            else
            {
                while($row = mysqli_fetch_assoc($res))
                {
                    echo "<div style='border:1px solid #ccc; padding:8px; margin:5px;'>";
                    echo "<b>{$row['item_type']}</b><br>";
                    echo "Location: {$row['location']}<br>";
                    echo "<a href='edit_found.php?id={$row['id']}'><button>Edit</button></a>";
                    echo "</div>";
                }
            }
        }
        else echo "Login to see your data";
        ?>
    </div>

<div style="width:25%;">
    <h3>🔔 Possible Matches</h3>

    <?php
    if($isLoggedIn)
    {
        $res = mysqli_query($conn, "SELECT * FROM possible_match WHERE lemail='$email' ORDER BY confidence DESC");

        if(!$res)
        {
            echo "❌ Error fetching matches";
        }

        if(mysqli_num_rows($res) == 0)
        {
            echo "No matches found yet";
        }
        else
        {
            while($row = mysqli_fetch_assoc($res))
            {
                echo "<div style='border:1px solid #ccc; padding:10px; margin:5px;'>";

                echo "<b>Item:</b> ".$row['item_type']."<br>";
                echo "<b>Match Score:</b> ".$row['confidence']."%<br>";
                echo "<b>Found by:</b> ".$row['fname']."<br>";
                echo "<b>Contact:</b> ".$row['contact']."<br>";

                echo "<b>Email: </b>".$row['femail']."</b>";

                echo "</div>";
            }
        }
    }
    else
    {
        echo "Login to see matches";
    }
    ?>
</div>


    <!-- PUBLIC DATA -->
    <div style="width:25%;">
        <h3>🎉 Recovered Items</h3>
        <?php
        $res = mysqli_query($conn, "SELECT * FROM recovered_items ORDER BY created_at DESC LIMIT 5");

        if($res && mysqli_num_rows($res) > 0)
        {
            while($row = mysqli_fetch_assoc($res))
            {
                echo "<p>{$row['item_type']} (Recovered on {$row['created_at']})</p>";
            }
        }
        else echo "No recovered items yet";
        ?>
    </div>

</div>

<hr>

<footer>
    <p>© Gamma Coders | All Rights Reserved</p>
</footer>

</body>
</html>
<?php
session_start();
$conn = mysqli_connect("localhost","root","","lostNfound");
if(!isset($_SESSION['user']))
{
    header("Location: login.php");
    exit();
}
$email = $_SESSION['user'];
$msg = "";
$res = mysqli_query($conn, "SELECT name FROM users WHERE email='$email'");
$data = mysqli_fetch_assoc($res);
$currentName = $data['name'] ?? "";
if(isset($_POST['update']))
{
    $name = trim($_POST['name']);
    if(strlen($name) < 2)
        $msg = "❌ Name must be at least 2 characters";
    else
    {
        mysqli_query($conn, "UPDATE users SET name='$name' WHERE email='$email'");
        $_SESSION['user_name'] = $name;
        header("Location: index.php?msg=name_updated");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Change Name</title>
    </head>
    <body>
        <h2>Change Name</h2>
        <form method="POST">
            <label>Current Name:</label><br>
            <input type="text" value="<?php echo $currentName; ?>" disabled><br><br>
            <label>New Name:</label><br>
            <input type="text" name="name" required><br><br>
            <button name="update">Update Name</button>
        </form>
        <br>
        <a href="index.php">⬅ Back to Main Page</a>
        <p style="color:red;"><?php echo $msg; ?></p>
    </body>
</html>
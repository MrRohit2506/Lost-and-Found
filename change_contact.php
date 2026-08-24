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
$res = mysqli_query($conn, "SELECT contact FROM users WHERE email='$email'");
$data = mysqli_fetch_assoc($res);
$currentContact = $data['contact'] ?? "";
if(isset($_POST['update']))
{
    $contact = trim($_POST['contact']);
    if(!preg_match('/^[0-9]{10}$/', $contact))
        $msg = "❌ Enter valid 10-digit number";
    else
    {
        mysqli_query($conn, "UPDATE users SET contact='$contact' WHERE email='$email'");
        header("Location: index.php?msg=contact_updated");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Change Contact</title>
    </head>
    <body>
        <h2>Change Phone Number</h2>
        <form method="POST">
            <label>Current Number:</label><br>
            <input type="text" value="<?php echo $currentContact; ?>" disabled><br><br>
            <label>New Number:</label><br>
            <input type="text" name="contact" required><br><br>
            <button name="update">Update Number</button>
        </form>
        <br>
        <a href="index.php">⬅ Back to Main Page</a>
        <p style="color:red;"><?php echo $msg; ?></p>
    </body>
</html>
<?php
session_start();
if(!isset($_SESSION['logged_in']))
{
    header("Location: login.php");
    exit();
}
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
$conn = mysqli_connect("localhost", "root", "", "lostNfound");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contact VARCHAR(15) NOT NULL,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$email = $_SESSION['user'];
$res = mysqli_query($conn, "SELECT name, contact FROM users WHERE email='$email'");
$user = mysqli_fetch_assoc($res);
$name = $user['name'];
$contact = $user['contact'];
if(isset($_POST['submit']))
{
    $type = $_POST['type'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    if(empty($type) || empty($message))
        $error = "❌ Please fill all fields";
    else
    {
        mysqli_query($conn, "INSERT INTO contact_messages (name,email,contact,type,message) 
        VALUES ('$name','$email','$contact','$type','$message')");
        $mail = new PHPMailer(true);
        try
        {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'bsbshsjjehsbzznnxnxnz@gmail.com';
            $mail->Password = 'gitxvilsjkqjgums';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->setFrom('bsbshsjjehsbzznnxnxnz@gmail.com', 'Lost & Found System');
            $mail->addAddress('bsbshsjjehsbzznnxnxnz@gmail.com');
            $mail->isHTML(true);
            $mail->Subject = "New $type from $name";
            $mail->Body = "
                <h3>📩 New Message</h3>
                <b>Name:</b> $name <br>
                <b>Email:</b> $email <br>
                <b>Contact:</b> $contact <br>
                <b>Type:</b> $type <br><br>
                <b>Message:</b><br>
                $message
                <hr>
                <small>Lost & Found System</small>";
            $mail->send();
            $success = "✅ Message sent successfully!";
        }
        catch (Exception $e)
        {
            $error = "❌ Mail Error: " . $mail->ErrorInfo;
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Contact / Feedback / Report Bug</title>
    </head>
    <body>
        <div>
            <h2>📩 Contact / Feedback / Report Bug</h2>
            <div>
                <h3>Our Info :</h3>
                <p><b>Name:</b>TEAM GAMMA CODERS</p>
                <p><b>Email:</b>gammacoder@gmail.com</p>
                <p><b>Contact:</b> <?php echo $contact; ?></p>
                <h4>Feel Free to Contact Us Without Any Hesitation</h4>
            </div>
            <?php if(isset($error)) echo "<p class='msg' style='color:red;'>$error</p>"; ?>
            <?php if(isset($success)) echo "<p class='msg' style='color:green;'>$success</p>"; ?>
            <div>
                <form method="POST" id="form">
                    <select name="type" id="type" required>
                        <option value="">Select Type</option>
                        <option value="Feedback">Feedback</option>
                        <option value="Bug Report">Report Bug</option>
                        <option value="Support">Support</option>
                    </select>
                    <textarea name="message" id="message" rows="4" placeholder="Enter your message..." required></textarea>
                    <button type="submit" name="submit" id="submitBtn" disabled>Send</button>
                </form>
            </div>
            <a href="index.php">⬅ Back to Main Page</a>
        </div>
        <script>
            const type = document.getElementById("type");
            const msg = document.getElementById("message");
            const btn = document.getElementById("submitBtn");
            function validate()
            {
                let valid = true;
                if(!type.value)
                    valid = false;
                if(msg.value.trim().length < 5)
                    valid = false;
                btn.disabled = !valid;
            }
            type.addEventListener("change", validate);
            msg.addEventListener("input", validate);
        </script>
    </body>
</html>
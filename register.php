<?php
session_start();
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';
$conn = mysqli_connect("localhost", "root", "");
$sql = "CREATE DATABASE IF NOT EXISTS lostNfound";
if (!mysqli_query($conn, $sql))
    echo "Error: " . mysqli_error($conn);
mysqli_select_db($conn, "lostNfound");
$sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        email VARCHAR(100) UNIQUE,
        contact VARCHAR(15) UNIQUE,
        password VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
if (!mysqli_query($conn, $sql))
    echo "Error: " . mysqli_error($conn);
use PHPMailer\PHPMailer\PHPMailer;
if(isset($_POST['reset']))
{
    unset($_SESSION['verified']);
    unset($_SESSION['otp']);
    unset($_SESSION['email']);
}
if(isset($_POST['send_otp']))
{
    if(isset($_SESSION['otp']))
        $email = $_SESSION['email'];
    else
        $email = $_POST['email'] ?? '';
    if(empty($email))
    {
        echo "❌ Email missing. Please enter email again.";
        exit();
    }
    $email = mysqli_real_escape_string($conn, $email);
    if(!preg_match('/^[a-zA-Z]+\d+@iiitmanipur\.ac\.in$/', $email))
    {
        echo "❌ Invalid email format";
        exit();
    }
    $local = explode("@", $email)[0];
    if(!preg_match('/^([a-zA-Z]+)(\d+)$/', $local, $match))
    {
        echo "❌ Invalid format";
        exit();
    }
    $letters = $match[1];
    $numbers = $match[2];
    if(preg_match('/^(23|24)/', $numbers))
    {
        if(strlen($letters) != 4 || !preg_match('/^(23|24)010[1-4]\d{3}$/', $numbers))
        {
            echo "❌ Invalid 23/24 format";
            exit();
        }
    }
    else if(preg_match('/^25/', $numbers))
    {
        if(!preg_match('/^25[1-6]\d{3}$/', $numbers))
        {
            echo "❌ Invalid 25 format";
            exit();
        }
    }
    else
    {
        echo "❌ Invalid starting digits";
        exit();
    }
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0) 
        echo "❌ Email already registered";
    else
    {
        if(isset($_SESSION['otp_time']) && (time() - $_SESSION['otp_time'] < 30))
        {
            echo "⏳ Please wait before requesting OTP again";
            exit();
        }
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;
        $_SESSION['otp_time'] = time();
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'bsbshsjjehsbzznnxnxnz@gmail.com';
        $mail->Password = 'gitxvilsjkqjgums';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('bsbshsjjehsbzznnxnxnz@gmail.com', 'Lost & Found');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'OTP Verification';
        $mail->Body = "
            <h3>🔐 Email Verification</h3>
            <p>Your OTP is: <b style='font-size:18px;'>$otp</b></p>
            <p>⏳ This OTP is valid for <b>5 minutes</b>.</p>
            <p>If you did not request this, please report to college.</p>
            <br>
            <small>— Gamma Coders | Lost & Found</small>";
        try
        {
            $mail->send();
            echo "✅ OTP Sent";
        }
        catch (Exception $e)
        {
            echo "❌ Mail Error: " . $mail->ErrorInfo;
        }
    }
}
if(isset($_POST['verify_otp']))
    if(isset($_SESSION['otp']) && isset($_SESSION['otp_time']))
    {
        if(time() - $_SESSION['otp_time'] > 300)
        {
            echo "❌ OTP expired. Please resend OTP.";
            unset($_SESSION['otp']);
            unset($_SESSION['otp_time']);
            exit();
        }
        if($_POST['otp'] == $_SESSION['otp'])
        {
            $_SESSION['verified'] = true;
            unset($_SESSION['otp']);
            unset($_SESSION['otp_time']);
            echo "✅ Email Verified";
        }
        else
            echo "❌ Wrong OTP";
    }
if(isset($_POST['Register']))
{
    $email = $_SESSION['email'] ?? '';
    if(empty($email))
    {
        echo "❌ Session expired. Please verify again.";
        exit();
    }
    if(!isset($_SESSION['verified']))
        echo "❌ Please verify email first";
    else
    {
        $email=mysqli_real_escape_string($conn, $_SESSION['email']);
        $name=mysqli_real_escape_string($conn, $_POST['name']);
        $pswd = password_hash($_POST['pswd'], PASSWORD_DEFAULT);
        $no=mysqli_real_escape_string($conn, $_POST['no']);
        $check = mysqli_query($conn, "SELECT id FROM users WHERE contact='$no'");
        if(mysqli_num_rows($check) > 0) 
            echo "❌ Contact No. already registered";
        else
        {
            $insert = mysqli_query($conn, "INSERT INTO users (name, email, contact, password) VALUES ('$name', '$email', '$no', '$pswd')");
            if($insert)
            {
                session_unset();
                session_destroy();
                header("Location: login.php");
                exit();
            }
            else
                echo "❌ Error: " . mysqli_error($conn);
        }
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>AI Lost & Found System</title>
    </head>
    <body>
        <header>
            <h1>Register</h1>
        </header>
        <div class="container">
            <div class="card">
                <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
                    <label>Email ID : </label>
                    <input type="email" name='email' id="regEmail" placeholder="Email"  value="<?php 
                        if(isset($_SESSION['email'])) echo $_SESSION['email'];
                        else if(isset($_POST['email'])) echo $_POST['email'];
                    ?>" required>
                    <button id="sendOtpBtn" type="submit" name="send_otp" disabled><?php echo isset($_SESSION['otp_time']) ? "Resend OTP" : "Send OTP"; ?></button>
                    <button type="submit" name="reset">Change Email</button>
                    <p id="otpTimer"></p>
                    <p id="resendTimer"></p>
                    <p style="color:red;" id="msg"></p>
                    <label>Enter OTP : </label>
                    <input id="otpInput" type="text" name="otp" placeholder="Enter OTP">
                    <button id="verifyBtn" type="submit" name="verify_otp" disabled>Verify</button><br><br>
                    <label>Full Name : </label>
                    <input type="text" name='name' id="regName" placeholder="Full Name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>"><br><br>
                    <label>Contact No. : </label>
                    <input type="tel" name='no' id="regContact" placeholder="Contact Number" value="<?php echo isset($_POST['no']) ? $_POST['no'] : ''; ?>">
                    <p style="color:red;" id="contactMsg"></p>
                    <label>Password : </label>
                    <div style="position: relative; display: inline-block;">
                        <input type="password" name="pswd" id="regPassword" placeholder="Password">
                        <span id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; user-select: none;">👁️</span>
                    </div>
                    <small>Password must be at least 6 characters</small>
                    <button type="submit" id="regbtn" name="Register" disabled>Register</button>
                </form>
                <p> Already have an account? <a href="login.php">Login</a> </p>
            </div>
            <a href="index.php">⬅ Back to Main Page</a>
        </div>
        <footer>
            <p>© Gamma Coders | All Rights Reserved</p>
        </footer>
        <script>
            const sendOtpBtn = document.getElementById("sendOtpBtn");
            const resendTimerEl = document.getElementById("resendTimer");
            const otpTimerEl = document.getElementById("otpTimer");
            let otpSentTime = <?php echo isset($_SESSION['otp_time']) ? $_SESSION['otp_time'] : 'null'; ?>;
            if (otpSentTime)
            {
                const expiryTime = otpSentTime + 300;
                const interval = setInterval(() => {
                    const now = Math.floor(Date.now() / 1000);
                    let otpRemaining = expiryTime - now;
                    if (otpRemaining <= 0)
                    {
                        otpTimerEl.innerText = "❌ OTP expired";
                        otpTimerEl.style.color = "red";
                        resendTimerEl.innerText = "🔁 Please resend OTP";
                        resendTimerEl.style.color = "blue";
                        verifyBtn.disabled = true;
                        otpInput.disabled = true;
                        clearInterval(interval);
                    }
                    else
                    {
                        const m = Math.floor(otpRemaining / 60);
                        const s = otpRemaining % 60;
                        const time = String(m).padStart(2, '0') + ":" + String(s).padStart(2, '0');
                        otpTimerEl.innerText = "⏳ OTP expires in " + time;
                        if (otpRemaining < 60)
                            otpTimerEl.style.color = "red";
                        else
                            otpTimerEl.style.color = "green";
                    }
                    let resendRemaining = 30 - (now - otpSentTime);
                    if (resendRemaining <= 0)
                    {
                        sendOtpBtn.disabled = false;
                        sendOtpBtn.innerText = "Resend OTP";
                        resendTimerEl.innerText = "✅ You can resend OTP now";
                        resendTimerEl.style.color = "green";
                        sendOtpBtn.style.cursor = "pointer";
                    }
                    else
                    {
                        sendOtpBtn.disabled = true;
                        const m = Math.floor(resendRemaining / 60);
                        const s = resendRemaining % 60;
                        const time = String(m).padStart(2, '0') + ":" + String(s).padStart(2, '0');
                        resendTimerEl.innerText = "⏳ Resend available in " + time;
                        resendTimerEl.style.color = "orange";
                        sendOtpBtn.style.cursor = "not-allowed";
                    }
                }, 1000);
            }
            const emailInput = document.getElementById("regEmail");
            const otpInput = document.getElementById("otpInput");
            const verifyBtn = document.getElementById("verifyBtn");
            const registerBtn = document.getElementById("regbtn");
            const msg = document.getElementById("msg");
            let isEmailValid = false;
            let isOtpValid = false;
            emailInput.addEventListener("input", function () {
                const email = emailInput.value;
                if (!email.includes("@"))
                {
                    msg.innerText = "❌ Missing @ symbol";
                    sendOtpBtn.disabled = true;
                    msg.style.color = "red";
                    isEmailValid = false;
                    return;
                }
                const parts = email.split("@");
                const local = parts[0];
                const domain = parts[1];
                if (domain !== "iiitmanipur.ac.in")
                {
                    msg.innerText = "❌ Only college email id allowed";
                    sendOtpBtn.disabled = true;
                    msg.style.color = "red";
                    isEmailValid = false;
                    return;
                }
                const match = local.match(/^([a-zA-Z]+)(\d+)$/);
                if (!match)
                {
                    msg.innerText = "❌ Format must be letters followed by numbers";
                    sendOtpBtn.disabled = true;
                    msg.style.color = "red";
                    isEmailValid = false;
                    return;
                }
                const letters = match[1];
                const numbers = match[2];
                if (/^(23|24)/.test(numbers))
                {
                    if (letters.length !== 4)
                    {
                        msg.innerText = "❌ Must have exactly 4 letters for 23/24";
                        sendOtpBtn.disabled = true;
                        msg.style.color = "red";
                        isEmailValid = false;
                        return;
                    }
                    if (!/^(23|24)010[1-4]\d{3}$/.test(numbers))
                    {
                        msg.innerText = "❌ Invalid Roll No Format";
                        sendOtpBtn.disabled = true;
                        msg.style.color = "red";
                        isEmailValid = false;
                        return;
                    }
                }
                else if (/^25/.test(numbers))
                {
                    if (!/^25[1-6]\d{3}$/.test(numbers))
                    {
                        msg.innerText = "❌ Invalid Roll No Format";
                        sendOtpBtn.disabled = true;
                        msg.style.color = "red";
                        isEmailValid = false;
                        return;
                    }
                }
                else
                {
                    msg.innerText = "❌ Invalid starting digits";
                    sendOtpBtn.disabled = true;
                    msg.style.color = "red";
                    isEmailValid = false;
                    return;
                }
                const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!validEmail.test(email))
                {
                    msg.innerText = "❌ Invalid email format";
                    sendOtpBtn.disabled = true;
                    msg.style.color = "red";
                    isEmailValid = false;
                    return;
                }
                msg.innerText = "✅ Valid email";
                msg.style.color = "green";
                sendOtpBtn.disabled = false;
                isEmailValid = true;
            });
            otpInput.addEventListener("input", function () {
                const otp = otpInput.value;
                if (/^[0-9]{6}$/.test(otp))
                {
                    verifyBtn.disabled = false;
                    isOtpValid = true;
                }
                else
                {
                    verifyBtn.disabled = true;
                    isOtpValid = false;
                }
            });
            const contactInput = document.getElementById("regContact");
            const contactMsg = document.getElementById("contactMsg");
            let isContactValid = false;
            let isVerified = <?php echo isset($_SESSION['verified']) ? 'true' : 'false'; ?>;
            window.addEventListener("load", function () {
                if (isVerified)
                {
                    emailInput.disabled = true;
                    sendOtpBtn.disabled = true;
                    emailInput.style.cursor = "not-allowed";
                }
            });
            let isOtpSent = <?php echo isset($_SESSION['otp']) ? 'true' : 'false'; ?>;
            window.addEventListener("load", function () {
                if (isOtpSent)
                {
                    emailInput.disabled = true;
                    sendOtpBtn.disabled = true;
                    emailInput.style.cursor = "not-allowed";
                }
            });
            contactInput.addEventListener("input", function () {
                const number = contactInput.value;
                if (!/^[0-9]+$/.test(number))
                {
                    contactMsg.innerText = "❌ Only digits allowed";
                    isContactValid = false;
                    return;
                }
                if (number.length !== 10)
                {
                    contactMsg.innerText = "❌ Must be exactly 10 digits";
                    isContactValid = false;
                    return;
                }
                contactMsg.innerText = "✅ Valid contact number";
                contactMsg.style.color = "green";
                isContactValid = true;
            });
            document.querySelector("form").addEventListener("input", function () {
                const name = document.getElementById("regName").value;
                const contact = document.getElementById("regContact").value;
                const password = document.getElementById("regPassword").value;
                if (isVerified && name && isContactValid && password.length >= 6)
                    registerBtn.disabled = false;
                else
                    registerBtn.disabled = true;
            });
            const togglePassword = document.getElementById("togglePassword");
            const passwordInput = document.getElementById("regPassword");
            togglePassword.addEventListener("click", function () {
                if (passwordInput.type === "password")
                {
                    passwordInput.type = "text";
                    togglePassword.innerText = "🙈";
                }
                else
                {
                    passwordInput.type = "password";
                    togglePassword.innerText = "👁️";
                }
            });
        </script>
    </body>
</html>
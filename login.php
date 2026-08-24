<?php
session_start();
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
$conn = mysqli_connect("localhost", "root", "", "lostNfound");
if(isset($_POST['login']))
{
    $email = $_POST['email'] ?? $_SESSION['login_email'] ?? '';
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
    $password = $_POST['password'] ?? '';
    if(empty($password))
    {
        echo "❌ Password required";
        exit();
    }
    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($query) > 0)
    {
        $user = mysqli_fetch_assoc($query);
        if(password_verify($password, $user['password']))
        {
            if(isset($_SESSION['login_otp_time']) && (time() - $_SESSION['login_otp_time'] < 30))
            {
                echo "⏳ Please wait before requesting OTP again";
                exit();
            }
            $otp = rand(100000, 999999);
            $_SESSION['login_otp'] = $otp;
            $_SESSION['login_email'] = $email;
            $_SESSION['login_otp_time'] = time();
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
            $mail->Subject = 'Login OTP';
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
                echo "✅ OTP sent to your email";
            }
            catch (Exception $e)
            {
                echo "❌ Mail error: " . $mail->ErrorInfo;
            }
        }
        else
            echo "❌ Wrong password";
    }
    else
        echo "❌ Email not registered";
}
if(isset($_POST['verify_login_otp']))
{
    if(isset($_SESSION['login_otp']) && isset($_SESSION['login_otp_time']))
    {
        if(time() - $_SESSION['login_otp_time'] > 300)
        {
            echo "❌ OTP expired. Please resend OTP.";
            unset($_SESSION['login_otp']);
            unset($_SESSION['login_otp_time']);
            exit();
        }
        if($_POST['otp'] == $_SESSION['login_otp'])
        {
            $_SESSION['logged_in'] = true;
            $_SESSION['user'] = $_SESSION['login_email'];
            unset($_SESSION['login_otp']);
            unset($_SESSION['login_otp_time']);
            header("Location: index.php");
            exit();
        }
        else
            echo "❌ Invalid OTP";
    }
}
if(isset($_POST['reset']))
{
    unset($_SESSION['login_otp']);
    unset($_SESSION['login_email']);
    unset($_SESSION['logged_in']);
    unset($_SESSION['user']);
    unset($_SESSION['login_otp_time']);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Login</title>
    </head>
    <body>
        <h2>Login</h2>
        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" id="email" value="<?php echo $_SESSION['login_email'] ?? ($_POST['email'] ?? ''); ?>" required>
            <p style="color:red;" id="contactMsg"></p>
            <label>Password:</label>
            <div style="position: relative; display: inline-block;">
                <input type="password" name="password" id="pswd" value="<?php echo $_POST['password'] ?? ''; ?>">
                <span id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; user-select: none;">👁️</span>
            </div>
            <button type="submit" name="login" id="sendOtpBtn" disabled><?php echo isset($_SESSION['login_otp']) ? "Resend OTP" : "Send OTP"; ?></button>
            <button name="reset">Change Email</button>
            <p id="otpTimer"></p>
            <p id="resendTimer"></p>
            <a href="forgot_password.php">Forgot Password?</a><br><br>
            <label>Enter OTP:</label>
            <input type="text" name="otp" id="otp" disabled><br><br>
            <button type="submit" name="verify_login_otp" id="verifyBtn" disabled>Verify OTP</button>
            <p>New User? <a href="register.php">Register</a></p>
        </form>
        <a href="index.php">⬅ Back to Main Page</a>
        <footer>
            <p>© Gamma Coders | All Rights Reserved</p>
        </footer>
        <script>
            const emailInput = document.querySelector("input[name='email']");
            const passwordInput = document.getElementById("pswd");
            const sendOtpBtn = document.getElementById("sendOtpBtn");
            const otpInput = document.getElementById("otp");
            const verifyBtn = document.getElementById("verifyBtn");
            const msg = document.getElementById("contactMsg");
            const pswd = document.getElementById("pswd")
            let isOtpSent = <?php echo isset($_SESSION['login_otp']) ? 'true' : 'false'; ?>;
            let isEmailValid = false;
            const otpTimerEl = document.getElementById("otpTimer");
            const resendTimerEl = document.getElementById("resendTimer");
            let otpSentTime = <?php echo isset($_SESSION['login_otp_time']) ? $_SESSION['login_otp_time'] : 'null'; ?>;
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
            window.addEventListener("load", function () {
                if (isOtpSent)
                {
                    otpInput.disabled = false;
                    otpInput.focus();
                }
            });
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
                isEmailValid = true;
            });
            document.querySelector("form").addEventListener("input", function () {
                if (isEmailValid && passwordInput.value.length >= 6)
                    sendOtpBtn.disabled = false;
                else
                    sendOtpBtn.disabled = true;
            });
            otpInput.addEventListener("input", function () {
                if (/^[0-9]{6}$/.test(otpInput.value))
                    verifyBtn.disabled = false;
                else
                    verifyBtn.disabled = true;
            });
            if (isOtpSent)
            {
                emailInput.disabled = true;
                passwordInput.disabled = true;
            }
            window.addEventListener("load", function () {
                emailInput.dispatchEvent(new Event('input'));
            });
            const togglePassword = document.getElementById("togglePassword");
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
<?php
session_start();
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
$conn = mysqli_connect("localhost", "root", "", "lostNfound");
if(isset($_POST['send_otp']))
{
    $email = $_POST['email'] ?? $_SESSION['fp_email'] ?? '';
    if(empty($email))
    {
        echo "❌ Email missing";
        exit();
    }
    if(!preg_match('/^[a-zA-Z]+\d+@iiitmanipur\.ac\.in$/', $email))
    {
        die("❌ Invalid email format");
    }
    $local = explode('@', $email)[0];
    preg_match('/^([a-zA-Z]+)(\d+)$/', $local, $match);
    if(!$match)
        die("❌ Invalid format");
    $letters = $match[1];
    $numbers = $match[2];
    if(preg_match('/^(23|24)/', $numbers))
    {
        if(strlen($letters) != 4 || !preg_match('/^(23|24)010[1-4]\d{3}$/', $numbers))
        {
            die("❌ Invalid 23/24 format");
            exit();
        }
    }
    else if(preg_match('/^25/', $numbers))
    {
        if(!preg_match('/^25[1-6]\d{3}$/', $numbers))
        {
            die("❌ Invalid 25 format");
            exit();
        }
    }
    else
    {
        die("❌ Invalid starting digits");
        exit();
    }
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) == 0)
        echo "❌ Email not registered";
    else
    {
        if(isset($_SESSION['fp_otp_time']) && (time() - $_SESSION['fp_otp_time'] < 30))
        {
            echo "⏳ Please wait before requesting OTP again";
            exit();
        }
        $otp = rand(100000, 999999);
        $_SESSION['fp_otp'] = $otp;
        $_SESSION['fp_email'] = $email;
        $_SESSION['fp_otp_time'] = time();
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
        $mail->Subject = 'Password Reset OTP';
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
            echo "✅ OTP sent";
        }
        catch (Exception $e)
        {
            echo "❌ Mail error: " . $mail->ErrorInfo;
        }
    }
}
if(isset($_POST['verify_otp']))
{
    if(isset($_SESSION['fp_otp']) && isset($_SESSION['fp_otp_time']))
    {
        if(time() - $_SESSION['fp_otp_time'] > 300)
        {
            echo "❌ OTP expired. Please resend OTP.";
            unset($_SESSION['fp_otp']);
            unset($_SESSION['fp_otp_time']);
            exit();
        }
        if($_POST['otp'] == $_SESSION['fp_otp'])
        {
            $_SESSION['fp_verified'] = true;
            echo "✅ OTP Verified";
            unset($_SESSION['fp_otp']);
            unset($_SESSION['fp_otp_time']);
        }
        else
            echo "❌ Wrong OTP";
    }
}
if(isset($_POST['reset_password']))
{
    if(!isset($_SESSION['fp_verified']))
        echo "❌ Verify OTP first";
    else
    {
        $newPass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $email = $_SESSION['fp_email'];
        mysqli_query($conn, "UPDATE users SET password='$newPass' WHERE email='$email'");
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
if(isset($_POST['reset']))
{
    unset($_SESSION['fp_verified']);
    unset($_SESSION['fp_otp']);
    unset($_SESSION['fp_email']);
    unset($_SESSION['fp_otp_time']);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Forgot / Change Password</title>
    </head>
    <body>
        <h2>Forgot / Change Password</h2>
        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo $_SESSION['fp_email'] ?? ''; ?>" required>
            <button name="send_otp"><?php echo isset($_SESSION['fp_otp']) ? "Resend OTP" : "Send OTP"; ?></button>
            <button name="reset">Change Email</button>
            <p id="otpTimer"></p>
            <p id="resendTimer"></p>
            <p id="msg"></p>
            <label>Enter OTP:</label>
            <input type="text" name="otp" id="otp">
            <button name="verify_otp">Verify OTP</button>
            <p id="msg1"></p>
            <label>New Password:</label>
            <div style="position: relative; display: inline-block;">
                <input type="password" name="new_password" id="pswd" value="<?php echo $_POST['new_password'] ?? ''; ?>">
                <span id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; user-select: none;">👁️</span>
            </div>
            <button name="reset_password">Reset Password</button>
            <p id="msg2"></p>
        </form>
        <a href="index.php">⬅ Back to Main Page</a>
        <footer>
            <p>© Gamma Coders | All Rights Reserved</p>
        </footer>
        <script>
            const otpTimerEl = document.getElementById("otpTimer");
            const resendTimerEl = document.getElementById("resendTimer");
            const sendOtpBtn = document.querySelector("button[name='send_otp']");
            const verifyBtn = document.querySelector("button[name='verify_otp']");
            let otpSentTime = <?php echo isset($_SESSION['fp_otp_time']) ? $_SESSION['fp_otp_time'] : 'null'; ?>;
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
                    }
                    else
                    {
                        const m = Math.floor(otpRemaining / 60);
                        const s = otpRemaining % 60;
                        const time = String(m).padStart(2,'0') + ":" + String(s).padStart(2,'0');
                        otpTimerEl.innerText = "⏳ OTP expires in " + time;
                        otpTimerEl.style.color = otpRemaining < 60 ? "red" : "green";
                    }
                    let resendRemaining = 30 - (now - otpSentTime);
                    if (resendRemaining <= 0)
                    {
                        sendOtpBtn.disabled = false;
                        sendOtpBtn.innerText = "Resend OTP";
                        resendTimerEl.innerText = "✅ You can resend OTP now";
                        resendTimerEl.style.color = "green";
                    }
                    else
                    {
                        resendTimerEl.innerText = "⏳ Resend in " + resendRemaining + " sec";
                        resendTimerEl.style.color = "orange";
                    }
                }, 1000);
            }
            const emailInput = document.querySelector("input[name='email']");
            const otpInput = document.querySelector("input[name='otp']");
            const passwordInput = document.getElementById("pswd");
            const resetBtn = document.querySelector("button[name='reset_password']");
            let isEmailValid = false;
            let isOtpValid = false;
            const msg = document.getElementById("msg");
            const msg1 = document.getElementById("msg1");
            const msg2 = document.getElementById("msg2");
            let isVerified = <?php echo isset($_SESSION['fp_verified']) ? 'true' : 'false'; ?>;
            let isOtpSent = <?php echo isset($_SESSION['fp_otp']) ? 'true' : 'false'; ?>;
            window.addEventListener("load", () => {
                otpInput.disabled = !isOtpSent;
                passwordInput.disabled = !isVerified;
                verifyBtn.disabled = true;
                resetBtn.disabled = true;
                sendOtpBtn.disabled = true;
                if (isOtpSent)
                {
                    emailInput.disabled = true;
                    sendOtpBtn.disabled = true;
                }
            });
            emailInput.addEventListener("input", () => {
                const email = emailInput.value;
                if (!email.includes("@"))
                {
                    showMsg(msg, "❌ Missing @", "red");
                    isEmailValid = false;
                    sendOtpBtn.disabled = true;
                    return;
                }
                const [local, domain] = email.split("@");
                if (domain !== "iiitmanipur.ac.in")
                {
                    showMsg(msg, "❌ Only college email allowed", "red");
                    isEmailValid = false;
                    sendOtpBtn.disabled = true;
                    return;
                }
                const match = local.match(/^([a-zA-Z]+)(\d+)$/);
                if (!match)
                {
                    showMsg(msg, "❌ Invalid format", "red");
                    isEmailValid = false;
                    sendOtpBtn.disabled = true;
                    return;
                }
                const letters = match[1];
                const numbers = match[2];
                if (/^(23|24)/.test(numbers))
                {
                    if (letters.length !== 4 || !/^(23|24)010[1-4]\d{3}$/.test(numbers))
                    {
                        showMsg(msg, "❌ Invalid 23/24 format", "red");
                        isEmailValid = false;
                        sendOtpBtn.disabled = true;
                        return;
                    }
                }
                else if (/^25/.test(numbers))
                {
                    if (!/^25[1-6]\d{3}$/.test(numbers))
                    {
                        showMsg(msg, "❌ Invalid 25 format", "red");
                        isEmailValid = false;
                        sendOtpBtn.disabled = true;
                        return;
                    }
                }
                else
                {
                    showMsg(msg, "❌ Invalid starting digits", "red");
                    isEmailValid = false;
                    sendOtpBtn.disabled = true;
                    return;
                }
                showMsg(msg, "✅ Valid email", "green");
                isEmailValid = true;
                sendOtpBtn.disabled = false;
            });
            otpInput.addEventListener("input", () => {
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
            passwordInput.addEventListener("input", () => {
                const pass = passwordInput.value;
                otpInput.disabled=true;
                emailInput.disabled=true;
                if (pass.length < 6)
                {
                    showMsg(msg2, "❌ Password must be at least 6 characters", "red");
                    resetBtn.disabled = true;
                }
                else
                {
                    showMsg(msg2, "✅ Valid Password", "green");
                    if (isVerified)
                        resetBtn.disabled = false;
                }
            });
            if (isVerified)
            {
                otpInput.disabled = true;
                verifyBtn.disabled = true;
                passwordInput.disabled = false;
                showMsg(msg1, "✅ OTP verified. Set new password.", "green");
            }
            function showMsg(element, text, color)
            {
                element.innerText = text;
                element.style.color = color;
            }
            otpInput.addEventListener("keyup", () => {
                if (otpInput.value.length === 6)
                    verifyBtn.focus();
            });
            if (isOtpSent)
                emailInput.disabled = true;
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
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
$co=0;
$arr=array();
$t=false;
$conn = mysqli_connect("localhost", "root", "", "lostNfound");
$sql = "CREATE TABLE IF NOT EXISTS possible_match (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fname VARCHAR(100),
        lemail VARCHAR(100),
        femail VARCHAR(100),
        contact VARCHAR(15),
        item_type VARCHAR(100),
        confidence decimal(18,15),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        foreign key(contact) references users(contact) on update cascade on delete cascade
    )";
if (!mysqli_query($conn, $sql))
    echo "Error: " . mysqli_error($conn);
if($_SESSION['from']=="lost")
{
    $id=$_SESSION['cid'];
    $r = mysqli_query($conn, "SELECT * FROM lost_items WHERE ID = '$id'");
    if(mysqli_num_rows($r) == 0)
        echo "Lost Item Reported Not Properly";
    else if(mysqli_num_rows($r) == 1)
    {
        $ro = mysqli_fetch_assoc($r);
        $type=$ro['item_type'];
        $email=$ro['user_email'];
        $company=$ro['company'];
        $color=$ro['color'];
        $location=$ro['location'];
        $res = mysqli_query($conn, "SELECT * FROM found_items");
        if(mysqli_num_rows($res) == 0)
            echo "No Found Item Reported";
        else
        {
            while($row = mysqli_fetch_assoc($res))
            {
                $typ=$row['item_type'];
                $emai=$row['user_email'];
                $compan=$row['company'];
                $colo=$row['color'];
                $locatio=$row['location'];
                similar_text($type, $typ, $a);
                similar_text($company, $compan, $b);
                similar_text($color, $colo, $c);
                similar_text($location, $locatio, $d);
                $co=$a*0.25+$b*0.25+$c*0.25+$d*0.25;
                if($typ=="idcard")
                    $co=$b;
                echo $co."=".$a*0.25."+".$b*0.25."+".$c*0.25."+".$d*0.25."\n";
                if(($typ=="idcard" && $co==100) || $co>=65)
                {
                    echo "Match found";
                     $arr[$emai]=$co;
                    $t=true;
                }
            }
            if($t)
            {
                $maxValue = max($arr);
                $maxKey = array_keys($arr, $maxValue)[0];
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
                    $mail->addAddress($maxKey);
                    $mail->isHTML(true);
                    $mail->Subject = "Possible Match Found - Confidence '$maxValue'%";
                    $mail->Body = "
                        <h3>Founding Person Details are availiable on the Website. Please refresh the Landing Page</h3>
                        <hr>
                        <h2>Lost & Found System | Gamma Coders</h2>";
                    $mail->send();
                    $success = "✅ Message sent successfully!";
                    $rr = mysqli_query($conn, "SELECT * FROM users WHERE email = '$maxKey'");
                    if(mysqli_num_rows($rr) == 0)
                        echo "Lost Item Reported Not Properly";
                    else if(mysqli_num_rows($rr) == 1)
                    {
                        $roww = mysqli_fetch_assoc($rr);
                        $fname = $roww['name'];
                        $femail = $roww['email'];
                        $fcon = $roww['contact'];
                        $rr = mysqli_query($conn, "INSERT INTO possible_match (fname, lemail, femail, contact, item_type, confidence) VALUES ('$fname', '$maxKey', '$femail', '$fcon', '$typ', '$maxValue')");
                    }
                }
                catch (Exception $e)
                {
                    $error = "❌ Mail Error: " . $mail->ErrorInfo;
                }
            }
        }
    }
}
if($_SESSION['from']=="found")
{
    $id=$_SESSION['cid'];
    $r = mysqli_query($conn, "SELECT * FROM found_items WHERE ID = '$id'");
    if(mysqli_num_rows($r) == 0)
        echo "Found Item Reported Not Properly";
    else if(mysqli_num_rows($r) == 1)
    {
        $ro = mysqli_fetch_assoc($r);
        $type=$ro['item_type'];
        $email=$ro['user_email'];
        $company=$ro['company'];
        $color=$ro['color'];
        $location=$ro['location'];
        $res = mysqli_query($conn, "SELECT * FROM lost_items");
        if(mysqli_num_rows($res) == 0)
            echo "No Found Item Reported";
        else
        {
            while($row = mysqli_fetch_assoc($res))
            {
                $typ=$row['item_type'];
                $emai=$row['user_email'];
                $compan=$row['company'];
                $colo=$row['color'];
                $locatio=$row['location'];
                similar_text($type, $typ, $a);
                similar_text($company, $compan, $b);
                similar_text($color, $colo, $c);
                similar_text($location, $locatio, $d);
                $co=$a*0.25+$b*0.25+$c*0.25+$d*0.25;
                if($typ=="idcard")
                    $co=$b;
                echo $co."=".$a*0.25."+".$b*0.25."+".$c*0.25."+".$d*0.25."\n";
                if(($typ=="idcard" && $co==100) || $co>=65)
                {
                    echo "Match found";
                    $arr[$emai]=$co;
                    $t=true;
                }
            }
            if($t)
            {
                $maxValue = max($arr);
                $maxKey = array_keys($arr, $maxValue)[0];
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
                    $mail->addAddress($maxKey);
                    $mail->isHTML(true);
                    $mail->Subject = "Possible Match Found - Confidence '$maxValue'%";
                    $mail->Body = "
                        <h3>Founding Person Details are availiable on the Website. Please refresh the Landing Page</h3>
                        <hr>
                        <h2>Lost & Found System | Gamma Coders</h2>";
                    $mail->send();
                    $success = "✅ Message sent successfully!";
                    $rr = mysqli_query($conn, "SELECT * FROM users WHERE email = '$maxKey'");
                    if(mysqli_num_rows($rr) == 0)
                        echo "Lost Item Reported Not Properly";
                    else if(mysqli_num_rows($rr) == 1)
                    {
                        $roww = mysqli_fetch_assoc($rr);
                        $fname = $roww['name'];
                        $femail = $roww['email'];
                        $fcon = $roww['contact'];
                        $ror = mysqli_query($conn, "INSERT INTO possible_match (fname, lemail, femail, contact, item_type, confidence) VALUES ('$fname', '$maxKey', '$femail', '$fcon', '$typ', '$maxValue')");
                        if($ror)
                        {
                            echo "wowwwwwww";
                        }
                        else
                            echo "❌ Error: " . mysqli_error($conn);
                    }
                }
                catch (Exception $e)
                {
                    $error = "❌ Mail Error: " . $mail->ErrorInfo;
                }
            }
        }
    }
}
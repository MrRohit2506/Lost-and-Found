<?php
session_start();
if(!isset($_SESSION['logged_in']))
{
    header("Location: login.php");
    exit();
}
$conn = mysqli_connect("localhost", "root", "", "lostNfound");
$email = $_SESSION['user'];
$sql = "CREATE TABLE IF NOT EXISTS found_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100),
    item_type VARCHAR(100),
    company VARCHAR(100),
    color VARCHAR(50),
    location VARCHAR(150),
    found_date DATE,
    found_time TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!mysqli_query($conn, $sql))
    echo "❌ Table Error: " . mysqli_error($conn);
preg_match('/(\d{2})/', $email, $matches);
$yearPrefix = $matches[1];
$minYear = "20" . $yearPrefix . "-07-01";
if(isset($_POST['submit']))
{
    $item = $_POST['itemType'] === "others" ? $_POST['itemTypeOther'] : $_POST['itemType'];
    if($item === "idcard")
    {
        $company = $_POST['rollNumber'];
        if(!preg_match('/^(23|24)010[1-4]\d{3}$|^25010[1-6]\d{3}$/', $company))
        {
            echo "<p style='color:red;'>❌ Invalid Roll Number Format</p>";
            exit();
        }
    }
    else
        $company = $_POST['company'] === "others" ? $_POST['companyOther'] : $_POST['company'];
    $color = $_POST['color'] === "others" ? $_POST['colorOther'] : $_POST['color'];
    $location = $_POST['location'] === "others" ? $_POST['locationOther'] : $_POST['location'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $query = "INSERT INTO found_items (user_email, item_type, company, color, location, found_date, found_time) VALUES ('$email','$item','$company','$color','$location','$date','$time')";
    if(mysqli_query($conn, $query))
    {
        echo "<p style='color:green;'>✅ Item Reported Successfully</p>";
        $_SESSION['from']="found";
        $r = mysqli_query($conn, "SELECT MAX(id) FROM found_items");
        if(mysqli_num_rows($r) == 0)
            echo "Lost Item Reported Not Properly";
        else if(mysqli_num_rows($r) == 1)
        {
            $ro = mysqli_fetch_assoc($r);
            $_SESSION['cid']=$ro['MAX(id)'];
        }
        header("Location: server.php");
    }
    else
        echo "<p style='color:red;'>❌ Error: ".mysqli_error($conn)."</p>";
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Report Found Item</title>
    </head>
    <body>
        <h2>📌 Report Found Item</h2>
        <form method="POST">
            <label>Item Type</label>
            <select name="itemType" id="itemType" required>
                <option value="">Select Item</option>
                <option value="mobile">Mobile Phone</option>
                <option value="laptop">Laptop</option>
                <option value="watch">Watch</option>
                <option value="wallet">Wallet</option>
                <option value="idcard">ID Card</option>
                <option value="bag">Bag</option>
                <option value="bottle">Bottle</option>
                <option value="specs">Spectacles</option>
                <option value="notebook">Notebook</option>
                <option value="umbrella">Umbrella</option>
                <option value="others">Others</option>
            </select><br><br>
            <input type="text" name="itemTypeOther" id="itemTypeOther" placeholder="Enter item" style="display:none;">
            <label id="companyLabel">Company</label>
            <select name="company" id="company" required>
                <option value="">Select</option>
            </select><br><br>
            <input type="text" name="companyOther" id="companyOther" placeholder="Enter company" style="display:none;">
            <input type="text" name="rollNumber" id="rollNumber" placeholder="Enter Roll Number" style="display:none;">
            <p id="msg"></p>
            <label>Color</label>
            <select name="color" id="color" required>
                <option value="">Select Color</option>
                <option>Black</option>
                <option>White</option>
                <option>Blue</option>
                <option>Red</option>
                <option>Green</option>
                <option>Silver</option>
                <option>Grey</option>
                <option>Brown</option>
                <option>Pink</option>
                <option>Yellow</option>
                <option>Purple</option>
                <option value="others">Others</option>
            </select><br><br>
            <input type="text" name="colorOther" id="colorOther" style="display:none;">
            <label>Location</label>
            <select name="location" id="location" required>
                <option value="">Select</option>
                <option>Near Auditorium</option>
                <option>Playground</option>
                <option>New Classroom Block</option>
                <option>Old Classroom Block</option>
                <option>Academic Block</option>
                <option>Faculty Cabin (Academic Block)</option>
                <option>Faculty Block 1</option>
                <option>Faculty Block 2</option>
                <option>HOD Block</option>
                <option>Room No 4</option>
                <option>Room No 5</option>
                <option>Room No 6</option>
                <option>Room No 7</option>
                <option>Room No 8</option>
                <option>Room No 9</option>
                <option>Room No 10</option>
                <option>Room No 11</option>
                <option>Room No 12</option>
                <option value="others">Others</option>
            </select><br><br>
            <input type="text" name="locationOther" id="locationOther" style="display:none;">
            <label>Date</label>
            <input type="date" name="date" min="<?php echo $minYear; ?>" required><br><br>
            <label>Time</label>
            <input type="time" required name="time">
            <br><br>
            <input type="checkbox" id="confirmCheck" required> I confirm this is genuine
            <br><br>
            <button type="submit" name="submit">Submit</button>
        </form>
        <a href="index.php">⬅ Back to Main Page</a>
        <footer>
            <p>© Gamma Coders | All Rights Reserved</p>
        </footer>
        <script>
            const itemType = document.getElementById("itemType");
            const company = document.getElementById("company");
            const companyOther = document.getElementById("companyOther");
            const rollNumber = document.getElementById("rollNumber");
            const companyLabel = document.getElementById("companyLabel");
            const msg = document.getElementById("msg");
            const dateInput = document.querySelector("input[type='date']");
            const timeInput = document.querySelector("input[type='time']");
            const submitBtn = document.querySelector("button[type='submit']");
            const checkbox = document.getElementById("confirmCheck");
            submitBtn.disabled = true;
            function handleOther(selectId, inputId)
            {
                const select = document.getElementById(selectId);
                const input = document.getElementById(inputId);
                select.addEventListener("change", () => {
                    input.style.display = select.value === "others" ? "block" : "none";
                });
            }
            handleOther("itemType", "itemTypeOther");
            handleOther("company", "companyOther");
            handleOther("color", "colorOther");
            handleOther("location", "locationOther");
            const companyOptions = {
                mobile: ["Apple", "Samsung", "MI", "Realme", "OnePlus", "Oppo", "Vivo"],
                laptop: ["Dell", "HP", "Lenovo", "Apple", "Asus", "Acer", "MSI"],
                watch: ["Boat", "Fastrack", "Noise", "Titan", "Apple", "Samsung"],
                bag: ["Wildcraft", "Skybags", "Puma", "Adidas", "Nike"],
                bottle: ["Milton", "Cello", "Tupperware"],
                umbrella: ["Puma", "Nike", "Reebok", "Local"]
            };
            itemType.addEventListener("change", () => {
                const type = itemType.value;
                company.innerHTML = '<option value="">Select</option>';
                msg.innerText = "";
                if(type === "idcard")
                {
                    company.style.display = "none";
                    companyOther.style.display = "none";
                    rollNumber.style.display = "block";
                    rollNumber.required = true;
                    company.required = false;
                    companyLabel.innerText = "Roll Number";
                    validateForm();
                    return;
                }
                rollNumber.style.display = "none";
                rollNumber.required = false;
                company.style.display = "block";
                company.required = true;
                companyLabel.innerText = "Company";
                if(companyOptions[type])
                {
                    companyOptions[type].forEach(c => {
                        let option = document.createElement("option");
                        option.value = c;
                        option.textContent = c;
                        company.appendChild(option);
                    });
                }
                let otherOption = document.createElement("option");
                otherOption.value = "others";
                otherOption.textContent = "Others";
                company.appendChild(otherOption);
                validateForm();
            });
            rollNumber.addEventListener("input", () => {
                const roll = rollNumber.value;
                const regex = /^(23|24)010[1-4]\d{3}$|^25010[1-6]\d{3}$/;
                if(roll.length === 0)
                {
                    msg.innerText = "";
                    validateForm();
                    return;
                }
                if(regex.test(roll))
                {
                    msg.innerText = "✅ Valid Roll Number";
                    msg.style.color = "green";
                }
                else
                {
                    msg.innerText = "❌ Invalid Roll Number";
                    msg.style.color = "red";
                }
                validateForm();
            });
            const today = new Date().toISOString().split("T")[0];
            dateInput.max = today;
            dateInput.addEventListener("change", () => {
                const selectedDate = dateInput.value;
                const today = new Date().toISOString().split("T")[0];
                if(selectedDate === today)
                {
                    const now = new Date();
                    const currentTime = now.toTimeString().slice(0,5);
                    timeInput.max = currentTime;
                }
                else
                    timeInput.removeAttribute("max");
                validateForm();
            });
            function validateForm()
            {
                let valid = true;
                if(!itemType.value)
                    valid = false;
                if(itemType.value === "idcard")
                {
                    const regex = /^(23|24)010[1-4]\d{3}$|^25010[1-6]\d{3}$/;
                    if(!regex.test(rollNumber.value))
                        valid = false;
                }
                else
                {
                    if(!company.value)
                        valid = false;
                    if(company.value === "others" && !companyOther.value.trim())
                        valid = false;
                }
                const color = document.getElementById("color");
                const colorOther = document.getElementById("colorOther");
                if(!color.value)
                    valid = false;
                if(color.value === "others" && !colorOther.value.trim())
                    valid = false;
                const location = document.getElementById("location");
                const locationOther = document.getElementById("locationOther");
                if(!location.value)
                    valid = false;
                if(location.value === "others" && !locationOther.value.trim())
                    valid = false;
                if(!dateInput.value || !timeInput.value)
                    valid = false;
                if(!checkbox.checked)
                    valid = false;
                submitBtn.disabled = !valid;
                submitBtn.style.opacity = valid ? "1" : "0.6";
                submitBtn.style.cursor = valid ? "pointer" : "not-allowed";
            }
            document.querySelectorAll("input, select").forEach(el => {
                el.addEventListener("input", validateForm);
                el.addEventListener("change", validateForm);
            });
            document.querySelector("form").addEventListener("submit", function(e)
            {
                if(submitBtn.disabled)
                    e.preventDefault();
            });
        </script>
    </body>
</html>
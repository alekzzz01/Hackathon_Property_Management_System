<?php
require '../session/db.php'; 

session_start();

if (!isset($_SESSION['user_id'])) {
    
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$now = date('Y-m-d\TH:i');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the form
    $unitNo = $_POST['unitNo'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $paidAmount = floatval($_POST['paidAmount']);


    // Calculate amount payable
    $pricePerHour = 0; // Initialize to hold hourly rate

    // Get the price per hour for the selected unit
    $sql = "SELECT PricePerHour FROM roomunittable WHERE UnitNo = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $unitNo);
    $stmt->execute();
    $stmt->bind_result($pricePerHour);
    $stmt->fetch();
    $stmt->close();

    // Calculate the duration in hours
    $checkinDate = new DateTime($checkin);
    $checkoutDate = new DateTime($checkout);
    $duration = $checkoutDate->diff($checkinDate)->h + ($checkoutDate->diff($checkinDate)->days * 24); // Total hours

    // Calculate total amount
    $amountPayable = $duration > 0 ? $duration * $pricePerHour : 0;

    // Determine payment status
    $paymentStatus = ($amountPayable <= $paidAmount) ? 'paid' : 'partially paid';

    // Insert booking data into the booking table
    $insertSql = "INSERT INTO bookingtable (UnitNo, CheckIn, CheckOut, Name, ContactInfo, amount_payable, amount_paid, PaymentStatus, AdminIdNo) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $insertStmt = $connection->prepare($insertSql);
    $insertStmt->bind_param("ssssddssi", $unitNo, $checkin, $checkout, $name, $contact, $amountPayable, $paidAmount, $paymentStatus, $user_id);

    if ($insertStmt->execute()) {
        // Booking successful
        header("Location: dashboard.php?success=Booking confirmed!"); // Redirect back to dashboard or any other page
        exit();
    } else {
        // Booking failed
        $error = "Error: " . $insertStmt->error;
    }
    $insertStmt->close();
}

// Display the existing room units and booking form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Units</title>
    <html data-theme="light"></html>

    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <style>@import url(https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.3.45/css/materialdesignicons.min.css);</style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="bg-base-200 px-5 py-5">

        <div class="flex justify-between">
                <h1 class="text-lg font-medium">Booking</h1>
                <div class="breadcrumbs text-sm">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li>Booking</li>
                </ul>
                </div>
        </div>

        <div class="min-h-screen mt-5">

            <div class="w-full">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-7">
                    <?php
                   require '../session/db.php';

                    if ($connection->connect_error) {
                        die("Connection failed: " . $connection->connect_error);
                    }

                    $sql = "SELECT roomunittable.UnitNo, roomunittable.UnitType, roomunittable.PricePerHour, roomunittable.Description, roomunittable.Image_Urls, bookingtable.AdminIdNo 
                    FROM roomunittable 
                    LEFT JOIN bookingtable ON roomunittable.UnitNo = bookingtable.UnitNo";
                    $result = $connection->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {

                            $imageUrls = explode(',', $row['Image_Urls']); 
                            $firstImageUrl = trim($imageUrls[0]); 


                            echo '
                            <div class="w-full flex justify-center">
                                <div class="card bg-base-100 shadow-lg rounded-lg overflow-hidden w-full max-w-sm">
                                    <figure>
                                        <img src="' . htmlspecialchars($firstImageUrl) . '" alt="' . htmlspecialchars($row['UnitType']) . '" class="rounded-t-lg w-full h-60"/>
                                    </figure>
                                    <div class="card-body p-4">
                                        <h2 class="card-title text-lg font-semibold">' . htmlspecialchars($row['UnitType']) . ' - ' . htmlspecialchars($row['UnitNo']) . '</h2>
                                        <p class="text-gray-700">' . htmlspecialchars($row['Description']) . '</p>
                                        <div class="card-actions">
                                            <p class="font-bold mt-3">Price per Hour: $' . htmlspecialchars($row['PricePerHour']) . '</p>
                                            <label for="bookingModal-' . htmlspecialchars($row['UnitNo']) . '" class="mt-3 btn btn-primary cursor-pointer w-full">Book Now</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="checkbox" id="bookingModal-' . htmlspecialchars($row['UnitNo']) . '" class="modal-toggle">
                            <div class="modal modal-center">
                                <div class="modal-box max-w-lg">
                                    <h2 class="font-bold text-lg mb-4">Book Room: ' . htmlspecialchars($row['UnitType']) . '</h2>
                                    <form method="POST" action="">
                                        <input type="hidden" name="unitNo" value="' . htmlspecialchars($row['UnitNo']) . '">
                                        <input type="hidden" name="unitType" value="' . htmlspecialchars($row['UnitType']) . '">
                                        <input type="hidden" name="adminID" value="' . htmlspecialchars($row['AdminIdNo']) . '"> <!-- Admin ID field -->
                                        
                                        <p class="font-bold">Price per Hour: $<span id="pricePerHour-' . htmlspecialchars($row['UnitNo']) . '">' . htmlspecialchars($row['PricePerHour']) . '</span></p>
                                        
                                        <div class="mb-4">
                                            <label for="name" class="block mb-2">Your Name:</label>
                                            <input type="text" name="name" required class="border border-gray-300 p-2 rounded w-full" placeholder="Enter your name">
                                        </div>
                            
                                        <div class="mb-4">
                                            <label for="contact" class="block mb-2">Contact Number:</label>
                                            <input type="text" name="contact" required class="border border-gray-300 p-2 rounded w-full" placeholder="Contact No.">
                                        </div>
                            
                                        <div class="mb-4">
                                            <label for="checkin" class="block mb-2">Check In:</label>
                                            <input type="datetime-local" id="checkin-' . htmlspecialchars($row['UnitNo']) . '" name="checkin"  required class="border border-gray-300 p-2 rounded w-full"  onchange="calculateTotal(\'' . htmlspecialchars($row['UnitNo']) . '\')">
                                        </div>
                            
                                        <div class="mb-4">
                                            <label for="checkout" class="block mb-2">Check Out:</label>
                                            <input type="datetime-local" id="checkout-' . htmlspecialchars($row['UnitNo']) . '" name="checkout" required class="border border-gray-300 p-2 rounded w-full" onchange="calculateTotal(\'' . htmlspecialchars($row['UnitNo']) . '\')">
                                        </div>
                            
                                        <p class="font-bold mb-2">Amount Payable: $<span id="totalAmount-' . htmlspecialchars($row['UnitNo']) . '">0.00</span></p>
                            
                                        <div class="mb-4">
                                            <label for="paidAmount" class="block mb-2">Amount paid:</label>
                                            <input type="text" name="paidAmount" required class="border border-gray-300 p-2 rounded w-full" placeholder="Enter amount paid">
                                        </div>
                            
                                        <div class="modal-action">
                                            <label for="bookingModal-' . htmlspecialchars($row['UnitNo']) . '" class="btn">Cancel</label>
                                            <button type="submit" class="btn btn-primary">Confirm Booking</button>
                                        </div>
                                    </form>
                                </div>
                            </div>';
                        }
                    } else {
                        echo '<p class="text-gray-500">No room units available.</p>';
                    }

                    $connection->close();
                    ?>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="text-red-500 text-sm mt-8"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
        </div>
    </div>

    <script>
        function calculateTotal(unitNo) {
            const checkin = document.getElementById(`checkin-${unitNo}`).value;
            const checkout = document.getElementById(`checkout-${unitNo}`).value;
            const pricePerHour = parseFloat(document.getElementById(`pricePerHour-${unitNo}`).innerText);

            if (checkin && checkout) {
                const checkinDate = new Date(checkin);
                const checkoutDate = new Date(checkout);
                const duration = (checkoutDate - checkinDate) / (1000 * 60 * 60); // Convert milliseconds to hours
                
                // Calculate total amount
                const totalAmount = duration > 0 ? duration * pricePerHour : 0;

                // Update the total amount display
                document.getElementById(`totalAmount-${unitNo}`).innerText = totalAmount.toFixed(2); // Display with two decimal places
            } else {
                document.getElementById(`totalAmount-${unitNo}`).innerText = '0.00'; // Reset if inputs are empty
            }
        }
    </script>

    <script>
    // JavaScript to set the minimum date for Check In and Check Out to the current date and time
    document.addEventListener("DOMContentLoaded", function() {
        var checkInInputs = document.querySelectorAll("[id^='checkin-']");
        var checkOutInputs = document.querySelectorAll("[id^='checkout-']");
        
        var currentDateTime = new Date().toISOString().slice(0,16); // Get current date and time in 'YYYY-MM-DDTHH:MM' format

        checkInInputs.forEach(function(input) {
            input.setAttribute("min", currentDateTime); // Set min attribute to the current date and time
        });

        checkOutInputs.forEach(function(input) {
            input.setAttribute("min", currentDateTime); // Set min attribute to the current date and time
        });
    });
    </script>


</body>
</html>

<?php
require '../session/db.php'; 

session_start();

if (!isset($_SESSION['user_id'])) {
    
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

date_default_timezone_set('Asia/Manila');
$now = date('Y-m-d H:i:s');

$bookedUnits = [];

$sql = "SELECT UnitNo, CheckIn, CheckOut, checkout_today FROM bookingtable";
$result = $connection->query($sql);

if (!$result) {
    // Display error if the query fails
    echo "Error fetching data: " . $connection->error;
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
       
        $checkin = new DateTime($row['CheckIn']);
        $checkout = new DateTime($row['CheckOut']);
        $now = new DateTime();
        
     

        $checkin = new DateTime($row['CheckIn']);
        $checkout = new DateTime($row['CheckOut']);
        $now = new DateTime();

        // Check if the unit is booked based on the current date
        if ($now >= $checkin && $now <= $checkout && $row['checkout_today'] === 'no') {
            $bookedUnits[] = $row['UnitNo']; // Add to booked units only if checkout_today is 'no'
        }
    }

       
} else {
    echo "No bookings found."; // Debugging: In case no bookings are found
}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the form
    $unitNo = $_POST['unitNo'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $paidAmount = floatval($_POST['paidAmount']);



    $pricePerHour = 0;

    $sql = "SELECT PricePerHour FROM roomunittable WHERE UnitNo = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $unitNo);
    $stmt->execute();
    $stmt->bind_result($pricePerHour);
    $stmt->fetch();
    $stmt->close();

 
    $checkinDate = new DateTime($checkin);
    $checkoutDate = new DateTime($checkout);
    $duration = $checkoutDate->diff($checkinDate)->h + ($checkoutDate->diff($checkinDate)->days * 24); // Total hours

    $amountPayable = $duration > 0 ? $duration * $pricePerHour : 0;


    $paymentStatus = ($amountPayable <= $paidAmount) ? 'paid' : 'partially paid';

    // Calculate the remaining balance
    $balance = $amountPayable - $paidAmount;


    $insertSql = "INSERT INTO bookingtable (UnitNo, CheckIn, CheckOut, Name, ContactInfo, amount_payable, amount_paid, PaymentStatus, AdminIdNo, Balance) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $insertStmt = $connection->prepare($insertSql);
    $insertStmt->bind_param("ssssddssii", $unitNo, $checkin, $checkout, $name, $contact, $amountPayable, $paidAmount, $paymentStatus, $user_id,  $balance);

    if ($insertStmt->execute()) {
        $updateSql = "UPDATE roomunittable SET is_Booked = 1 WHERE UnitNo = ?";
        $updateStmt = $connection->prepare($updateSql);
        $updateStmt->bind_param("s", $unitNo);

        if ($updateStmt->execute()) {
            header("Location: booking.php?success=1");
            exit();
        } else {
           
            header("Location: booking.php?error=update_failed");
            exit();
        }

        $updateStmt->close();
    } else {
        // Booking failed
        $error = "Error: " . $insertStmt->error;
    }
    $insertStmt->close();
}

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

    
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div id="success-message" class="bg-green-100 text-green-500 text-sm p-3 rounded">Booking Confirmed.</div>
        <?php elseif (isset($_GET['error'])): ?>
            <div id="error-message" class="bg-red-100 text-red-500 text-sm p-3 rounded">
                <?php 
                    if ($_GET['error'] == 'update_failed') {
                        echo "Update Error: Failed to update room status.";
                    } elseif ($_GET['error'] == 'booking_failed') {
                        echo "Booking Error: Failed to create booking.";
                    }
                ?>
            </div>
        <?php endif; ?>


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
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-7">
                    <?php
                   require '../session/db.php';

                    if ($connection->connect_error) {
                        die("Connection failed: " . $connection->connect_error);
                    }

                    $result = $connection->query("SELECT * FROM roomunittable");

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {

                            $unitNo = $row['UnitNo'];
                            $isBooked = in_array($unitNo, $bookedUnits);

                        // debug for booked units
                            // echo '<pre>';
                            // print_r($bookedUnits);
                            // echo '</pre>';
                            
                    // current time check in time check out time
                            // echo "Now: " . $now->format('Y-m-d H:i:s') . "<br>";
                            // echo "CheckIn: " . $checkin->format('Y-m-d H:i:s') . "<br>";
                            // echo "CheckOut: " . $checkout->format('Y-m-d H:i:s') . "<br>";

                    // showing if a unit is booked or no
                            // echo '<pre>';
                            // echo 'Unit No: ' . $unitNo . ', Is Booked: ' . ($isBooked ? 'Yes' : 'No') . '<br>';
                            // echo '</pre>';


                      


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
                                        <p class="text-gray-700">Maximum pax allowed: ' . htmlspecialchars($row['Pax']) . '</p>
                                        <div class="card-actions">
                                            <p class="font-bold mt-3">Price per Hour: $' . htmlspecialchars($row['PricePerHour']) . '</p>
                                               <label for="bookingModal-' . $unitNo . '" 
                                                    class="mt-3 btn ' . ($isBooked ? 'btn-disabled' : 'btn-neutral') . ' cursor-pointer w-full" 
                                                    ' . ($isBooked ? 'disabled' : '') . '>
                                                    ' . ($isBooked ? 'Booked' : 'Book Now') . '
                                                </label>

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
                                            <button type="submit" class="btn btn-neutral">Confirm Booking</button>
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

<script>
        // Check if 'success' query parameter exists
        if (window.location.search.includes('success=1')) {
            // After displaying the message, remove 'success' parameter from the URL
            var url = new URL(window.location);
            url.searchParams.delete('success');
            window.history.replaceState({}, '', url);
        }
    </script>



</html>

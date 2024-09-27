<?php
require '../session/db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the form
    $unitNo = $_POST['unitNo'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $paidAmount = floatval($_POST['paidAmount']);
    $adminID = $_POST['adminID'];

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
    $insertStmt->bind_param("ssssddssi", $unitNo, $checkin, $checkout, $name, $contact, $amountPayable, $paidAmount, $paymentStatus, $adminID);

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
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-7">
                    <?php
                    if ($connection->connect_error) {
                        die("Connection failed: " . $connection->connect_error);
                    }

                    $sql = "SELECT roomunittable.UnitNo, roomunittable.UnitType, roomunittable.PricePerHour, roomunittable.Description, bookingtable.AdminIdNo 
                    FROM roomunittable 
                    LEFT JOIN bookingtable ON roomunittable.UnitNo = bookingtable.UnitNo";
                    $result = $connection->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '
                            <div class="w-full flex justify-center">
                                <div class="card bg-base-100 shadow-lg rounded-lg overflow-hidden w-full max-w-sm">
                                    <figure>
                                        <img src="https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.webp" alt="' . htmlspecialchars($row['UnitType']) . '" class="rounded-t-lg w-full"/>
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
                                            <input type="datetime-local" id="checkin-' . htmlspecialchars($row['UnitNo']) . '" name="checkin" required class="border border-gray-300 p-2 rounded w-full" onchange="calculateTotal(\'' . htmlspecialchars($row['UnitNo']) . '\')">
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
                        echo '<p class="text-gray-500">No available room units.</p>';
                    }
                    $connection->close();
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function calculateTotal(unitNo) {
            const checkin = new Date(document.getElementById('checkin-' + unitNo).value);
            const checkout = new Date(document.getElementById('checkout-' + unitNo).value);
            const pricePerHour = parseFloat(document.getElementById('pricePerHour-' + unitNo).innerText);

            if (!isNaN(checkin) && !isNaN(checkout) && checkin < checkout) {
                const duration = (checkout - checkin) / (1000 * 60 * 60); // Duration in hours
                const totalAmount = duration * pricePerHour;
                document.getElementById('totalAmount-' + unitNo).innerText = totalAmount.toFixed(2);
            } else {
                document.getElementById('totalAmount-' + unitNo).innerText = '0.00';
            }
        }
    </script>

</body>
</html>

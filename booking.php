<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Units</title>

    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <style>@import url(https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.3.45/css/materialdesignicons.min.css);</style>
</head>
<body>

    <?php include 'pages/navbar.php'; ?>

    <div class="bg-gray-50 font-[sans-serif]">
        <div class="min-h-screen flex flex-col items-center justify-center py-6 px-4">
            <div class="max-w-6xl w-full">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    
                    <?php
                    require './session/db.php';

                    if ($connection->connect_error) {
                        die("Connection failed: " . $connection->connect_error);
                    }

                    $sql = "SELECT UnitNo, UnitType, PricePerHour, Description FROM roomunittable";
                    $result = $connection->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Start a separate div for each card
                            echo '<div class="w-full flex justify-center">'; // New outer div to center the card
                            echo '    <div class="card bg-base-100 shadow-lg rounded-lg overflow-hidden w-full max-w-xs">'; // Card styling
                            echo '        <figure>';
                            echo '            <img src="https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.webp" alt="' . htmlspecialchars($row['UnitType']) . '" class="rounded-t-lg"/>';
                            echo '        </figure>';
                            echo '        <div class="card-body p-4">'; // Add padding to the card body
                            echo '            <h2 class="card-title text-lg font-semibold">' . htmlspecialchars($row['UnitType']) . ' - ' . htmlspecialchars($row['UnitNo']) . '</h2>';
                            echo '            <p class="text-gray-700">' . htmlspecialchars($row['Description']) . '</p>';
                            echo '            <p class="font-bold">Price per Hour: $' . htmlspecialchars($row['PricePerHour']) . '</p>';
                            echo '            <div class="card-actions justify-end">';

                            // Button to trigger the DaisyUI modal
                            echo '                <label for="bookingModal-' . htmlspecialchars($row['UnitNo']) . '" class="btn btn-primary cursor-pointer">Book Now</label>';
                            echo '            </div>';
                            echo '        </div>';
                            echo '    </div>';
                            echo '</div>'; 

                            // DaisyUI Modal
                            echo '<input type="checkbox" id="bookingModal-' . htmlspecialchars($row['UnitNo']) . '" class="modal-toggle">';
                            echo '<div class="modal">';
                            echo '    <div class="modal-box">';
                            echo '        <h2 class="font-bold text-lg mb-4">Book Room: ' . htmlspecialchars($row['UnitType']) . '</h2>';
                            echo '        <form id="bookingForm">';
                            echo '            <input type="hidden" name="unitNo" value="' . htmlspecialchars($row['UnitNo']) . '">';
                            echo '            <input type="hidden" name="unitType" value="' . htmlspecialchars($row['UnitType']) . '">';
                            echo '            <p class="font-bold">Price per Hour: $<span id="pricePerHour-' . htmlspecialchars($row['UnitNo']) . '">' . htmlspecialchars($row['PricePerHour']) . '</span></p>';  
                            echo '            <div class="mb-4">';
                            echo '                <label for="name" class="block mb-2">Your Name:</label>';
                            echo '                <input type="text" name="name" required class="border border-gray-300 p-2 rounded w-full" placeholder="Enter your name">';
                            echo '            </div>';
                            echo '            <div class="mb-4">';
                            echo '                <label for="contact" class="block mb-2">Contact Number:</label>';
                            echo '                <input type="text" name="contact" required class="border border-gray-300 p-2 rounded w-full" placeholder="Contact No.">';
                            echo '            </div>';
                            echo '            <div class="mb-4">';
                            echo '                <label for="checkin" class="block mb-2">Check In :</label>';
                            echo '                <input type="datetime-local" id="checkin-' . htmlspecialchars($row['UnitNo']) . '" name="checkin" required class="border border-gray-300 p-2 rounded w-full" onchange="calculateTotal(\'' . htmlspecialchars($row['UnitNo']) . '\')">';
                            echo '            </div>';
                            echo '            <div class="mb-4">';
                            echo '                <label for="checkout" class="block mb-2">Check Out :</label>';
                            echo '                <input type="datetime-local" id="checkout-' . htmlspecialchars($row['UnitNo']) . '" name="checkout" required class="border border-gray-300 p-2 rounded w-full" onchange="calculateTotal(\'' . htmlspecialchars($row['UnitNo']) . '\')">';
                            echo '            </div>';
                            echo '            <p class="font-bold">Total Amount: $<span id="totalAmount-' . htmlspecialchars($row['UnitNo']) . '">0.00</span></p>';
                            echo '            <div class="modal-action">';
                            echo '                <label for="bookingModal-' . htmlspecialchars($row['UnitNo']) . '" class="btn">Cancel</label>';
                            echo '                <button type="submit" class="btn btn-primary">Confirm Booking</button>';
                            echo '            </div>';
                            echo '        </form>';
                            echo '    </div>';
                            echo '</div>';
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

</body>
</html>

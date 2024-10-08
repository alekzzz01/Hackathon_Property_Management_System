<?php
require '../session/db.php';

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_id'])) {
    $checkoutId = $_POST['checkout_id'];
    $currentStatus = $_POST['current_status'];

    // Toggle the checkout status
    $newStatus = ($currentStatus === 'yes') ? 'no' : 'yes';

    // Prepare the statement to update checkout status
    $stmt = $connection->prepare("UPDATE bookingtable SET checkout_today = ? WHERE booking_id = ?");
    $stmt->bind_param("si", $newStatus, $checkoutId);

    // Execute the query
    if ($stmt->execute()) {
        // Get the UnitNo for the current booking
        $unitResult = $connection->query("SELECT UnitNo FROM bookingtable WHERE booking_id = $checkoutId");
        if ($unitResult->num_rows > 0) {
            $unitRow = $unitResult->fetch_assoc();
            $unitNo = $unitRow['UnitNo'];

            // Update the room's is_Booked status to 0 (available) if the guest checks out
            if ($newStatus === 'yes') {
                $updateRoomStatus = "UPDATE roomunittable SET is_Booked = 0 WHERE UnitNo = ?";
                $updateStmt = $connection->prepare($updateRoomStatus);
                $updateStmt->bind_param("s", $unitNo);

                if ($updateStmt->execute()) {
                    $success = "Guest checkout status and room availability updated successfully.";
                } else {
                    $error = "Error updating room status: " . $updateStmt->error;
                }

                $updateStmt->close();
            }
        } else {
            $error = "Error fetching unit information.";
        }
    } else {
        $error = $connection->error;
    }

    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkin_id'])) {
    $checkinId = $_POST['checkin_id'];
    $currentCheckinStatus = $_POST['current_checkin_status'];


    $newCheckinStatus = ($currentCheckinStatus === 'yes') ? 'no' : 'yes';


    $stmt = $connection->prepare("UPDATE bookingtable SET checkin_today = ? WHERE booking_id = ?");
    $stmt->bind_param("si", $newCheckinStatus, $checkinId);
    
    if ($stmt->execute()) {
        $success = "Guest check-in status updated successfully.";
    } else {
        $error = $connection->error;
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History</title>
    <html data-theme="light"></html>
   
    <link rel="stylesheet" type="text/css" href="styles.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <link href='https://unpkg.com/boxicons/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script defer>
        $(document).ready(function () {
            $('#myTable').DataTable({
                "lengthMenu": [10, 25, 50, 75, 100],
                "pageLength": 10,
                "pagingType": "full_numbers"
            });
        });
    </script>
</head>
<body class="h-screen flex flex-col">

    <?php include 'navbar.php' ?>

    <div class="h-full bg-base-200 px-5 py-5">
        <div class="flex justify-end">
            <div class="breadcrumbs text-sm">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li>Booking History</li>
                </ul>
            </div>
        </div>

        <div role="tablist" class="tabs tabs-lifted mt-7">
            <input type="radio" name="my_tabs_2" role="tab" class="tab" aria-label="Bookings" checked="checked" />
                                            
            <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6 overflow-x-scroll">
                <div class="table-container">
                    <table id="myTable" class="display">
                        <thead id="thead">
                            <tr>
                                <th>Booking ID</th>
                                <th>Unit no.</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Guest Name</th>
                                <th>Contact Info</th>
                                <th>Payment Status</th>
                                <th>Balance</th>
                                <th>Admin No.</th>
                                <th>Action</th> <!-- New column for the buttons -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $connection->query("SELECT * FROM bookingtable");

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($row['booking_id']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['UnitNo']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['CheckIn']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['CheckOut']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['Name']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['ContactInfo']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['PaymentStatus']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['Balance']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['AdminIdNo']) . '</td>';
                                    
                               
                                    $checkoutToday = htmlspecialchars($row['checkout_today']);
                                    $buttonTextCheckout = ($checkoutToday === 'yes') ? 'Mark as Not Checked Out' : 'Mark as Checked Out';
                                    $checkoutDisabled = ($checkoutToday === 'yes') ? 'disabled' : ''; // Disable button if already checked out

                                    // Add a button to toggle the checkin status
                                    $checkinToday = htmlspecialchars($row['checkin_today']);
                                    $buttonTextCheckin = ($checkinToday === 'yes') ? 'Mark as Not Checked In' : 'Mark as Checked In';
                                    $checkinDisabled = ($checkinToday === 'yes') ? 'disabled' : ''; // Disable button if already checked in

                                    echo '<td class="flex flex-wrap gap-2">

                                            <form method="POST" class="inline">
                                                <input type="hidden" name="checkin_id" value="' . htmlspecialchars($row['booking_id']) . '">
                                                <input type="hidden" name="current_checkin_status" value="' . $checkinToday . '">
                                                <button type="submit" class="btn btn-outline" ' . $checkinDisabled . '>' . $buttonTextCheckin . '</button>
                                            </form>

                                            <form method="POST" class="inline">
                                                <input type="hidden" name="checkout_id" value="' . htmlspecialchars($row['booking_id']) . '">
                                                <input type="hidden" name="current_status" value="' . $checkoutToday . '">
                                                <button type="submit" class="btn btn-neutral" ' . $checkoutDisabled . '>' . $buttonTextCheckout . '</button>
                                            </form>

                                        </td>';


                                    echo '</tr>';
                                }
                            } else {
                                echo "<h2>No items ordered found.</h2>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <?php if (isset($error)): ?>
                    <div class="bg-red-100 text-red-500 text-sm mt-8 p-3 rounded"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                        <div class="bg-green-100 text-green-500 text-sm mt-8 p-3 rounded"><?php echo $success; ?></div>
                <?php endif; ?>



            </div>
        </div>

<?php
$connection->close();
?>

</body>
</html>

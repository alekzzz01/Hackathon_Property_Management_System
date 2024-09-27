<?php
require '../session/db.php';

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Handle the button click to toggle the checkout status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_id'])) {
    $checkoutId = $_POST['checkout_id'];
    $currentStatus = $_POST['current_status'];

    // Determine the new status
    $newStatus = ($currentStatus === 'yes') ? 'no' : 'yes';

    // Update the database with the new status
    $stmt = $connection->prepare("UPDATE bookingtable SET checkout_today = ? WHERE booking_id = ?");
    $stmt->bind_param("si", $newStatus, $checkoutId);
    
    if ($stmt->execute()) {
        echo "Guest checkout status updated successfully.";
    } else {
        echo "Error updating record: " . $connection->error;
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <html data-theme="light"></html>
   
    <link  rel="stylesheet" type="text/css" href="styles.css" />
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

    <div class="h-screen bg-base-200 px-5 py-5">
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
                                            
            <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
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
                                <th>Admin No.</th>
                                <th>Action</th> <!-- New column for the button -->
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
                                    echo '<td>' . htmlspecialchars($row['AdminNo']) . '</td>';
                                    
                                    // Add a button to toggle the checkout status
                                    $checkoutToday = htmlspecialchars($row['checkout_today']);
                                    $buttonText = ($checkoutToday === 'yes') ? 'Mark as Not Checked Out' : 'Mark as Checked Out';

                                    echo '<td>
                                            <form method="POST">
                                                <input type="hidden" name="checkout_id" value="' . htmlspecialchars($row['booking_id']) . '">
                                                <input type="hidden" name="current_status" value="' . $checkoutToday . '">
                                                <button type="submit" class="btn btn-primary">' . $buttonText . '</button>
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
            </div>
        </div>

<?php
$connection->close();
?>

</body>
</html>

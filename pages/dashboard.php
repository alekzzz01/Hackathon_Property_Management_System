<?php
require '../session/db.php';

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Handle the button click to toggle the check-in status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkin_id'])) {
    $checkinId = $_POST['checkin_id'];
    $currentStatus = $_POST['current_status'];

    // Determine the new status
    $newStatus = ($currentStatus === 'yes') ? 'no' : 'yes';

    // Update the database with the new status
    $stmt = $connection->prepare("UPDATE bookingtable SET checkin_today = ? WHERE booking_id = ?");
    $stmt->bind_param("si", $newStatus, $checkinId);
    
    if ($stmt->execute()) {
        $success = "Guest check-in status updated successfully.";
    } else {
        $error =  $connection->error;
    }
    
    $stmt->close();
}

// Fetch the number of guests who checked in today
$checkinCountResult = $connection->query("SELECT COUNT(*) AS checkin_count FROM bookingtable WHERE checkin_today = 'yes'");
$checkinCount = 0;
if ($checkinCountResult->num_rows > 0) {
    $row = $checkinCountResult->fetch_assoc();
    $checkinCount = $row['checkin_count'];
}

// Fetch the number of guests who checked out today
$checkoutCountResult = $connection->query("SELECT COUNT(*) AS checkout_count FROM bookingtable WHERE checkout_today = 'yes'");
$checkoutCount = 0;
if ($checkoutCountResult->num_rows > 0) {
    $row = $checkoutCountResult->fetch_assoc();
    $checkoutCount = $row['checkout_count'];
}

// Fetch the number of available rooms
$sqlAvailable = "SELECT COUNT(*) AS roomunittable FROM roomunittable WHERE is_booked = 0";
$resultAvailable = $connection->query($sqlAvailable);
$availableRooms = 0;
if ($resultAvailable->num_rows > 0) {
    $rowAvailable = $resultAvailable->fetch_assoc();
    $availableRooms = $rowAvailable['roomunittable'];
}

// Fetch the number of booked rooms
$sqlBooked = "SELECT COUNT(*) AS booked_count FROM roomunittable WHERE is_booked = 1";
$resultBooked = $connection->query($sqlBooked);
$bookedRooms = 0;
if ($resultBooked->num_rows > 0) {
    $rowBooked = $resultBooked->fetch_assoc();
    $bookedRooms = $rowBooked['booked_count'];
}

$totalRooms = $availableRooms + $bookedRooms;

// Fetch the total number of guests per month
$guestsPerMonth = [];
for ($i = 1; $i <= 12; $i++) {
    $sqlGuests = "SELECT COUNT(*) AS guest_count FROM bookingtable WHERE MONTH(CheckIn) = $i";
    $resultGuests = $connection->query($sqlGuests);
    
    $guestCount = 0;
    if ($resultGuests->num_rows > 0) {
        $rowGuests = $resultGuests->fetch_assoc();
        $guestCount = $rowGuests['guest_count'];
    }
    $guestsPerMonth[] = $guestCount;
}

$totalRooms = $availableRooms + $bookedRooms;

$connection->close();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="styles.css" />
    <html data-theme="light"></html>
   
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <link href='https://unpkg.com/boxicons/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">

    <link rel="icon" href="../pages/unitImageView/images/logo.jpg" />
</head>
<body class="h-fit bg-base-200 flex flex-col">

    <?php include 'navbar.php' ?>

    <div class="h-full px-5 py-5">
        <div class="flex justify-between">
            <h1 class="text-lg font-medium">Dashboard</h1>
            <div class="breadcrumbs text-sm">
                <ul>
                    <li>Dashboard</li>
                </ul>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-7">

          <div class="flex flex-col gap-5 col-span-2 lg:col-span-1">

                <div class="rounded shadow bg-base-100">
                    <div class="card-body">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h1 class="text-4xl font-semibold mb-2"><?php echo $checkinCount; ?></h1>
                                <p>Check-In</p>
                            </div>
                            <button class="btn btn-square btn-sm">
                                <i class='bx bxs-check-square text-xl'></i>
                            </button>
                        </div>
                    </div>
                </div>

                
                <div class="rounded shadow bg-base-100">
                    <div class="card-body">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h1 class="text-4xl font-semibold mb-2"><?php echo $checkoutCount; ?></h1>
                                <p>Check-out</p>
                            </div>
                            <button class="btn btn-square btn-sm">
                                <i class='bx bxs-x-square text-xl'></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <div class="rounded shadow bg-base-100 col-span-2">
                <div class="card-body">
                    <div class="flex flex-col gap-12">

                        <div class="flex flex-col gap-3 lg:col-span-2">   
                            <p class="font-medium">Available Rooms: <?php echo $availableRooms; ?></p>
                            <div class="flex w-full h-4 bg-gray-200 rounded-full overflow-hidden dark:bg-neutral-700" role="progressbar" aria-valuenow="<?php echo ($availableRooms / $totalRooms) * 100; ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="flex flex-col justify-center rounded-full overflow-hidden bg-yellow-600 text-xs text-white text-center whitespace-nowrap dark:bg-yellow-500 transition duration-500" style="width: <?php echo ($availableRooms / $totalRooms) * 100; ?>%">
                                    <?php echo round(($availableRooms / $totalRooms) * 100); ?>%
                                </div>
                            </div>
                            <p class="font-medium text-gray-400">Total Rooms: <?php echo $totalRooms; ?></p>
                        </div>

                        <div class="flex flex-col gap-3 lg:col-span-2">   
                            <p class="font-medium">Booked Rooms: <?php echo $bookedRooms; ?></p>
                            <div class="flex w-full h-4 bg-gray-200 rounded-full overflow-hidden dark:bg-neutral-700" role="progressbar" aria-valuenow="<?php echo ($bookedRooms / $totalRooms) * 100; ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="flex flex-col justify-center rounded-full overflow-hidden bg-yellow-600 text-xs text-white text-center whitespace-nowrap dark:bg-yellow-500 transition duration-500" style="width: <?php echo ($bookedRooms / $totalRooms) * 100; ?>%">
                                    <?php echo round(($bookedRooms / $totalRooms) * 100); ?>%
                                </div>
                            </div>
                            <p class="font-medium text-gray-400">Total Rooms: <?php echo $totalRooms; ?></p>
                        </div>
                    </div>
                </div>
            </div>


            <div class="rounded shadow bg-base-100 col-span-1 lg:col-span-3">
                <div class="card-body">
                    <div id="chart"></div>  
                </div>
            </div>
        </div>

    </div>

</body>

<script>
       
       var options = {
        chart: {
            type: 'line',
            height: 350
        },
        series: [{
            name: 'Total Guests',
            data: <?php echo json_encode($guestsPerMonth); ?>
        }],
        colors: ['#F6D6A5'], 
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        },
        stroke: {
            curve: 'smooth'
        },
        title: {
            text: 'Reservation Analytics',
            align: 'center',
            style: {
                fontFamily: 'Inter',
                fontWeight: 'Bold'
            }
        },
        markers: {
            size: 4,
            colors: ['#FFA41B'],
            strokeColors: '#fff',
            strokeWidth: 2,
        },
        tooltip: {
            enabled: true,
            x: {
                format: 'MMM'
            }
        },
        theme: {
            fontFamily: 'Inter'
        }
    };

    // Create the chart
    var chart = new ApexCharts(document.querySelector("#chart"), options);

    // Render the chart
    chart.render();
</script>

</html>


<?php
require '../session/db.php';

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Fetch the number of guests who checked out today
$checkoutCountResult = $connection->query("SELECT COUNT(*) AS checkout_count FROM bookingtable WHERE checkout_today = 'yes'");

$checkoutCount = 0;
if ($checkoutCountResult->num_rows > 0) {
    $row = $checkoutCountResult->fetch_assoc();
    $checkoutCount = $row['checkout_count'];
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <html data-theme="light"></html>
    
    <link  rel="stylesheet" type="text/css" href="styles.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <link href='https://unpkg.com/boxicons/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="h-screen flex flex-col">

    <?php include 'navbar.php' ?>

    <div class="h-auto bg-base-200 px-5 py-5">
        <div class="flex justify-between">
            <h1 class="text-lg font-medium">Dashboard</h1>
            <div class="breadcrumbs text-sm">
                <ul>
                    <li>Dashboard</li>
                </ul>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-7">

            <div class="rounded shadow bg-base-100">
                <div class="card-body">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h1 class="text-4xl font-semibold mb-2">70</h1>
                            <p>New Bookings</p>
                        </div>
                        <button class="btn btn-square btn-sm">
                            <i class='bx bxs-book-alt text-xl'></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="rounded shadow bg-base-100">
                <div class="card-body">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h1 class="text-4xl font-semibold mb-2">70</h1>
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

            <div class="rounded shadow bg-base-100 col-span-1 lg:col-span-3">
                <div class="card-body">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 w-full">
                        <div class="flex flex-col gap-3">   
                            <p class="font-medium">Available Rooms: 5</p>
                            <div class="flex w-full h-4 bg-gray-200 rounded-full overflow-hidden dark:bg-neutral-700" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                <div class="flex flex-col justify-center rounded-full overflow-hidden bg-blue-600 text-xs text-white text-center whitespace-nowrap dark:bg-blue-500 transition duration-500" style="width: 25%">25%</div>
                            </div>
                            <p class="font-medium text-gray-400">Total Rooms: 20</p>
                        </div>

                        <div class="flex flex-col gap-3">   
                            <p class="font-medium">Booked Units: 5</p>
                            <div class="flex w-full h-4 bg-gray-200 rounded-full overflow-hidden dark:bg-neutral-700" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                <div class="flex flex-col justify-center rounded-full overflow-hidden bg-blue-600 text-xs text-white text-center whitespace-nowrap dark:bg-blue-500 transition duration-500" style="width: 100%">100%</div>
                            </div>
                            <p class="font-medium text-gray-400">Total Rooms: 20</p>
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

        <div>
        </div>

    </div>

</body>

<script>
    // Create the chart options
    var options = {
        chart: {
            type: 'line',
            height: 350
        },
        series: [{
            name: 'Sales',
            data: [10, 41, 35, 51, 49, 62, 69, 91, 148]
        }],
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep']
        },
        stroke: {
            curve: 'smooth'
        },
        title: {
            text: 'Reservation Statistics',
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

<?php
require '../session/db.php';


if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
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



    <link  rel="stylesheet" type="text/css" href="styles.css" />
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

            $('#myTable2').DataTable({
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
                    <li>Rooms</li>
                </ul>
                </div>
            </div>

           
            <div role="tablist" class="tabs tabs-lifted mt-7">
            <input type="radio" name="my_tabs_2" role="tab" class="tab" aria-label="All Unit and Rooms" checked="checked"/>
                                          
            <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
                                <p></p>
                            
                                <div class="table-container">
                                    <table id="myTable" class="display">
                                        <thead id="thead">
                                            <tr>
                                                <th>Unit No.</th>
                                                <th>Unit Type</th>
                                                <th>Price per Hour</th>
                                                <th>Description</th>
                                    
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                           $result = $connection->query("SELECT * FROM roomunittable");

                                            if ($result->num_rows > 0) {
                            
                                                while ($row = $result->fetch_assoc()) {
                                                    // Calculate subtotal
                                             
                                            
                                                    echo '<tr>';
                            
                                                    echo '<td>' . htmlspecialchars($row['UnitNo']) . '</td>';
                                                    echo '<td>' . htmlspecialchars($row['UnitType']) . '</td>';
                                                    echo '<td>' . htmlspecialchars($row['PricePerHour']) . '</td>';
                                                    echo '<td>' . htmlspecialchars($row['Description']) . '</td>';
                                                    echo '<td><a href="unitview.php?id=' . $row['UnitNo'] . '" class="btn">View</a></td>';
                                               
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

// Close the connection
$connection->close();
?>

</body>
</html>


<?php

require '../session/db.php';


if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}


if(isset($_GET['id'])) {
    $UnitNo = $_GET['id'];

    // Fetch property details from the database based on the property ID
    $sql = "SELECT * FROM roomunittable WHERE UnitNo = $UnitNo";
    $result = $connection->query($sql);


}





?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unit View</title>

    
    <html data-theme="light"></html>
    
    <link  rel="stylesheet" type="text/css" href="styles.css" />

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />

    <link href='https://unpkg.com/boxicons/css/boxicons.min.css' rel='stylesheet'>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">




</head>
<body class="h-screen flex flex-col ">


        <?php include 'navbar.php' ?>

        <div class="h-full bg-base-200 px-5 py-5 flex flex-col items-center">
                <div class="max-w-screen-2xl">

                        <?php 
                            
                        if ($result->num_rows > 0) {
                            $unit = $result->fetch_assoc();

                            $imageUrls = explode(',', $unit['Image_Urls']);
                           

                        }

                        ?>



                        <div>
                            <a href="rooms.php" class="btn btn-neutral"><i class='bx bx-arrow-back'></i></a>
                        </div>

                      

                        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5 mt-7">
                                <?php 
                                    if (!empty($imageUrls) && count(array_filter($imageUrls)) > 0) {
                                        echo '<img class="rounded-lg h-full col-span-1 lg:col-span-2 lg:row-span-2" src="' . trim($imageUrls[0]) . '" alt="">';
                                        
                                        for ($i = 1; $i < count($imageUrls); $i++) {
                                            if (!empty(trim($imageUrls[$i]))) { // Ensure no empty URLs
                                                echo '<img class="rounded-lg h-full" src="' . trim($imageUrls[$i]) . '" alt="">';
                                            }
                                        }
                                    } else {
                                        // Fallback image
                                        echo '<img class="rounded-lg col-span-4 bg-red-500" src="https://care-to.southernleytestateu.edu.ph/wp-content/uploads/2022/06/NoImageFound.jpg.png" alt="No Image Found">';
                                    }
                                ?>
                        </div>

                

                        <div class="mt-7 w-50">
                            <div role="tablist" class="tabs tabs-bordered">
                            <input type="radio" name="my_tabs_1" role="tab" class="tab" aria-label="Unit"   checked="checked" />
                            <div role="tabpanel" class="tab-content p-5"> 
                                <h1 class="text-lg font-bold"><?php  echo $unit ['UnitType']?></h1>
                                <p class="font-regular text-gray-400">Unit No. <?php  echo $unit ['UnitNo']?></p>
                            </div>

                            <input type="radio" name="my_tabs_1" role="tab" class="tab" aria-label="Description"   />
                            <div role="tabpanel" class="tab-content p-5"><p class="font-medium"><?php  echo $unit ['Description']?></p></div>

                            <input type="radio" name="my_tabs_1" role="tab" class="tab" aria-label="Price" />
                            <div role="tabpanel" class="tab-content p-5"><p class="font-medium">Price per hour: <?php echo $unit ['PricePerHour'] ?></p></div>

                            <input type="radio" name="my_tabs_1" role="tab" class="tab" aria-label="360 View" />
                            <div role="tabpanel" class="tab-content p-5"><p class="font-medium"><p class="font-medium"><a class="btn" href="./unitImageView/unit360view.php?id=<?php echo $unit['UnitNo']; ?>">Click to view the 360 image of the Room</a></p>
                            </div>



                            </div>
                        </div>


                </div>




        </div>
    
</body>
</html>
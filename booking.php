<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Units</title>

    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <style>@import url(https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.3.45/css/materialdesignicons.min.css);</style>
</head>
<body>

    <?php include 'pages/navbar.php'; ?>

    <div class="bg-gray-50 font-[sans-serif]">
        <div class="min-h-screen flex flex-col items-center justify-center py-6 px-4" x-data="app()">
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
                            echo '<div class="card bg-base-100 w-full shadow-xl">';
                            echo '    <figure>';
                            echo '        <img src="https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.webp" alt="' . htmlspecialchars($row['UnitType']) . '" />';
                            echo '    </figure>';
                            echo '    <div class="card-body">';
                            echo '        <h2 class="card-title">' . htmlspecialchars($row['UnitType']) . ' - ' . htmlspecialchars($row['UnitNo']) . '</h2>';
                            echo '        <p>' . htmlspecialchars($row['Description']) . '</p>';
                            echo '        <p class="font-bold">Price per Hour: $' . htmlspecialchars($row['PricePerHour']) . '</p>';
                            echo '        <div class="card-actions justify-end">';
                            echo '            <button class="btn btn-primary">Book Now</button>';
                            echo '        </div>';
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

    
    
</body>
</html>

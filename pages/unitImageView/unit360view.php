
<?php

require '../../session/db.php';


    $result = null; 

    if (isset($_GET['id'])) {
        $UnitNo = $_GET['id'];

        $sql = "SELECT * FROM roomunittable WHERE UnitNo = $UnitNo";
        $result = $connection->query($sql); 
    }


    if ($result && $result->num_rows > 0) {
        $unit = $result->fetch_assoc();
        $panoramaImageUrl = $unit['360_Image_Url']; 
    } else {
        $panoramaImageUrl = 'images/image1.jpeg'; 
    }




?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
    <link rel="stylesheet" href="style.css" />

    
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />

    <link href='https://unpkg.com/boxicons/css/boxicons.min.css' rel='stylesheet'>





  </head>
  <body>
    <div class="main-container">

      <div class="back">
        <a href="../unitview.php?id=<?php echo $UnitNo; ?>" class="btn btn-neutral">
            <i class='bx bx-arrow-back'></i>
        </a>
    </div>



      <h1>Hi, Welcome</h1>  

      <div class="image-container"></div>
     </div>




    <script
      src="https://cdnjs.cloudflare.com/ajax/libs/three.js/105/three.min.js"
      integrity="sha512-uWKImujbh9CwNa8Eey5s8vlHDB4o1HhrVszkympkm5ciYTnUEQv3t4QHU02CUqPtdKTg62FsHo12x63q6u0wmg=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"></script>
    <script src="js/panolens.min.js"></script>

    <script>
          const panoramaImageUrl = "<?php echo $panoramaImageUrl; ?>";
          const panoramaImage = new PANOLENS.ImagePanorama(panoramaImageUrl);
        // const panoramaImage = new PANOLENS.ImagePanorama("images/image1.jpeg");
        const imageContainer = document.querySelector(".image-container");

        const viewer = new PANOLENS.Viewer({
            container: imageContainer,
            autoRotate: true,
            autoRotateSpeed: 0.3,
            controlBar: true, 
            controlButtons: [
                'zoom-in',
                'zoom-out',
                'fullscreen',
                'reset' 
            ],
            mouseControl: true,
            keyboardControl: true
        });

        viewer.add(panoramaImage);
    </script>
  </body>
</html>
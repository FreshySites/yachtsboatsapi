<?php 

get_header();



$key      = get_option('boats_api_key');

$api_url    = get_option('boats_api_key_url');

$plugin_chk   = get_option('Activated_BoatsAPI');



if($key && $api_url && $plugin_chk){



global $wpdb;

$boat_id=$_GET['boat_id'];

$images = $wpdb->prefix.'images';

$videos = $wpdb->prefix.'videos';

$boats = $wpdb->prefix.'boats';

$engines = $wpdb->prefix.'engines';

$agents = $wpdb->prefix.'agents';

$boat_hull = $wpdb->prefix.'boat_hull_materials';

$rs_images_db_arr = $wpdb->get_results("SELECT * FROM $images Where boatid = $boat_id");

$rs_images = json_decode(json_encode($rs_images_db_arr), true);



$rs_videos_db_arr = $wpdb->get_results("SELECT * FROM $videos Where boatid = $boat_id");

$rs_videos = json_decode(json_encode($rs_videos_db_arr), true);



$boat_db_arr = $wpdb->get_results("SELECT * FROM $boats Where id = $boat_id");

$rs_boat = json_decode(json_encode($boat_db_arr), true);



$hullid = $rs_boat[0]['hullid'];

$hull_title = $wpdb->get_results("SELECT title FROM $boat_hull Where code = '$hullid'");

$hul = json_decode(json_encode($hull_title), true);

$hull = $hul[0]['title'];



$rs_engines_db_arr = $wpdb->get_results("SELECT * FROM $engines Where boatid = $boat_id");

$rs_engines = json_decode(json_encode($rs_engines_db_arr), true);


$agentId = $rs_boat[0]['agentid'];

$rs_agent_db_arr = $wpdb->get_results("SELECT * FROM $agents Where partid = $agentId");

$rs_agent = json_decode(json_encode($rs_agent_db_arr), true);
$rs_agent = $rs_agent[0];



if($rs_engines[0]['fuel']){

    $fuel = $rs_engines[0]['fuel'];

}else{

    $fuel = 'N/A';

}



$count = 1;

//extract youtube video id
if(count($rs_videos) > 0){
  if(str_contains($rs_videos[0]['url'], 'v=')) {
    $embed = explode ("v=",$rs_videos[0]['url']); //previous logic
    $videoId = $embed[1];
  }
  else { //by SV
    $pattern = '#https?://youtu\.be/([a-zA-Z0-9_-]+)#';
    if (preg_match($pattern, $rs_videos[0]['url'], $matches)) {
        $videoId = $matches[1];
    }
  }
}




?>

<div class="listing-main-detail">

  <div class="row">

    <div class="container">

      <div class="exclise-listing-detail-main">

        <!-- Tabs content -->

        <div class="col-lg-8">

          <ul class="nav nav-tabs">

          <li class="active"><a href="#tab1default" data-toggle="tab">Photos</a></li>

          <?php
          if( isset($videoId) && !empty($videoId) )
             echo '<li><a href="#tab2default" data-toggle="tab">Video</a></li>';
          ?>

         </ul>

              <div class="tab-content">

                <div class="clearfix tab-pane fade in active" id="tab1default" style="margin-bottom: 35px;">

                  <div class="royalSlider rsDefault">
                      
                    <?php 
                    
                        $pdf_image = "";
                    
                        $i = 1;
                    
                    ?>

                   <?php foreach ($rs_images as $key => $image){ ?>

                    <div id="divToPrint2">

                      <a class="rsImg" href="<?php echo $image['url']?>" >

                      <img src="<?php echo $image['url']?>" alt="" style="width:100%;" class="rsTmb">

                      </a>

                    </div>
                    
                    <?php if ($i == 1) { $pdf_image = $image['url']; } ?>
                    
                    <?php 
                        $i++;
                    ?>

                   <?php } ?>

                 </div>

               </div>

              <div class="tab-pane fade" id="tab2default">
              <?php
                if( isset($videoId) && !empty($videoId) )
                    echo '<iframe width="640" height="360" src="https://www.youtube.com/embed/'.$videoId.'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
              ?>

            </div>

          </div>

        <br><br>

        <ul class="nav nav-tabs" >

         <li class="active" id="divToPrint3"><a data-toggle="tab" href="#about">Basic Description</a></li>

         <li id="divToPrint4"><a data-toggle="tab" href="#fullspec">Full Specs</a></li>

        </ul>

        <div class="tab-content">

          <div id="about" class="tab-pane fade in active">

            <?php if($rs_boat[0]['listingtitle']!=""){ ?>

              <br/>

              <p class="details-info-tag"><?php echo $rs_boat[0]['listingtitle']; ?></p>

            <?php } ?>

            <br/>

            <ul class="fa-ul">

              <li><i class="fa fa-li fa-caret-right" aria-hidden="true"></i>Year: <?php echo $rs_boat[0]['year'] ?></li>

              <li><i class="fa fa-li fa-caret-right" aria-hidden="true"></i>Length: <?php echo floor($rs_boat[0]['nominallength']); ?> ft</li>

              <li><i class="fa fa-li fa-caret-right" aria-hidden="true"></i>Price: <span class="price-tag"> <?php if($rs_boat[0]['price']!=0) {echo("$".number_format($rs_boat[0]['price'],0,'',','));}else{echo("Call for Price");}?></span></li>

              <li><i class="fa fa-li fa-caret-right" aria-hidden="true"></i>Located in <?php if($rs_boat[0]['city'] !='' && $rs_boat[0]['city'] != "Unknown"){ echo $rs_boat[0]['city'].", ";}  if($rs_boat[0]['statecode']){ echo $rs_boat[0]['statecode']." ";} echo $rs_boat[0]['countrycode'] ?></li>

              <li><i class="fa fa-li fa-caret-right" aria-hidden="true"></i>Hull Material: <?php echo $hull; ?></li>

              <li><i class="fa fa-li fa-caret-right" aria-hidden="true"></i>Engine/Fuel Type:  <?php echo $fuel; ?></li>

              <li><i class="fa fa-li fa-caret-right" aria-hidden="true"></i>YW#  <?php echo $rs_boat[0]['yachtworldid']; ?></li>

            </ul>



            <p><?php echo $rs_boat[0]['generalboatdescription'] ?></p>

          </div>



          <div id="fullspec" class="tab-pane fade">



            <?php if($rs_boat[0]['buildername']!="" || $rs_boat[0]['designername']!="" || $rs_boat[0]['make']!="" ){ ?>

                  <h4>Make</h4>

                  <p>

                      <?php if($rs_boat[0]['make']!=""){  echo "Make: ".$rs_boat[0]['make']."</br>";}?>

                      <?php if($rs_boat[0]['buildername']!=""){  echo "Builder: ".$rs_boat[0]['buildername']."</br>";}?>

                      <?php if($rs_boat[0]['designername']!=""){ echo "Designer: ".$rs_boat[0]['designername']."</br>"; }?>

                  </p>

              <?php } ?>

            <?php if($rs_boat[0]['lengthoverall']!="" || $rs_boat[0]['lengthatwaterline']!="" || $rs_boat[0]['lengthofdeck']!="" ||  $rs_boat[0]['maxdraft']!="" ||

             $rs_boat[0]['displacementmeasure']!="" || $rs_boat[0]['ballastweightmeasure']!="" || $rs_boat[0]['bridgeclearancemeasure']!="" ||

             $rs_boat[0]['cabinheadroommeasure']!="" || $rs_boat[0]['beammeasure']!="" || $rs_boat[0]['freeboardmeasure']!="" || $rs_boat[0]['rangemeasure']!=""){?>

              <h4>Measurements</h4>

              <p>

                <?php if($rs_boat[0]['lengthoverall']!="" && $rs_boat[0]['lengthoverall']!=0.00){ echo "Length Overall: ".$rs_boat[0]['lengthoverall']." ft</br>"; }?>

                <?php if($rs_boat[0]['lengthatwaterline']!="" && $rs_boat[0]['lengthatwaterline']!=0.00){ echo "Length Waterline: ".$rs_boat[0]['lengthatwaterline']." ft</br>"; }?>

                <?php if($rs_boat[0]['lengthofdeck']!="" && $rs_boat[0]['lengthofdeck']!=0.00){   echo "Length of Deck:".$rs_boat[0]['lengthofdeck']." ft</br>";} ?>

                <?php if($rs_boat[0]['maxdraft']!="" && $rs_boat[0]['maxdraft']!=0.00){ echo "Max Draft: ".$rs_boat[0]['maxdraft']." ft</br>";} ?>

                <?php if($rs_boat[0]['displacementmeasure']!="" && $rs_boat[0]['displacementmeasure']!=0.00){ echo "Displacement:".$rs_boat[0]['displacementmeasure']." lb "; echo $rs_boat[0]['displacementmeasure']."</br>";} ?>

                <?php if($rs_boat[0]['ballastweightmeasure']!="" && $rs_boat[0]['ballastweightmeasure']!= 0.00){ echo "Ballast Weight: ".$rs_boat[0]['ballastweightmeasure']." lb</br>";} ?>

                <?php if($rs_boat[0]['bridgeclearancemeasure']!="" && $rs_boat[0]['bridgeclearancemeasure']!= 0.00){ echo "Bridge Clearance: ".$rs_boat[0]['bridgeclearancemeasure']." ft</br>"; } ?>

                <?php if($rs_boat[0]['cabinheadroommeasure']!="" && $rs_boat[0]['cabinheadroommeasure']!= 0.00){ echo " Cabin Head room: ".$rs_boat[0]['cabinheadroommeasure']." ft</br>"; }?>

                <?php if($rs_boat[0]['beammeasure']!="" && $rs_boat[0]['beammeasure'] != 0.00){ echo "Beam: ".$rs_boat[0]['beammeasure']." ft</br>"; }?>

                <?php if($rs_boat[0]['freeboardmeasure']!="" && $rs_boat[0]['freeboardmeasure']!= 0.00){ echo "Free board: ".$rs_boat[0]['freeboardmeasure']." ft</br>"; }?>

                <?php if($rs_boat[0]['rangemeasure']!="" && $rs_boat[0]['rangemeasure']!= 0.00){ echo "Range: ".$rs_boat[0]['rangemeasure']." mi</br>";} ?>

              </p>

            <?php }?>

            <?php if($rs_engines){?>

              <h4>Engines</h4>

              <?php foreach($rs_engines as $engine){ ?>

                <p>

                  <?php if($rs_boat[0]['rangemeasure']!=""){ echo "Total Power: ".$engine['enginepower']."</br>"; }?>

                  <?php if($engine['make']!=""){ echo " Engine Brand: ".$engine['make']."</br>";} ?>

                  <?php if($engine['year']!=""){ echo "Year Built: ".$engine['year']."</br>";} ?>

                  <?php if($engine['model']!=""){ echo "Engine Model: ".$engine['model']."</br>";} ?>

                  <?php if($engine['type']!=""){ echo "Engine Type: ".$engine['type']."</br>";} ?>

                  <?php if($engine['fuel']!=""){ echo "Engine/Fuel Type: ".$engine['fuel']."</br>";} ?>

                  <?php if($engine['hours']!=""){ echo "Engine Hours: ".$engine['hours']."</br>";} ?>

                  <?php if($engine['enginepower']!=""){ echo "Engine Power: ".$engine['enginepower']."</br>"; } ?>



                </p>

              <?php }

            } ?>

            <?php if($rs_boat[0]['watertankcountnumeric'] != "" || $rs_boat[0]['fueltankcountnumeric'] ){ ?>

              <h4>Tanks</h4>

              <p>

                Fresh Water Tanks: <?php echo $rs_boat[0]['watertankcountnumeric']; ?> (<?php echo floor($rs_boat[0]['watertankcapacitymeasure']); ?> Gallons)</br>

                Fuel Tanks: <?php echo $rs_boat[0]['fueltankcountnumeric']; ?> (<?php echo floor($rs_boat[0]['fueltankcapacitymeasure']); ?> Gallons)</br>



              </p>

            <?php } ?>

            <p><?php

                $pattern = '[Â|â|€|˜|™|¢|customContactInformation]';

                // echo str_replace("Â","",$rs_boat[0]['additionaldetaildescription']); 

                echo preg_replace($pattern, ' ', $rs_boat[0]['additionaldetaildescription']);

            ?></p>

          

          </div>    



        </div>    

        </div>    

        <div class="col-lg-4 agent_info">
            
            
<?php require_once( __DIR__ . '/tcpdf/tcpdf.php' );
            
            
// Ensure PDF folder exists

$upload_dir = wp_upload_dir();
$upload_path = $upload_dir['basedir'];



$pdfDir = $upload_path . '/yacht_pdfs/';

if (!is_dir($pdfDir)) {
    mkdir($pdfDir, 0755, true);
}


$slug = strtolower(trim( $rs_boat[0]['make'] . "-" . $rs_boat[0]['model'] . "-" . $rs_boat[0]['year'] )); 
$slug = preg_replace('/[^a-z0-9\s-]/', '', $slug); // Remove special characters
$slug = preg_replace('/\s+/', '-', $slug); // Replace spaces with hyphens
$slug1 = $slug . "-" . rand(0, 999) . ".pdf";

// File path
$pdfFilePath = $pdfDir . $slug1;


// File URL
$upload_dir = wp_upload_dir();
$upload_url = $upload_dir['baseurl'];

$pdfFileURL = $upload_url . "/yacht_pdfs/" . $slug1;




$yacht_name = $rs_boat[0]['make'] . " " . $rs_boat[0]['model'] . " " . $rs_boat[0]['year'];



$price_pdf = "";

if($rs_boat[0]['price']!=0) { $price_pdf = ("$".number_format($rs_boat[0]['price'],0,'',','));}else{ $price_pdf = ("Call for Price");}


$city_pdf = "";

if($rs_boat[0]['city'] !='' && $rs_boat[0]['city'] != "Unknown"){ $city_pdf = $rs_boat[0]['city'].", ";}  

if($rs_boat[0]['statecode']){ $city_pdf .= $rs_boat[0]['statecode'] . " " . $rs_boat[0]['countrycode'];} 



// Generate PDF
$pdf = new TCPDF();
$pdf->setPrintHeader(false); // Disable the black top border
$pdf->setPrintFooter(false); // Optional: Disable footer too
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle($yacht_name);
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();




// Add Image

if ($pdf_image != ""){
    
    $image_data = @file_get_contents($pdf_image);
    
    if ($image_data) {
    file_put_contents('temp_image.jpg', $image_data);
    
    // Set image position (Left-aligned) and size
    $x_position = 10;   // Left margin
    $y_position = 10;   // Top margin
    $img_width = 80;    // Adjust as needed
    $img_height = 80;   // Adjust as needed 50

    // Insert image
    $pdf->Image('temp_image.jpg', $x_position, $y_position, $img_width, $img_height, '', '', '', false, 300, '', false, false, 0, false, false, false);
    
    // Move the cursor below the image before writing text
    $pdf->SetY($y_position + $img_height + 10); // 10 is extra spacing

    unlink('temp_image.jpg'); // Delete image after use
} else {
    $pdf->Cell(0, 10, 'Image could not be loaded.', 0, 1, 'C');
}


}


// Add Title
$pdf->SetFont('helvetica', 'B', 20);
$pdf->Cell(0, 10, $yacht_name, 0, 1, 'L');
$pdf->Ln(5);


/*
// Now text will start below the image
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Yacht Name: Luxury Boat', 0, 1, 'L');
*/


$pdf->SetFont('helvetica', '', 12);

if (!empty($rs_boat[0]['year'])){
$pdf->Cell(0, 10, 'Year: ' . $rs_boat[0]['year'], 0, 1, 'L');
}

if (!empty( floor($rs_boat[0]['nominallength']) )){
$pdf->Cell(0, 10, 'Length: ' . floor($rs_boat[0]['nominallength']) . " ft", 0, 1, 'L');
}

if (!empty( $price_pdf )){
$pdf->Cell(0, 10, 'Price: ' . $price_pdf, 0, 1, 'L');
}

if (!empty( $city_pdf )){
$pdf->Cell(0, 10, 'Located in: ' . $city_pdf, 0, 1, 'L');
}

if (!empty( $hull )){
$pdf->Cell(0, 10, 'Hull Material: ' . $hull, 0, 1, 'L');
}

if (!empty( $fuel )){
$pdf->Cell(0, 10, 'Engine/Fuel Type: ' . $fuel, 0, 1, 'L');
}

if (!empty( $rs_boat[0]['yachtworldid'] )){
$pdf->Cell(0, 10, 'YW# ' . $rs_boat[0]['yachtworldid'], 0, 1, 'L');
}



// Make -------------------------------------------------------------------------------------------------------------

if($rs_boat[0]['buildername']!="" || $rs_boat[0]['designername']!="" || $rs_boat[0]['make']!="" ){
    
    
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Make ', 0, 1, 'L');


$pdf->SetFont('helvetica', '', 12);
    
    
    if($rs_boat[0]['make']!=""){  
        
        $pdf->Cell(0, 10, 'Make: ' . $rs_boat[0]['make'], 0, 1, 'L');
        
    } 

    if($rs_boat[0]['buildername']!=""){  
        
        $pdf->Cell(0, 10, 'Builder: ' . $rs_boat[0]['buildername'], 0, 1, 'L');
        
    }

    if($rs_boat[0]['designername']!=""){ 
        
        $pdf->Cell(0, 10, 'Designer: ' . $rs_boat[0]['designername'], 0, 1, 'L');
        
    }

       

}



// Measurements -------------------------------------------------------------------------------------------------------------

if($rs_boat[0]['lengthoverall']!="" || $rs_boat[0]['lengthatwaterline']!="" || $rs_boat[0]['lengthofdeck']!="" ||  $rs_boat[0]['maxdraft']!="" ||
 $rs_boat[0]['displacementmeasure']!="" || $rs_boat[0]['ballastweightmeasure']!="" || $rs_boat[0]['bridgeclearancemeasure']!="" ||
 $rs_boat[0]['cabinheadroommeasure']!="" || $rs_boat[0]['beammeasure']!="" || $rs_boat[0]['freeboardmeasure']!="" || $rs_boat[0]['rangemeasure']!=""){
     
     
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Measurements ', 0, 1, 'L');


$pdf->SetFont('helvetica', '', 12);
     

if($rs_boat[0]['lengthoverall']!="" && $rs_boat[0]['lengthoverall']!=0.00){ 
    
    $pdf->Cell(0, 10, "Length Overall: ".$rs_boat[0]['lengthoverall'] . " ft", 0, 1, 'L');
    
    
}


if($rs_boat[0]['lengthatwaterline']!="" && $rs_boat[0]['lengthatwaterline']!=0.00){ 
    
    $pdf->Cell(0, 10, "Length Waterline: ".$rs_boat[0]['lengthatwaterline']." ft", 0, 1, 'L');
    
    
}


if($rs_boat[0]['lengthofdeck']!="" && $rs_boat[0]['lengthofdeck']!=0.00){   
    
    $pdf->Cell(0, 10, "Length of Deck:".$rs_boat[0]['lengthofdeck']." ft", 0, 1, 'L');
    
    
}


if($rs_boat[0]['maxdraft']!="" && $rs_boat[0]['maxdraft']!=0.00){ 
    
    $pdf->Cell(0, 10, "Max Draft: ".$rs_boat[0]['maxdraft']." ft", 0, 1, 'L');
    
    
}


if($rs_boat[0]['displacementmeasure']!="" && $rs_boat[0]['displacementmeasure']!=0.00){ 
    
    $pdf->Cell(0, 10, "Displacement:".$rs_boat[0]['displacementmeasure']." lb " . $rs_boat[0]['displacementmeasure']."</br>", 0, 1, 'L');
    
    
}


if($rs_boat[0]['ballastweightmeasure']!="" && $rs_boat[0]['ballastweightmeasure']!= 0.00){ 
    
    $pdf->Cell(0, 10, "Ballast Weight: ".$rs_boat[0]['ballastweightmeasure']." lb", 0, 1, 'L');
    
    
}


if($rs_boat[0]['bridgeclearancemeasure']!="" && $rs_boat[0]['bridgeclearancemeasure']!= 0.00){ 
    
    $pdf->Cell(0, 10, "Bridge Clearance: ".$rs_boat[0]['bridgeclearancemeasure']." ft", 0, 1, 'L');
    
}



if($rs_boat[0]['cabinheadroommeasure']!="" && $rs_boat[0]['cabinheadroommeasure']!= 0.00){ 
    
    $pdf->Cell(0, 10, " Cabin Head room: ".$rs_boat[0]['cabinheadroommeasure']." ft", 0, 1, 'L');
    
}



if($rs_boat[0]['beammeasure']!="" && $rs_boat[0]['beammeasure'] != 0.00){ 
    
    $pdf->Cell(0, 10, "Beam: ".$rs_boat[0]['beammeasure']." ft", 0, 1, 'L');
    
}



if($rs_boat[0]['freeboardmeasure']!="" && $rs_boat[0]['freeboardmeasure']!= 0.00){ 
    
    $pdf->Cell(0, 10, "Free board: ".$rs_boat[0]['freeboardmeasure']." ft", 0, 1, 'L');
    
}



if($rs_boat[0]['rangemeasure']!="" && $rs_boat[0]['rangemeasure']!= 0.00){ 
    
    $pdf->Cell(0, 10, "Range: ".$rs_boat[0]['rangemeasure']." mi", 0, 1, 'L');
    
}


     
}



// Engines -------------------------------------------------------------------------------------------------------------

if($rs_engines){
    
    
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Engines ', 0, 1, 'L');



$pdf->SetFont('helvetica', '', 12);


foreach($rs_engines as $engine){
    
    
    
    
    if($rs_boat[0]['rangemeasure']!=""){ 
        
        $pdf->Cell(0, 10, " Total Power: ".$engine['enginepower'], 0, 1, 'L');
        
    }



    if($engine['make']!=""){ 
        
        $pdf->Cell(0, 10, " Engine Brand: ".$engine['make'], 0, 1, 'L');
        
    }
    
    
    
    if($engine['year']!=""){ 
        
        $pdf->Cell(0, 10, "Year Built: ".$engine['year'], 0, 1, 'L');
        
    }
    
    
    
    if($engine['model']!=""){ 
        
        $pdf->Cell(0, 10, "Engine Model: ".$engine['model'], 0, 1, 'L');
        
    }
    
    
    
    if($engine['type']!=""){ 
        
        $pdf->Cell(0, 10, "Engine Type: ".$engine['type'], 0, 1, 'L');
        
    }
    
    
    
    if($engine['fuel']!=""){ 
        
        $pdf->Cell(0, 10, "Engine/Fuel Type: ".$engine['fuel'], 0, 1, 'L');
        
    } 
    
    
    
    if($engine['hours']!=""){ 
        
        $pdf->Cell(0, 10, "Engine Hours: ".$engine['hours'], 0, 1, 'L');
        
    } 
    
    
    
    if($engine['enginepower']!=""){ 
        
        $pdf->Cell(0, 10, "Engine Power: ".$engine['enginepower'], 0, 1, 'L');
        
    } 

    
}


    
    
}



if($rs_boat[0]['watertankcountnumeric'] != "" || $rs_boat[0]['fueltankcountnumeric'] ){
    
    
    
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Tanks ', 0, 1, 'L');



$pdf->SetFont('helvetica', '', 12);



$pdf->Cell(0, 10, "Fresh Water Tanks: " . $rs_boat[0]['watertankcountnumeric'] . floor($rs_boat[0]['watertankcapacitymeasure']) . "Gallons", 0, 1, 'L');
    
    
$pdf->Cell(0, 10, "Fuel Tanks: " . $rs_boat[0]['fueltankcountnumeric'] . floor($rs_boat[0]['fueltankcapacitymeasure']) . "Gallons", 0, 1, 'L');


} 


if($rs_boat[0]['generalboatdescription']!=""){ 

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'General Boat Description ', 0, 1, 'L');


$pdf->SetFont('helvetica', '', 12);



$content1 = str_replace('font-family', 'font-family1', $rs_boat[0]['generalboatdescription']);

$content1 = str_replace('font-size', 'font-size1', $content1);


$pdf->writeHTML($content1, true, false, true, false, '');

}

 

/*
$details = "Year: {$yacht['year']}\n" .
           "Length: {$yacht['length']}\n" .
           "Price: {$yacht['price']}\n" .
           "Located in: {$yacht['location']}\n" .
           "Hull Material: {$yacht['hull_material']}\n" .
           "Engine Type: {$yacht['engine_type']}\n";
*/




$details = "";       
           
           
$pdf->MultiCell(0, 10, $details, 0, 'L');

// Save PDF file
$pdf->Output($pdfFilePath, 'F');

// Debugging Output
if (file_exists($pdfFilePath)) {
    //echo "PDF generated successfully at: " . realpath($pdfFilePath);
} else {
    //echo "Failed to create PDF!";
}
            
            
            
            ?>
            

          <div class="right-box-inner">
              
              <div id="divToPrint1">
                <h3><?php echo $rs_boat[0]['make']; ?>&nbsp;<?php echo $rs_boat[0]['model']; ?><br><span><?php echo $rs_boat[0]['year'];?></span></h3>
    
                <span class="line"></span>
    
                <h4><?php if($rs_boat[0]['price']!=0) {echo("$".number_format($rs_boat[0]['price'],0,'',','));}else{echo("Call for Price");}?>
    
                <br><span><?php if($rs_boat[0]['city'] != "Unknown"){echo $rs_boat[0]['city'].", ";} echo $rs_boat[0]['statecode']; ?></span></h4>
    
              </div>
          
    			<a href="mailto:<?php echo $rs_agent["email"]; ?>" class="btn-layout small-btn-layout"> <?php echo $rs_agent["email"]; ?></a>
    			
    			<a href="tel:<?php echo $rs_agent["phone"]; //12319335414 ?>" class="btn-layout small-btn-layout"><?php echo $rs_agent["phone"]; //12319335414 ?></a>
    			
    			<a href="<?php echo $pdfFileURL; ?>" download="<?php echo $slug1; ?>" class="btn-layout small-btn-layout">
    			    Download PDF
    			</a>
			
          </div>

        </div> 

      </div> 

      <!-- More information form -->

      <!-- Button trigger modal -->

      <!-- Modal -->

      <div class="modal fade" id="more-info" tabindex="-1" role="dialog" aria-labelledby="more-infoModalLabel" aria-hidden="true">

          <div class="modal-dialog" role="document">

            <div class="modal-content">

              <div class="modal-header">

                <div class="sub-heading-global">

                  <h2>

                    Request More Information

                  </h2>

                </div>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                  <span aria-hidden="true">&times;</span>

                </button>

              </div>

              <form method="post" id="queries_form">

               <div class="modal-body">

                <?php 

                  $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

                ?>

                <input type="hidden" name="boat-name" value="<?= $rs_boat[0]['make'] ?> <?= $rs_boat[0]['model']; ?>">

                <input type="hidden" name="boat-url" value="<?= $actual_link ?>">

                <input type="hidden" name="boat-id" value="<?= $_GET['boat_id'] ?>">

                <div class="popup-image">

                  <img src="<?php echo $rs_images[0]['url']?>" style="height: 200px;margin-bottom: 13px;">

                  <h4><span><?php echo $rs_boat[0]['year'];?></span> <?php echo $rs_boat[0]['make']; ?>&nbsp;<?php echo $rs_boat[0]['model']; ?></h4>

                </div>

                <div class="form-rowfull">

                  <label>Your Name</label>

                  <input name="full_name"  type="text" value="" class="form-control" required="required">

                </div>

                <div class="form-row-half">

                  <label>Email</label>

                  <input name="email"  type="email" value="" class="form-control" required="required">

                </div>

                <div class="form-row-half">

                  <label>Phone</label>

                  <input name="phone"  type="text" value="" class="form-control" >

                </div>

                <div class="form-rowfull">

                  <label>Your Company</label>

                  <input name="company"  type="text" value="" class="form-control" >

                </div>

                <div class="form-rowfull">

                  <label>Message</label>

                  <textarea name="message" class="form-control" style="height:226px" width="100%"></textarea>

                </div>

                <div class="modal-footer">

                  <button type="button" id="queries_data" class="btn btn-primary">Submit</button>

                </div>

              </div>

            </form>  

          </div>

        </div>

      </div>

    </div>

  </div>

</div>



<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>





<!-- Grey Box Script -->

<script type='text/javascript'>

jQuery(function($) {

  $(window).load(function() {

    affix();

  });

  $(window).scroll(function() {

    affix();

  });



  var flag = 0;

  function affix() {

    if ($(window).height() > $('.affix-top').height()) {

      if (flag == 0) {

        $('.affix-top').removeAttr('style');

      }

      var positionScroll = $(window).scrollTop();

      var positionTop = $('.affix-hook').offset().top;

      var positionStop = ($('.section-white').height() + positionTop - 100) - $('.affix-top').outerHeight();



      if (flag == 1) {

        $('.affix-top').css('top',  positionStop - positionTop + 15);

      }



      flag = 1;

      if (positionScroll + $('.menu').outerHeight() > positionTop && positionScroll < positionStop - $('.menu').outerHeight()) {

        $('.affix-top').css('top',  positionScroll - positionTop + $('.menu').outerHeight());

        flag = 0;

      }

      if (positionScroll < positionTop) {

        flag = 0;

      }

    } else {

      $('.affix-top').css('top',  0);

    }

  }

});

</script>



<!-- Royal Slider -->

<link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/royalslider/royalslider.css'; ?>">

<link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/royalslider/skins/default/rs-default.css' ?>"> 

<script src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/royalslider/jquery.royalslider.min.js'?>"></script>

<script type='text/javascript'>

jQuery(function($) {

   $(".royalSlider").royalSlider({

    //transitionType: 'fade',

    controlNavigation: 'thumbnails',

    autoScaleSlider: true,

    autoScaleSliderWidth: 750,

    autoScaleSliderHeight:  575,

    imageScaleMode: 'fit',

    imageScalePadding: 0,

    navigateByClick: false,

    arrowsNav:true,

    arrowsNavAutoHide: true,

    arrowsNavHideOnTouch: false,

    thumbs: {

      spacing: 6,

      arrows: false,

      appendSpan: true,

      firstMargin: false,

    },

    autoPlay: {

      delay: 3000,

      enabled: true,

      pauseOnHover: true

    },

    fullscreen: {

      enabled: true,

      nativeFS: false

    }

  });

});



</script>



<script type="text/javascript">     

    function PrintYachtsPage() {    

       var divToPrint1 = document.getElementById('divToPrint1');

       var divToPrint2 = document.getElementById('divToPrint2');

       var divToPrint3 = document.getElementById('divToPrint3');

       var about = document.getElementById('about');

       var divToPrint4 = document.getElementById('divToPrint4');

       var fullspec = document.getElementById('fullspec');

       var popupWin = window.open('', '_blank', 'width=1000,height=1000');

       popupWin.document.open();

       popupWin.document.write('<html><body onload="window.print()">' + divToPrint1.innerHTML +  divToPrint2.innerHTML + divToPrint3.innerHTML + about.innerHTML + divToPrint4.innerHTML + fullspec.innerHTML +'</html>');

        popupWin.document.close();

            }

 </script>



 <?php 

}else{

  echo "It seems plugin not activated OR Api key is not configured.";

}

 ?>

<?php get_footer(); ?>
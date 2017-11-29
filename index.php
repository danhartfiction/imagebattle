<?php

$dir = '/opt/lampp/htdocs/ib';

echo "<html><body>";

if (!isset($_GET['mode'])) $_GET['mode'] = "new";
$mode = $_GET['mode'];

if (!$mode or $mode == 'new') {
  # Build list of categories
  $imageDirectories = array();
  if ($dirhandle = opendir($dir)) {
    while (false !== ($file = readdir($dirhandle))) {
      if ($file == '.' || $file == '..') {
        continue;
      }
      if (is_dir("$dir/$file")) {
        array_push($imageDirectories, $file);
      }
    }
  }

  echo "Choose a category to battle: <br><ul>";
  foreach ($imageDirectories as $id) {
    echo "<li><a href=\"/ib/index.php?mode=battle&folder=" . addslashes($id) . "\">$id</a></li>";
  }
  echo "</ul>";
} elseif ($mode == 'victory') {
   $db = mysqli_connect('localhost', 'ImageBattle', 'password', 'ImageBattle') or die();
   $folder = base64_encode($_GET['folder']);
   $winner = $_GET['winner'];
   $loser = $_GET['loser'];
   # Update winning image
   if ($w_image_result = mysqli_query($db, "SELECT * FROM ImageBattle WHERE filename='$winner' AND category_data='$folder') LIMIT 1")) {
     $w_image = $w_image_result->fetch_assoc();
     if (isset($w_image['id'])) {
       mysqli_query($db, "UPDATE ImageBattle SET total_wins='" . ($w_image['total_wins'] + 1) . "' WHERE id='" . $w_image['id'] . "' LIMIT 1;") or die();
     } else {
       mysqli_query($db, "INSERT INTO ImageBattle (id, filename, raw_filename, total_wins, total_losses, category_data) VALUES ('', '$winner', '" . base64_decode($winner) . "' ,'1', '0', '" . $folder . "')") or die();
     }
   }
   # Update losing image
   if ($l_image_result = mysqli_query($db, "SELECT * FROM ImageBattle WHERE filename='$loser' LIMIT 1")) {
     $l_image = $l_image_result->fetch_assoc();
     if (isset($l_image['id'])) {
       mysqli_query($db, "UPDATE ImageBattle SET total_losses='" . ($l_image['total_losses'] + 1) . "', WHERE id='" . $l_image['id'] . "' LIMIT 1;") or die();
     } else {
       mysqli_query($db, "INSERT INTO ImageBattle (id, filename, raw_filename, total_wins, total_losses, category_data) VALUES ('', '$loser', '" . base64_decode($loswer) . "', '0', '1', '" . $folder . "')") or die();
     }
   }
   # Next set!
   header("Location: /ib/index.php?mode=battle&folder=$folder");
} else {
#  echo "Mode: $mode<br>";
  $folder = $_GET['folder'];
#  echo "Folder: $folder<br>";

  # Find two random images
  $battleImages = array();
  if ($dirhandle = opendir($dir . '/' . $folder)) {
    while (false !== ($file = readdir($dirhandle))) {
      if ($file == '.' || $file == '..') {
        continue;
      }
      $fn = $dir . '/' . $folder . '/' . $file;
      $in = $folder . '/' . $file;
      if (is_file($fn)) {
        array_push($battleImages, $in);
      }
    }
  }
  $random_images = array_rand($battleImages, 2);
  $image1 = base64_encode($battleImages[$random_images[0]]);
  $image2 = base64_encode($battleImages[$random_images[1]]);
  echo "<table border=0, cellpadding=10, cellspacing=0 width=100%><tr>";
  echo "<td width=50% valign=top>";
  echo "<a href=\"/ib/index.php?mode=victory&folder=$folder&winner=$image1&loser=$image2\">";
  echo "<img width=100% src=\"" . $battleImages[$random_images[0]] . "\"></a></td>";
  echo "<td width=50% valign=top>";
  echo "<a href=\"/ib/index.php?mode=victory&folder=$folder&winner=$image2&loser=$image1\">";
  echo "<img width=100% src=\"" . $battleImages[$random_images[1]] . "\"></a></td>";
  echo "</tr></table>";
}

echo "</body></html>";

?>

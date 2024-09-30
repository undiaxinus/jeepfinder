<?php
  include_once("../connection/connect.php");
  $conn = connection();
  $id = $_GET['id'];
  $sql = "SELECT * FROM user WHERE account ='admin' ";
  $result = $conn->query($sql);
  $contact = [];
  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      $contact[] = $row;
    }
  }
  foreach ($contact as $con) {
    echo '<li class="">
    ';
    if ($con['status'] == 'online') {
      echo ' <a href="contact.php?id='.$id.'&user='.$con['user'].'" style="text-decoration: none;">
        <div class="d-flex bd-highlight">
          <div class="img_cont">
            <img src="../img/'.$con['profile'].'" class="rounded-circle user_img">
            <span class="online_icon"></span>
          </div>
          <div class="user_info">
            <span>'.$con['fname'].' '.$con['mname'].' '.$lname.'</span>
            <p>Costumer Service is online</p>
          </div>
        </div>
      </a>';
    } else{
      echo ' <a href="contact.php?id='.$id.'" style="text-decoration: none;">
        <div class="d-flex bd-highlight">
          <div class="img_cont">
            <img src="../img/'.$con['profile'].'" class="rounded-circle user_img">
            <span class="online_icon offline"></span>
          </div>
          <div class="user_info">
            <span>'.$con['fname'].' '.$con['mname'].' '.$lname.'</span>
              <p>Costumer Service is offline</p>
          </div>
        </div>
      </a>';
    }
    echo '</li>'; 
  }
?>
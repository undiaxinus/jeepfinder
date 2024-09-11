<?php
session_start();
if ($_SESSION['Role'] != 'user') {
    header('Location: ../index.html?error=Access denied');
    exit;
}

                
include_once("../connection/connect.php");
$conn = connection();


date_default_timezone_set('Asia/Manila'); // Set timezone to Manila, Philippines

$insertSuccess = false; // Flag to check if insertion was successful
if(isset($_POST['submit'])) {
   $sender_name = $_POST['sender_name'];
    $receiver_name = $_POST['receiver_name'];
    $message = $_POST['message'];
    $timestamp = date('Y-m-d H:i:s');

    $sql = "INSERT INTO message (sender_name, receiver_name, message, timestamp) VALUES ('$sender_name','$receiver_name','$message','$timestamp')";
    
    if ($conn->query($sql) === TRUE) {
        $insertSuccess = true;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch messages from the database
$sql = "SELECT * FROM user WHERE account ='admin' ";
$result = $conn->query($sql);
$contact = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $contact[] = $row;
    }
}



?>

<!DOCTYPE html>
<html>
  <head>
    <title>Chat</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"  crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.js"></script>
  </head>

  <style type="text/css">
    
   .home-section {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: 100vh;
        }
    .chat{
      margin-top: 20px;
      margin-bottom: auto;
    }
    .card{
      margin-bottom: 45px;
      height: 700px;
      border-radius: 15px !important;
      background-color: rgba(0,0,0,0.4) !important;
    }
    .contacts_body{
      padding:  0.75rem 0 !important;
      overflow-y: auto;
      white-space: nowrap;
    }
    .msg_card_body{

      overflow-y: auto;
    }
    .card-header{
      border-radius: 15px 15px 0 0 !important;
      border-bottom: 0 !important;
    }
   .card-footer{
    border-radius: 0 0 15px 15px !important;
      border-top: 0 !important;
  }
    .container{
      align-content: center;
    }
    .search{
      border-radius: 15px 0 0 15px !important;
      background-color: rgba(0,0,0,0.3) !important;
      border:0 !important;
      color:white !important;
    }
    .search:focus{
         box-shadow:none !important;
           outline:0px !important;
    }
    .type_msg{
      background-color: rgba(0,0,0,0.3) !important;
      border:0 !important;
      color:white !important;
      height: 60px !important;
      overflow-y: auto;
    }
      .type_msg:focus{
         box-shadow:none !important;
           outline:0px !important;
    }
    .attach_btn{
  border-radius: 15px 0 0 15px !important;
  background-color: rgba(0,0,0,0.3) !important;
      border:0 !important;
      color: white !important;
      cursor: pointer;
    }
    .send_btn{
  border-radius: 0 15px 15px 0 !important;
  background-color: rgba(0,0,0,0.3) !important;
      border:0 !important;
      color: white !important;
      cursor: pointer;
    }
    button p{
      height: 60px
    }
    .search_btn{
      border-radius: 0 15px 15px 0 !important;
      background-color: rgba(0,0,0,0.3) !important;
      border:0 !important;
      color: white !important;
      cursor: pointer;
    }
    .contacts{
      list-style: none;
      padding: 0;
    }
    .contacts li{
      width: 100% !important;
      padding: 5px 10px;
      margin-bottom: 15px !important;
    }
  .active{
      background-color: rgba(0,0,0,0.3);
  }
    .user_img{
      height: 70px;
      width: 70px;
      border:1.5px solid #f5f6fa;
      object-fit: cover;
    }
    .user_img_msg{
      height: 40px;
      width: 40px;
      border:1.5px solid #f5f6fa;
      object-fit: cover;
    }
  .img_cont{
      position: relative;
      height: 70px;
      width: 70px;
  }

  .img_cont_msg{
      height: 40px;
      width: 40px;
  }
  .online_icon{
    position: absolute;
    height: 15px;
    width:15px;
    background-color: #4cd137;
    border-radius: 50%;
    bottom: 0.2em;
    right: 0.4em;
    border:1.5px solid white;
  }
  .offline{
    background-color: #c23616 !important;
  }
  .user_info{
    margin-top: auto;
    margin-bottom: auto;
    margin-left: 15px;
  }
  .user_info span{
    font-size: 20px;
    color: white;
  }
  .user_info p{
  font-size: 10px;
  color: rgba(255,255,255,0.6);
  }
  .msg_cotainer{
    margin-top: auto;
    margin-bottom: auto;
    margin-left: 10px;
    border-radius: 25px;
    background-color: #82ccdd;
    padding: 10px;
    position: relative;
  }
  .msg_cotainer_send{
    margin-top: auto;
    margin-bottom: auto;
    margin-right: 10px;
    border-radius: 25px;
    background-color: #78e08f;
    padding: 10px;
    position: relative;
  }
  .msg_time{
    position: absolute;
    left: 0;
    bottom: -15px;
    color: rgba(255,255,255,0.5);
    font-size: 10px;
    width: 100px;
  }
  .msg_time_send{
    position: absolute;
    right:0;
    bottom: -15px;
    color: rgba(255,255,255,0.5);
    font-size: 10px;
    width: 100px;
  }
  .msg_head{
    position: relative;
  }

    /* Adjust font sizes for smaller screens */
        @media only screen and (max-width: 720px) {
          .chat{
      margin-top: 10px;
      height: 700px;
    }
    .card{
        width: 360px;
        margin-left: -15px;
    }
    .card-footer{
   margin-bottom: 35px;
  }
        }
  </style>
  
  <body>
    <?php
    include 'navigation.php';
    ?>
    <section class="home-section">
    <div class="container-fluid h-100">
      <div class="row justify-content-center h-100">
        <div class="col-md-4 col-xl-3 chat"><div class="card mb-sm-3 mb-md-0 contacts_card">
          <div class="card-header">
            <div class="input-group">
              <input type="text" placeholder="Search..." name="" class="form-control search">
              <div class="input-group-prepend">
                <span class="input-group-text search_btn"><i class="fas fa-search"></i></span>
              </div>
            </div>
          </div>
          <div class="card-body contacts_body">

            <ui class="contacts">
              
            </ui>

          </div>
          <div class="card-footer"></div>
        </div></div>
        <div class="col-md-8 col-xl-6 chat">
          <div class="card">
            <div class="card-header msg_head">
              <div class="d-flex bd-highlight">
                <div class="img_cont">
                  <img src="../img/sbmo.png" class="rounded-circle user_img">
                  <span class="online_icon"></span>
                </div>
                <div class="user_info">
                  <span>Costumer Service</span>
                  <p>1767 Messages</p>
                </div>
              </div> 
            </div>
            <div class="card-body msg_card_body ">
           
              
              <div class="d-flex justify-content-start mb-4">
                <div class="img_cont_msg">
                  <img src="" class="rounded-circle user_img_msg">
                </div>
      
              </div>
            </div>

            <form method="post" action="">
                    <?php
                    include_once("../connection/connect.php");
                    $sql = "SELECT * FROM user WHERE account = 'admin'";
                    $result = $conn->query($sql);
                    $receiver_name = '';
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $receiver_name = $row['user'];
                    }
                    ?>
                    <input type="hidden" name="sender_name" value="<?php echo $_SESSION['id']; ?>">
                    <input type="hidden" name="receiver_name" value="<?php echo $receiver_name; ?>">
            <div class="card-footer">
              <div class="input-group">
                <div class="input-group-append">
                  <span class="input-group-text attach_btn"></span>
                </div>
                <input name="message" class="form-control type_msg" placeholder="Type your message..." required>
                
                <button class="send_btn" type="submit" name="submit">
    <p class="input-group-text send_btn" style="">
       Send
    </p>
</button>

              </div>
            </div>
          </form>
          </div>
        </div>
      </div>
    </div>
  </section>
  </body>
  <script type="text/javascript">
      $(document).ready(function(){
$('#action_menu_btn').click(function(){
  $('.action_menu').toggle();
});
  });
  </script>
<script>
$(document).ready(function() {
    // Function to fetch messages
    function fetchMessages() {
        $.ajax({
            url: 'fetch_messages.php?id=<?php echo $id; ?>', // Replace with your PHP script handling message fetching
            success: function(data) {
                $('.msg_card_body').html(data); // Replace the messages section with updated content
            }
        });
    }

    // Fetch messages 
    setInterval(fetchMessages, 1);
});
</script>
<script>
$(document).ready(function() {
    // Function to fetch messages
    function fetchMessages() {
        $.ajax({
            url: 'fetch_contact.php?id=<?php echo $id; ?>', // Replace with your PHP script handling message fetching
            success: function(data) {
                $('.contacts').html(data); // Replace the messages section with updated content
            }
        });
    }

    // Fetch messages 
    setInterval(fetchMessages, 1);
});
</script>

</html>

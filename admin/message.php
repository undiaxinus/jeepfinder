<?php
  session_start();
  if (!isset($_SESSION['Role']) || $_SESSION['Role'] != 'admin') {
    header('Location: ../index.html?error=Access denied');
    exit;
  }
  include_once("../connection/connect.php");
  $conn = connection();
  $sender_id = isset($_GET['id']) ? $_GET['id'] : null;
  date_default_timezone_set('Asia/Manila');
  $insertSuccess = false;
  if (isset($_POST['submit'])) {
    $receiver_name = $_POST['receiver_name'];
    $message = $_POST['message'];
    $timestamp = date('Y-m-d H:i:s');
    $mstatus = "unread";
    $sql = "INSERT INTO message (sender_name, receiver_name, message,message_status, timestamp) VALUES ('$sender_id','$receiver_name','$message', '$mstatus', '$timestamp')";
    if ($conn->query($sql) === TRUE) {
      $insertSuccess = true;
    } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
  }
  $sql = "SELECT * FROM user WHERE account ='user' ";
  $result = $conn->query($sql);
  $contact = [];
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $contact[] = $row;
    }
  }
  $messages = []; 
  $messageSql = "SELECT * FROM message WHERE sender_name = '$sender_id' OR receiver_name = '$sender_id' ORDER BY timestamp";
  $messageResult = $conn->query($messageSql);
  if ($messageResult->num_rows > 0) {
    while ($msgRow = $messageResult->fetch_assoc()) {
      $messages[] = $msgRow;
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
    .online_icons{
      position: absolute;
      height: 15px;
      width:15px;
      background-color: #c23616;
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
    .contacts li.contact-item {
      cursor: pointer;
    }
    .contacts li.contact-item:hover {
      background-color: rgba(0, 0, 0, 0.2);
    }
    .contact-item.active {
      background-color: rgba(0, 0, 0, 0.2);
    }
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
    <?php include 'navigation.php'; ?>
    <section class="home-section">
      <div class="container-fluid h-100">
        <div class="row justify-content-center h-100">
          <div class="col-md-4 col-xl-3 chat">
            <div class="card mb-sm-3 mb-md-0 contacts_card">
              <div class="card-header">
                <div class="input-group">
                  <input type="text" placeholder="Search..." class="form-control search">
                  <div class="input-group-prepend">
                    <span class="input-group-text search_btn"><i class="fas fa-search"></i></span>
                  </div>
                </div>
              </div>
              <div class="card-body contacts_body">
                <ul class="contacts">
                  <?php foreach ($contact as $c): ?>
                    <li class="contact-item" 
                      data-id="<?php echo $c['id']; ?>" 
                      data-fullname="<?php echo htmlspecialchars($c['fname'] . ' ' . $c['mname'] . ' ' . $c['lname']); ?>" 
                      data-username="<?php echo htmlspecialchars($c['user']); ?>" 
                      data-status="<?php echo htmlspecialchars($c['status']); ?>">
                      <div class="d-flex bd-highlight">
                        <div class="img_cont">
                          <img src="../img/sbmo.png" class="rounded-circle user_img">
                          <?php
                            if($c['status'] == "online"){
                              echo '<span class="online_icon"></span>';
                            }else{
                              echo '<span class="online_icons"></span>';
                            }
                          ?>
                        </div>
                        <div class="user_info">
                          <span><?php echo htmlspecialchars($c['fname'] . ' ' . $c['mname'] . ' ' . $c['lname']); ?></span>
                          <p><?php echo htmlspecialchars($c['status']); ?></p>
                        </div>
                      </div>
                    </li>
                  <?php endforeach; ?>
              </ul>
            </div>
            <div class="card-footer"></div>
          </div>
        </div>
        <div class="col-md-8 col-xl-6 chat">
          <div class="card" id="conversation-card" style="display: none;">
            <div class="card-header msg_head">
              <div class="d-flex bd-highlight">
                <div class="img_cont">
                  <img src="../img/sbmo.png" class="rounded-circle user_img">
                  <div id="status-name"></div>
                  </div>
                  <div class="user_info">
                    <span id="receiver-name">Customer Service</span>
                  </div>
                </div>
              </div>
              <div class="card-body msg_card_body"></div>
              <form method="post" action="">
                <input type="hidden" name="sender_name" value="<?php echo htmlspecialchars($sender_id); ?>">
                <input type="hidden" name="receiver_name" value="">
                <div class="card-footer">
                  <div class="input-group">
                    <div class="input-group-append">
                      <span class="input-group-text attach_btn"></span>
                    </div>
                    <input name="message" class="form-control type_msg" placeholder="Type your message..." required>
                    <button class="send_btn" type="submit" name="submit" >
                      <p class="input-group-text send_btn">Send</p>
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
    <script>
      $(document).ready(function() {
        $('.msg_card_body').animate({ scrollTop: $('.msg_card_body').prop('scrollHeight') }, 1000);
        $('#conversation-card').hide();
        var activeContactId = localStorage.getItem('activeContactId');
        if (activeContactId) {
          $('.contact-item[data-id="' + activeContactId + '"]').addClass('active');
          var receiverFullName = $('.contact-item[data-id="' + activeContactId + '"]').data('fullname');
          var receiverUsername = $('.contact-item[data-id="' + activeContactId + '"]').data('username');
          var statusname = $('.contact-item[data-id="' + activeContactId + '"]').data('status');
          $('#receiver-name').text(receiverFullName);
          $('input[name="receiver_name"]').val(receiverUsername);
          $('#status-name').text(statusname);
          if (statusname === 'online') {
            $('#status-name').html('<span class="online_icon"></span>');
          } else {
            $('#status-name').html('<span class="online_icons"></span>');
          }
          $('#conversation-card').show();
          loadMessages(receiverUsername);
        }
        $('.contacts').on('click', '.contact-item', function() {
          $('.contact-item').removeClass('active'); 
          $(this).addClass('active'); 
          var contactId = $(this).data('id');
          var receiverFullName = $(this).data('fullname'); 
          var receiverUsername = $(this).data('username'); 
          var statusname = $(this).data('status');
          localStorage.setItem('activeContactId', contactId);
          $('input[name="receiver_name"]').val(receiverUsername);
          $('#receiver-name').text(receiverFullName);
          if (statusname === 'online') {
            $('#status-name').html('<span class="online_icon"></span>');
          } else {
            $('#status-name').html('<span class="online_icons"></span>');
          }
          $('#conversation-card').show();
          loadMessages(receiverUsername);
        });
        setInterval(function() {
          var activeContactId = localStorage.getItem('activeContactId');
          if (activeContactId) {
            var receiverUsername = $('.contact-item[data-id="' + activeContactId + '"]').data('username');
            loadMessages(receiverUsername); 
          }
        }, 2000);
        $('.send_btn').on('click', function() {
          $('#conversation-card').show();
        });
        function loadMessages(receiverUsername) {
          var $msgCardBody = $('.msg_card_body');
          var scrollHeightBefore = $msgCardBody.prop('scrollHeight');
          var scrollTopBefore = $msgCardBody.scrollTop();
          var isAtBottom = (scrollTopBefore + $msgCardBody.innerHeight() >= scrollHeightBefore - 10);
          $.ajax({
            url: 'fetch_messages.php',
            type: 'GET',
            data: { receiver_name: receiverUsername, sender_id: <?php echo json_encode($sender_id); ?> },
            success: function(data) {
              $msgCardBody.html(data);
              var scrollHeightAfter = $msgCardBody.prop('scrollHeight');
              if (isAtBottom) {
                $msgCardBody.animate({ scrollTop: scrollHeightAfter }, 1000);
              } else {
                $msgCardBody.scrollTop(scrollTopBefore + (scrollHeightAfter - scrollHeightBefore));
              }
            },
            error: function() {
              alert("Error loading messages.");
            }
          });
        }
      });
    </script>
  </body>
</html>
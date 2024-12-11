<?php
  session_start();
  if ($_SESSION['Role'] != 'user') {
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
    $sql = "INSERT INTO message (sender_name, receiver_name, message, message_status, timestamp) VALUES ('$sender_id','$receiver_name','$message', '$mstatus', '$timestamp')";
    if ($conn->query($sql) === TRUE) {
      $insertSuccess = true;
      
      // Update unread messages to read for the sender
      $updateSql = "UPDATE message SET message_status = 'read' WHERE receiver_name = '$sender_id' AND message_status = 'unread'";
      $conn->query($updateSql);
      
    } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
  }
  $sql = "SELECT * FROM user WHERE account ='admin' ";
  $result = $conn->query($sql);
  $contact = [];
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $contact[] = $row;
    }
  }
  $user = $row['user'];
  
  function getUnreadMessageCount($username, $receiver) {
    global $conn;
    $sql = "SELECT COUNT(*) as unread_count FROM message WHERE sender_name = ? AND receiver_name = ? AND message_status = 'unread'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $receiver);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data['unread_count'];
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
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
      padding-top: 0px;
      position: relative;
      z-index: 1;
      margin-left: 0;
      transition: all 0.5s ease;
      overflow-x: hidden;
      width: 100%;
    
    }
    .home-section.active {
      margin-left: 250px;
    }
    .chat{
      width: 100%;
      max-width: 800px;
      
      padding: 10px;
    }
    .card{
      height: calc(100vh - 200px);
      border-radius: 15px !important;
      background-color: rgba(0,0,0,0.4) !important;
      display: flex;
      flex-direction: column;
    }
    .contacts_body{
      padding:  0.75rem 0 !important;
      overflow-x: auto;
      overflow-y: hidden;
      white-space: nowrap;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: thin;
      -ms-overflow-style: auto;
    }
    .contacts_body::-webkit-scrollbar {
      height: 3px;
      display: block;
    }
    .contacts_body::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.05);
      border-radius: 20px;
    }
    .contacts_body::-webkit-scrollbar-thumb {
      background: linear-gradient(to right, 
        rgba(255, 255, 255, 0.2), 
        rgba(255, 255, 255, 0.3)
      );
      border-radius: 20px;
      transition: background 0.3s ease;
    }
    .contacts_body::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(to right, 
        rgba(255, 255, 255, 0.3), 
        rgba(255, 255, 255, 0.4)
      );
    }
    .msg_card_body{
      height: calc(100% - 120px);
      overflow-y: auto !important;
      overflow-x: hidden !important;
      padding: 15px;
      background-color: rgba(0, 0, 0, 0.4);
    }
    .msg_card_body::-webkit-scrollbar {
      width: 5px;
    }
    .msg_card_body::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.1);
    }
    .msg_card_body::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.3);
      border-radius: 10px;
    }
    .card-header{
      border-radius: 15px 15px 0 0 !important;
      border-bottom: 0 !important;
      flex-shrink: 0;
    }
   .card-footer{
    border-radius: 0 0 15px 15px !important;
      border-top: 0 !important;
      flex-shrink: 0;
      padding: 10px;
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
   
    .contacts{
      list-style: none;
      padding: 0;
    }
    .contacts li{
      width: 100% !important;
      padding: 5px 10px;
      margin-bottom: 5px !important;
    }
    .active{
      background-color: rgba(0,0,0,0.3);
    }
    .user_img{
      height: 50px;
      width: 50px;
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
      height: 50px;
      width: 70px;
    }
    .img_cont_msg{
      height: 40px;
      width: 40px;
    }
    .online_icon{
      position: absolute;
      height: 20px;
      width:20px;
      background-color: #4cd137;
      border-radius: 50%;
      bottom: 1.5em;
      right: 1em;
      border:1.5px solid white;
      color:black;
      font-size: 10px;
      justify-content: center;
      align-items: center;
    }
    .online_icons {
      position: absolute;
      height: 20px;
      width:20px;
      background-color: #c23616;
      border-radius: 50%;
      bottom: 1.5em;
      right: 1em;
      border:1.5px solid white;
      color:white;
      font-size: 10px;
      justify-content: center;
      align-items: center;
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
      font-size: 13px;
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
     
    }
    .contacts li.contact-item {
      cursor: pointer; 
    }
    .contacts li.contact-item:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }
    .contact-item.active {
      background-color: rgba(255, 255, 255, 0.1);
    }
    .notification {
      position: fixed;
      top: 20px;
      right: 20px;
      background-color: #f8d7da;
      color: #721c24;
      padding: 15px;
      border: 1px solid #f5c6cb;
      border-radius: 5px;
      z-index: 1000;
      animation: fadeIn 0.5s;
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    .contacts_card {
      height: 100px !important;
      padding: 3px 0;
      margin-bottom: 20px;
      overflow: hidden;
    }

    .contacts {
      display: inline-flex;
      flex-direction: row;
      gap: 15px;
      padding: 0 10px;
      margin: 0;
      height: 100%;
      align-items: center;
    }

    .contacts_body {
      padding: 0 !important;
      overflow-x: auto;
      overflow-y: hidden;
      white-space: nowrap;
      height: 100%;
      -webkit-overflow-scrolling: touch;
    }

    .contacts_body::-webkit-scrollbar {
      height: 5px;
    }

    .contacts_body::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.1);
    }

    .contacts_body::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.3);
      border-radius: 10px;
    }

    .contacts li {
      width: auto !important;
      padding: 5px;
      margin-bottom: 5px !important;
      text-align: center;
      display: inline-block;
      flex-shrink: 0;
    }

    .img_cont {
      position: relative;
      height: 50px;
      width: 50px;
      margin: 0 auto;
    }

    .user_info {
      margin-top: 5px;
      text-align: center;
      margin-left: 0;
      white-space: normal;
    }

    .user_info span {
      font-size: 12px;
      display: block;
    }

    .user_info p {
      display: none; /* Hide status text since we're using icons */
    }

    .online_icon,
    .online_icons {
      bottom: 0;
      right: 0;
    }

    /* Adjust contact item layout */
    .contact-item .d-flex {
      flex-direction: column;
      align-items: center;
      width: 70px;
    }
    @media only screen and (max-width: 720px) {
      .chat{
        width: 95%;
       
      }
      .card{
        width: 100%;
        margin-left: 0;
      }
      .contacts_card {
        height: 180px;
        display: block;
      }
      .home-section {
        padding-top: 0px;
        margin-left: 0;
        width: 100%;
      }
      .home-section.active {
        margin-left: 0;
      }
    }
    .container-fluid {
      margin-left: 100px;
      transition: all 0.5s ease;
    }
    .home-section.active .container-fluid {
      margin-left: 0;
    }
    @media only screen and (max-width: 720px) {
      .container-fluid {
        margin-left: 0;
      }
    }
    .msg_cotainer, 
    .msg_cotainer_send {
      max-width: 80%;
      word-wrap: break-word;
    }
    @media only screen and (max-width: 720px) {
      form{
        margin-bottom: 50px;
      }
      .chat {
        width: 100%;  /* Changed from 95% */
        margin: 0;    /* Changed from 10px auto */
        padding: 0px; /* Reduced padding */
      }

      .card {
        height: calc(100vh - 80px); /* Reduced from 100px to show more content */
        margin: 0;
        border-radius: 0 !important; /* Remove border radius on mobile */
        width: 100%;
        display: flex;
        flex-direction: column;
      }

      .contacts_card {
        height: 100px !important; /* Keep consistent height */
        margin-bottom: 10px;
      }

      .home-section {
        padding: 0;
        padding-top: 50px; /* Reduced top padding */
        width: 100%;
        top: 0px;
        margin-top: 0px;
        padding-top: 0px;
      }

      .container-fluid {
        padding: 0; /* Remove padding */
        margin: 0;  /* Remove margin */
      }

      /* Adjust message containers for mobile */
      .msg_cotainer, 
      .msg_cotainer_send {
        max-width: 85%; /* Slightly wider messages */
        padding: 8px;   /* Reduced padding */
      }

      /* Adjust input area for mobile */
      .card-footer {
        padding: 5px;
        position: relative;
        bottom: 0;
        width: 100%;
        background-color: rgba(0,0,0,0.4);
      }

      .type_msg {
        height: 40px !important; /* Slightly reduced height */
      }

      .send_btn p {
        height: 40px; /* Match input height */
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .input-group {
        margin: 0;
        height: 40px;
      }

      /* Adjust contact items for mobile */
      .contacts li {
        padding: 3px;
      }

      .img_cont {
        height: 40px;  /* Smaller images */
        width: 40px;
      }

      .user_img {
        height: 40px;
        width: 40px;
      }

      .user_info span {
        font-size: 11px; /* Smaller font */
      }

      /* Adjust scrollbars for mobile */
      .contacts_body::-webkit-scrollbar {
        height: 3px;
      }

      .msg_card_body::-webkit-scrollbar {
        width: 3px;
      }
    }
    .ellipsis {
  display: inline-block;
  max-width: 7ch; /* Limits to 7 characters (ch unit is based on the width of the character '0') */
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}


    /* Additional adjustments for very small screens */
    @media only screen and (max-width: 480px) {
      
      .card {
        height: calc(100vh - 70px); /* Even smaller for very small screens */
      }

      .msg_card_body {
        height: calc(100% - 90px);
      }
    }
  </style>
  <body>
    <?php include 'navigation.php'; ?>
    <section class="home-section">
      <div class="container-fluid">
        <!-- Contacts Section -->
        <div class="chat">
          <div class="card mb-sm-3 mb-md-0 contacts_card">
            <div class="card-body contacts_body">
            <ul class="contacts">
    <?php foreach ($contact as $c): ?>
        <?php
            // Listahan ng mga random na larawan
            $images = [
                "../img/c_avatar/avatar1.gif",
                "../img/c_avatar/avatar2.gif",
                "../img/c_avatar/avatar3.gif"
            ];
            // Random na pumili ng larawan mula sa listahan
            $randomImage = $images[array_rand($images)];
        ?>
        <li class="contact-item" 
            data-id="<?php echo $c['id']; ?>" 
            data-fullname="<?php echo htmlspecialchars($c['fname'] . ' ' . $c['mname'] . ' ' . $c['lname']); ?>" 
            data-username="<?php echo htmlspecialchars($c['user']); ?>" 
            data-status="<?php echo htmlspecialchars($c['status']); ?>">
            <div class="d-flex bd-highlight">
                <div class="img_cont">
                    <img id="randomImage" src="<?php echo $randomImage; ?>" class="rounded-circle user_img">
                    <?php
                $unreadCount = getUnreadMessageCount($c['user'], $sender_id, $conn);
                if ($c['status'] == "online") {
                    echo '<span class="online_icon">' . $unreadCount . '</span>';
                } else {
                    echo '<span class="online_icons">' . $unreadCount . '</span>';
                }
                ?>
                </div>
                <div class="user_info">
                <span class="ellipsis"><?php echo htmlspecialchars($c['fname'] . ' ' . $c['lname']); ?></span>

                </div>
            </div>
        </li>
    <?php endforeach; ?>
</ul>

            </div>
            
          </div>
        </div>
        
        <!-- Chatbox Section -->
        <div class="chat">
          <div class="card" id="conversation-card" style="display: none;">
            <div class="card-header msg_head">
              <!-- <div class="d-flex bd-highlight">
                <div class="img_cont">
                  <img src="../img/sbmo.png" class="rounded-circle user_img">
                  <div id="status-name"></div>
                </div>
                <div class="user_info">
                  <span id="receiver-name">Customer Service</span>
                </div>
              </div> -->
              <h5 class="card-title" style="color: white;" id="receiver-name">Costumer Service</h5>
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

        // Add this new code for navigation toggle
        let sidebar = document.querySelector(".sidebar");
        let homeSection = document.querySelector(".home-section");
        
        $('.sidebarBtn').click(function() {
          sidebar.classList.toggle("active");
          if(sidebar.classList.contains("active")) {
            homeSection.classList.add("active");
          } else {
            homeSection.classList.remove("active");
          }
        });
      });
      
    </script>
  </body>
</html>
<?php
  include '../connection/conn.php';
  $id = isset($_GET['id']) ? $_GET['id'] : '';
  if ($id === '') {
    echo '<script>
      Swal.fire({
        icon: "warning",
        title: "Please Login Again",
        text: "Your session has expired or is invalid. Please log in again.",
        confirmButtonColor: "#3085d6",
        confirmButtonText: "OK"
      }).then(() => {
        window.location.href = "logout1.php";
      });
    </script>';
    exit;
  }
  $sql = "SELECT * FROM user WHERE user = ?";
  $stmt = $conn->prepare($sql);
  if ($stmt === false) {
    echo '<script>
      Swal.fire({
        icon: "error",
        title: "Database Error",
        text: "Failed to prepare the SQL statement.",
        confirmButtonColor: "#3085d6",
        confirmButtonText: "OK"
      }).then(() => {
        window.location.href = "logout1.php";
      });
    </script>';
    exit;
  }
  $stmt->bind_param("s", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $stmt->close();
  if (!$row) {
    echo '<script>
      Swal.fire({
        icon: "warning",
        title: "Please Login Again",
        text: "Your session has expired or is invalid. Please log in again.",
        confirmButtonColor: "#3085d6",
        confirmButtonText: "OK"
      }).then(() => {
        window.location.href = "logout1.php";
      });
    </script>';
    exit;
  }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <title>SABAT MO!</title>
    <link rel="icon" type="image/png" href="../img/sbmo.png">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  </head>
  <style type="text/css">
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap');
  *{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins" , sans-serif;
  }
  body{
    background: #1a1a2e;
  }
  .sidebar{
    position: fixed;
    left: 0;
    height: 100%;
    width: 78px;
    background: rgba(17, 16, 29, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    padding: 6px 14px;
    z-index: 99;
    transition: all 0.5s ease;
  }
  .sidebar.open{
    width: 250px;
  }
  .sidebar .logo-details{
    height: 60px;
    display: flex;
    align-items: center;
    position: relative;
  }
  .sidebar .logo-details .icon{
    opacity: 0;
    transition: all 0.5s ease;
  }
  .sidebar .logo-details .logo_name{
    color: #fff;
    font-size: 20px;
    font-weight: 600;
    opacity: 0;
    transition: all 0.5s ease;
  }
  .sidebar.open .logo-details .icon,
  .sidebar.open .logo-details .logo_name{
    opacity: 1;
  }
  .sidebar .logo-details #btn{
    position: absolute;
    top: 50%;
    right: 0;
    transform: translateY(-50%);
    font-size: 22px;
    transition: all 0.4s ease;
    font-size: 23px;
    text-align: center;
    cursor: pointer;
    transition: all 0.5s ease;
  }
  .sidebar.open .logo-details #btn{
    text-align: right;
  }
  .sidebar i{
    color: #fff;
    height: 60px;
    min-width: 50px;
    font-size: 28px;
    text-align: center;
    line-height: 60px;
  }
  .sidebar .nav-list{
    margin-top: 20px;
    height: 100%;
  }
  .sidebar li{
    position: relative;
    margin: 8px 0;
    list-style: none;
  }
  .sidebar li .tooltip{
    position: absolute;
    top: -20px;
    left: calc(100% + 15px);
    z-index: 3;
    background: #fff;
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.3);
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 15px;
    font-weight: 400;
    opacity: 0;
    white-space: nowrap;
    pointer-events: none;
    transition: 0s;
  }
  .sidebar li:hover .tooltip{
    opacity: 1;
    pointer-events: auto;
    transition: all 0.4s ease;
    top: 50%;
    transform: translateY(-50%);
  }
  .sidebar.open li .tooltip{
    display: none;
  }
  .sidebar input{
    font-size: 15px;
    color: #FFF;
    font-weight: 400;
    outline: none;
    height: 50px;
    width: 100%;
    width: 50px;
    border: none;
    border-radius: 12px;
    transition: all 0.5s ease;
    background: #1d1b31;
  }
  .sidebar.open input{
    padding: 0 20px 0 50px;
    width: 100%;
  }
  .sidebar .bx-search{
    position: absolute;
    top: 50%;
    left: 0;
    transform: translateY(-50%);
    font-size: 22px;
    background: #1d1b31;
    color: #FFF;
  }
  .sidebar.open .bx-search:hover{
    background: #1d1b31;
    color: #FFF;
  }
  .sidebar .bx-search:hover{
    background: #FFF;
    color: #11101d;
  }
  .sidebar li a{
    display: flex;
    height: 100%;
    width: 100%;
    border-radius: 12px;
    align-items: center;
    text-decoration: none;
    transition: all 0.4s ease;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(4px);
    margin: 8px 0;
  }
  .sidebar li a:hover{
    background: rgba(255, 255, 255, 0.15);
    transform: translateX(5px);
    box-shadow: 0 0 15px rgba(255, 255, 255, 0.3),
                inset 0 0 15px rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
  }
  .sidebar li a .links_name{
    color: #fff;
    font-size: 15px;
    font-weight: 400;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: 0.4s;
  }
  .sidebar.open li a .links_name{
    opacity: 1;
    pointer-events: auto;
  }
  .sidebar li a:hover .links_name,
  .sidebar li a:hover i{
    transition: all 0.5s ease;
    color: #11101D;
  }
  .sidebar li i{
    height: 50px;
    line-height: 50px;
    font-size: 18px;
    border-radius: 12px;
  }
  .sidebar li.profile{
    position: fixed;
    height: 60px;
    width: 78px;
    left: 0;
    bottom: -8px;
    padding: 10px 14px;
    background: #1d1b31;
    transition: all 0.5s ease;
    overflow: hidden;
  }
  .sidebar.open li.profile{
    width: 250px;
  }
  .sidebar li .profile-details{
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
  }
  .sidebar li img{
    height: 45px;
    width: 45px;
    object-fit: cover;
    border-radius: 6px;
    margin-right: 10px;
  }
  .sidebar li.profile .name,
  .sidebar li.profile .job{
    font-size: 15px;
    font-weight: 400;
    color: #fff;
    white-space: nowrap;
  }
  .sidebar li.profile .job{
    font-size: 12px;
  }
  .sidebar .profile #log_out{
    position: absolute;
    top: 50%;
    right: 0;
    transform: translateY(-50%);
    background: #1d1b31;
    width: 100%;
    height: 60px;
    line-height: 60px;
    border-radius: 0px;
    transition: all 0.5s ease;
  }
  .sidebar.open .profile #log_out{
    width: 50px;
    background: none;
  }
  .home-section{
    position: relative;
    min-height: 100vh;
    top: 0;
    left: 78px;
    width: calc(100% - 78px);
    transition: all 0.5s ease;
    z-index: 2;
  }
  .sidebar.open ~ .home-section{
    left: 250px;
    width: calc(100% - 250px);
  }
  .home-section .text{
    display: inline-block;
    color: #11101d;
    font-size: 25px;
    font-weight: 500;
    margin: 18px
  }
  .home-section{
    left: 0;
    width: 100%; 
  }
  .tooltip{
    display: none;
  }
  .sidebar li i{
    height: 30px;
    line-height: 30px;
    font-size: 18px;
    border-radius: 12px;
  }
  .sidebar .nav-list{
    margin-top: 0;
    height: 100%;
  }
  .sidebar li{
    position: relative;
    margin: 8px 0;
    list-style: none;
  }
  i{
    padding-top: 0px;
  }
  @media (max-width: 768px) {
    .links_name{
      display: none;
    }
    .sidebar {
      position: fixed; 
      bottom: 0; 
      left: 0;
      width: 100%; 
      height: 45px; 
      background: rgba(17, 16, 29, 0.95);
      backdrop-filter: blur(10px);
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
      z-index: 99;
    }
    .sidebar .logo-details {
      display: none; 
    }
    .sidebar .nav-list {
      display: flex;
      justify-content: space-around;
      width: 100%;
      margin-bottom: 0px;
    }
    .sidebar li {
      list-style: none;
      text-align: center;
      padding: 5px;
    }
    .sidebar li a {
      display: block;
      text-decoration: none;
      color: #fff;
      font-size: 14px;
      transition: all 0.3s ease;
      height: 30px;
      margin-top: -7px;
    }
    .sidebar li a:hover {
      color: #fff;
    }
    .sidebar li i {
      font-size: 24px;
      line-height: 20px;
    }
    .sidebar .profile {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
    .sidebar .profile #log_out {
      display: block;
      font-size: 18px;
      color: #fff;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    .sidebar .profile #log_out:hover {
      color: #FFF;
    }
    i{
      padding-top: 5px;
    }
    
  }
  

  .sidebar, .sidebar li a, .sidebar i, .links_name {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  /* Glowing icon on hover */
  .sidebar li a:hover i {
    color: #fff;
    text-shadow: 0 0 10px rgba(255, 255, 255, 0.8),
                 0 0 20px rgba(255, 255, 255, 0.8);
  }
  /* Glowing text on hover */
  .sidebar li a:hover .links_name {
    color: #fff;
    text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
  }
  /* Smooth transitions */
  .sidebar li a, .sidebar li i, .sidebar li .links_name {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .mail-badge{
    display: none; 
    background-color: red; 
    color: white; 
    border-radius: 50%;
    width: 20px; 
    height: 20px; 
    margin-top: 5px; 
    margin-right: 1px; 
    padding-left: 8px; 
    justify-content: center; 
    align-items: center; 
    font-size: 12px; 
    position: absolute; 
    top: 0; 
    right: 0;
  }
  </style>
  <body>
    <div class="sidebar">
      <div class="logo-details">
        <i class='bx bx-car icon'></i>
        <div class="logo_name">Sabat Mo!</div>
        <i class='bx bx-menu' id="btn" ></i>
      </div>
      <ul class="nav-list">
        <li>
          <a href="dashboard.php?id=<?php echo $id ?>">
            <i class='bx bxs-dashboard'></i>
            <span class="links_name">Dashboard</span>
          </a>
          <span class="tooltip">Dashboard</span>
        </li>
        <li>
          <a href="message.php?id=<?php echo $id ?>">
            <i class='bx bx-envelope'></i>
            <span class="links_name">Mail</span>
            <span class="mail-badge">0</span>
            </a>
          <span class="tooltip">Mail</span>
        </li>
        <li>
          <a href="about.php?id=<?php echo $id ?>">
            <i class='bx bx-info-circle' ></i>
            <span class="links_name">About</span>
          </a>
          <span class="tooltip">About</span>
        </li>
        <li>
          <a href="settings.php?id=<?php echo $id ?>">
            <i class="bx bx-user"></i>
            <span class="links_name">Profile</span>
          </a>
        </li>
        <li>
          <a href="logout.php?id=<?php echo $id ?>">
            <i class='bx bx-log-out' ></i>
            <span class="links_name">Logout</span>
          </a>
          <span class="tooltip">Logout</span>
        </li>
      </ul>
    </div>
    <script>
      let sidebar = document.querySelector(".sidebar");
      let closeBtn = document.querySelector("#btn");
      let searchBtn = document.querySelector(".bx-search");
      closeBtn.addEventListener("click", ()=>{
        sidebar.classList.toggle("open");
        menuBtnChange();
      });
      searchBtn.addEventListener("click", ()=>{
        sidebar.classList.toggle("open");
        menuBtnChange();
      });
      function menuBtnChange() {
        if(sidebar.classList.contains("open")){
          closeBtn.classList.replace("bx-menu", "bx-menu-alt-right");
        }else {
          closeBtn.classList.replace("bx-menu-alt-right","bx-menu");
        }
      }
    </script>
    <script>
      document.addEventListener('contextmenu', function (event) {
        event.preventDefault();
      });
    </script>
    <script>
      function fetchUnreadMessages() {
        const userId = '<?php echo $id; ?>'; // Get the user ID from PHP
        fetch(`fetch_unread_messages.php?id=${userId}`)
          .then(response => response.json())
          .then(data => {
            const unreadCount = data.unread_count;
            const badge = document.querySelector('.mail-badge');
            if (unreadCount > 0) {
              badge.textContent = unreadCount;
              badge.style.display = 'inline-block';
            } else {
              badge.style.display = 'none';
            }
          })
          .catch(error => console.error('Error fetching unread messages:', error));
      }

      // Fetch unread messages every 1 seconds
      setInterval(fetchUnreadMessages, 1000);
      fetchUnreadMessages(); // Initial fetch
    </script>
  </body>
</html>
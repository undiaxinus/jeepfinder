<?php 
    session_start();
    if ($_SESSION['Role'] != 'admin') {
        header('Location: ../index.html?error=Access denied'); 
        exit();
    }
    include "../connection/conn.php";
    $sql = "SELECT * FROM `user` ";
    $result = $conn->query($sql);
    $sql = "SELECT COUNT(*) AS jeep FROM `locate` ";
    $results = $conn->query($sql);
    if ($results->num_rows > 0) {
        $rows = $results->fetch_assoc();
        $jeep = $rows['jeep'];
    } else {
        $jeep = 0;
    }
    $sql = "SELECT COUNT(*) AS status FROM `user` ";
    $results = $conn->query($sql);
    if ($results->num_rows > 0) {
        $rows = $results->fetch_assoc();
        $user = $rows['status'];
    } else {
        $user = 0;
    }
    $sql = "SELECT COUNT(*) AS online FROM `user` WHERE status = 'online' ";
    $results = $conn->query($sql);
    if ($results->num_rows > 0) {
        $rows = $results->fetch_assoc();
        $online = $rows['online'];
    } else {
        $online = 0;
    }
    $sql = "SELECT COUNT(*) AS offline FROM `user` WHERE status = 'offline' ";
    $results = $conn->query($sql);
    if ($results->num_rows > 0) {
        $rows = $results->fetch_assoc();
        $offline = $rows['offline'];
    } else {
        $offline = 0;
    }
    $conn->close();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SABAT MO</title>
        <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet-draggable/dist/leaflet-draggable.css" />
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
        <script src="https://unpkg.com/leaflet-draggable/dist/leaflet-draggable.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/osmbuildings@4.0.0/dist/OSMBuildings-Leaflet.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            .home-section {
                max-width: 100%;
            }
            .border {
                margin-left: 90px;
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 10px;
                justify-items: center;
            }
            .border1 {
                margin-left: 70px;
                display: grid;
                grid-template-columns: repeat(1, 1fr);
                gap: 10px;
                justify-items: center;
            }
            a {
                text-decoration: none;
                color: #ffffff;
            }
            .card {
                opacity: 80%;
                margin-top: 30px;
                background-color: #ffffff;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                padding: 20px;
                text-align: center;
                width: 95%;
                transition: transform 0.3s ease;
            }
            .card1 {
                opacity: 80%;
                margin-top: 30px;
                background-color: red;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                text-align: center;
                width: 95%;
                transition: transform 0.3s ease;
            }
            .card1 h2{
                margin-top:70px
            }
            .border i {
                margin-left: -60px;
                opacity: 50%;
                position: absolute;
                font-size: 110px;
                margin-top: -30px;
                border-radius: 50%;
                padding: 10px;
                justify-content: center;
                align-items: center;
                filter: grayscale(50%);
                transition: transform 0.3s ease;
            }
            .card:hover, .border i:hover {
                transform: scale(1.05);
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
                background-color: #fff;
                table-layout: fixed;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            th {
                background-color: #f2f2f2;
                color: #333;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            tr:hover {
                background-color: #f2f2f2;
            }
            .search-container {
                margin-bottom: 10px;
            }
            .search-container input {
                width: 15%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 14px;
                margin-left: 140vh;
                margin-top: 2vh;
                right: 50px;
                top: 180px; 
                position: absolute; 
                z-index:100;
            }
            .btn {
                padding: 5px 10px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 5px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 14px;
                cursor: pointer;
            }
            .btn.edit{
                width: 100%;
                margin-bottom: 5px;
            }
            .btn.delete {
                background-color: #f44336;
                width: 100%;
            }
            .addemergency {
                position: fixed;
                bottom: 20px;
                right: 20px; 
                z-index: 2000;
            }
            .addemergency details {
                position: relative;
            }
            .addemergency summary {
                list-style: none;
                cursor: pointer;
                font-size: 9px;
            }
            .addemergency ul {
                display: none;
                list-style: none;
                padding: 0;
                margin: 0;
            }
            .addemergency details[open] ul {
                width: 250px;
                display: block;
                position: absolute;
                background-color: #ffffff;
                border: 1px solid #ddd;
                border-radius: 5px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                top: -80px;
                right: 40px;
                z-index: 1000;
            }
            .addemergency ul li {
                padding: 10px;
                border-bottom: 1px solid #ddd;
            }
            .addemergency ul li:last-child {
                border-bottom: none;
            }
            .addemergency ul li:hover {
                background-color: #f0f0f0;
            }
            .addemergency i{
                font-size: 50px;
                color:  #007bff;
            }
            @media only screen and (max-width: 768px) {
                .border {
                    margin-left: 10px;
                    grid-template-columns: repeat(2, 1fr);
                }
                .border1 {
                    margin-left: 0px;
                    grid-template-columns: repeat(1, 1fr);
                }
                .card {
                    opacity: 80%;
                    margin-top: 30px;
                    background-color: #ffffff;
                    border-radius: 10px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    padding: 20px;
                    text-align: center;
                    width: 95%;
                    transition: transform 0.3s ease;
                }
                .border i {
                    margin-left: -75px;
                    opacity: 50%;
                    position: absolute;
                    font-size: 120px;
                }
                .search-container input {
                    width: 20%;
                    padding: 5px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    font-size: 10px;
                    margin-left: 80%;
                    margin-top: 2vh;
                    right: 30px;
                    top: 380px;
                }
                .card h2 {
                    font-size: 20px;
                }
                .card p {
                    font-size: 15px;
                }
                .card1 {
                    padding: 5px;
                    padding-top: 5px;
                    padding-bottom: 0px;
                    background-color: red;
                    width: 100%;
                }
                .card1 h2 {
                    font-size: 15px;
                    margin-top: 20px;
                }
                th, td {
                    font-size: 10px;
                    text-overflow: ellipsis; 
                    white-space: nowrap; 
                }
                .btn {
                    padding: 1px 5px;
                    background-color: #4CAF50;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    text-align: center;
                    text-decoration: none;
                    display: inline-block;
                    font-size: 10px;
                    cursor: pointer;
                }
                .addemergency {
                    bottom: 70px;
                    right: 20px; 
                    position: fixed;
                }
            }
        </style>
    </head>
    <body>
        <?php include "navigation.php" ?>
        <section class="home-section">
            <div class="border">
                <a href="users.php?id=<?php echo $id ?>">
                    <div class="card" style="background-color: blue;">
                        <i class='bx bxs-group'></i>
                        <h2>Users <?php echo $user ?></h2>
                        <p>This is the content of total account.</p>
                    </div>
                </a>
                <a href="online.php?id=<?php echo $id ?>">
                    <div class="card" style="background-color: green;">
                        <i class='bx bxs-user'></i>
                        <h2>Online <?php echo $online ?></h2>
                        <p>This is the content of total account online.</p>
                    </div>
                </a>
                <a href="offline.php?id=<?php echo $id ?>">
                    <div class="card" style="background-color: red;">
                        <i class='bx bxs-ghost'></i>
                        <h2>Offline <?php echo $offline ?></h2>
                        <p>This is the content of total account offline.</p>
                    </div>
                </a>
                <a href="dashboard.php?id=<?php echo $id ?>">
                    <div class="card" style="background-color: orange;">
                        <i class='bx bxs-car'></i>
                        <h2>Jeepney <?php echo $jeep ?></h2>
                        <p>This is the content of total jeepney.</p>
                    </div>
                </a>
            </div>
            <div class="search-container">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search..">
            </div>
            <div class="border1">
                <div class="card1">
                    <h2 style="color: #fff">Users Offline</h2>
                    <div class="table-container">
                        <table id="jeepneyTable">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>User ID</th>
                                <th>Password</th>
                                <th>Account ID</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr> 
                        </table>
                        <hr style="border: 2px black solid">
                    </div> 
                </div>
            </div>
            <div class="addemergency" id="toggleAddEmergency">
                <details id="addEmergencyDetails">
                    <summary><i class='bx bxs-plus-circle'></i></summary>
                    <ul>
                        <li><a href='Add_Account.php?id=<?php echo $id ?>' style="color:black;"> Add Account</a></li>
                        <li><a href='Add_Jeepney.php?id=<?php echo $id ?>' style="color:black;">Add Jeepney</a></li>
                    </ul>
                </details>
            </div>
            <br><br><br><br><br><br>
        </section>
    </body>
    <script>
        let isAddEmergencyEnabled = false;
        function fetchTableData() {
            let searchInput = document.getElementById('searchInput').value.trim();
            if (searchInput === "") {
                let xhr = new XMLHttpRequest();
                xhr.open('GET', 'offline_fetch.php?id=<?php echo $id; ?>', true);
                xhr.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('jeepneyTable').getElementsByTagName('tbody')[0].innerHTML = this.responseText;
                    }
                };
                xhr.send();
            }
        }
        function searchTable() {
            let input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("jeepneyTable");
            tr = table.getElementsByTagName("tr");
            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        }
                    }
                }
            }
        }
        document.getElementById('toggleAddEmergency').addEventListener('change', function() {
            isAddEmergencyEnabled = this.checked;
        });
        fetchTableData();
        setInterval(fetchTableData, 1000);
    </script>
</html>

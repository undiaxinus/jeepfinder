<?php
session_start();
if($_SESSION['Role'] != 'admin'){
    header('Location: ../index.html?error=Access denied');
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SABAT MO</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style type="text/css">
            .home-section {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh; 
            }
            .container {
                background-color: rgba(0,0,0,0.4) !important;
                padding: 20px;
                border-radius: 10px;
                max-width: 800px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                width: 90%;
                margin-bottom: 50px;
            }
            .title {
                color: #ff0000;
                font-size: 32px;
                font-weight: bold;
                margin-top: 0;
                text-align: center;
            }
            .subtitle {
                font-size: 18px;
                font-weight: bold;
                margin: 20px 0;
                text-align: center;
            }
            .content {
                text-align: justify;
                font-size: 16px;
                line-height: 1.5;
            }
            .content img {
                float: left;
                margin-right: 20px;
                margin-bottom: 10px;
                max-width: 40%;
                height: auto;
            }
            .rating {
                text-align: center;
                margin: 20px 0;
            }
            .rating p {
                margin: 10px 0;
            }
            .rating i {
                color: gold;
                font-size: 30px;
                margin: 0 5px;
            }
            .footer {
                margin-top: 20px;
                background-color: #333; 
                color: #fff; 
                padding: 10px;
                border-radius: 0 0 10px 10px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
            }
            .footer .social {
                text-align: center;
                flex: 1;
            }
            .footer .social p {
                margin: 5px 0;
            }
            .footer .social i {
                color: #fff;
                font-size: 30px;
                margin: 0 10px;
            }
            .footer .qr {
                text-align: center;
                flex: 1;
            }
            .footer .qr img {
                width: 80px;
                height: 80px;
            }
            .footer .qr p {
                margin: 5px 0;
            }
            @media (max-width: 768px) {
                .content img {
                    float: none;
                    display: block;
                    margin: 0 auto 20px auto;
                    max-width: 80%;
                }
                .footer {
                    flex-direction: column;
                    align-items: center;
                }
                .footer .social {
                    margin-bottom: 10px;
                }
                .footer .social i {
                    font-size: 24px;
                    margin: 0 5px;
                }
                .footer .qr img {
                    width: 60px;
                    height: 60px;
                }
            }
            @media (max-width: 480px) {
                .title {
                    font-size: 24px;
                }
                .subtitle {
                    font-size: 16px;
                }
                .content {
                    font-size: 14px;
                }
                .rating i {
                    font-size: 24px;
                }
                .footer .social i {
                    font-size: 20px;
                    margin: 0 3px;
                }
                .footer .qr img {
                    width: 50px;
                    height: 50px;
                }
            }
        </style>
    </head>
    <body>
        <?php include "navigation.php" ?>
        <section class="home-section">
            <div class="container">
                <div class="title"></div>
                <div class="content">
                    <img src="../img/sbmo.png" alt="Jeepney Image">
                    <div class="subtitle">"STAY ON TRACK IN LEGAZPI: REAL-TIME MONITORING FOR SMOOTHER RIDES."</div>
                    Implementing a Real-Time Location and Passenger Monitoring System for Legazpi’s Jeepneys, this mobile application enhances passengers' overall experience in Jeepneys by providing them with real-time information and features that contribute to a more informed, efficient, and comfortable journey. Powered by GPS technology and onboard sensors for passenger counting, this mobile application provides passengers with instant updates on jeepney location and available seating capacity, ensuring an excellent and informed travel experience. By using these technologies, both passengers and operators can optimize their journey planning, leading to improved efficiency, reduced wait times, and enhanced satisfaction for all users in Legazpi's transportation network.
                </div>
                <div class="rating">
                    <p>HOW IS YOUR EXPERIENCE?</p>
                    <p>Please rate us</p>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star" style="color: gray;"></i>
                </div>
                <div class="footer">
                    <div class="social">
                        <p>FOR MORE INFO<br>VISIT US ON:</p>
                        <i class="fab fa-facebook-f"></i>
                        <i class="fab fa-instagram"></i>
                        <i class="fab fa-youtube"></i>
                    </div>
                    <div class="qr">
                        <p>DOWNLOAD APP NOW</p>
                        <img src="../img/QR.png" alt="QR Code">
                    </div>
                </div>
            </div>
        </section>
    </body>
</html>

<?php
session_start();
if($_SESSION['Role'] != 'user'){
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
            padding: 20px;
            background: rgba(0, 0, 0, 0.1);
        }
        .container {
            background: rgba(17, 16, 29, 0.95) !important;
            padding: 30px;
            border-radius: 20px;
            max-width: 800px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            width: 90%;
            margin-bottom: 30px;
        }
        .title {
            background: linear-gradient(45deg, #ff3366, #ff0000);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 30px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .subtitle {
            color: #fff;
            font-size: 20px;
            font-weight: bold;
            margin: 25px 0;
            text-align: center;
            line-height: 1.4;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        .content {
            color: #fff;
            text-align: justify;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        .content img {
            float: left;
            margin-right: 30px;
            margin-bottom: 20px;
            max-width: 40%;
            height: auto;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }
        .content img:hover {
            transform: scale(1.02);
        }
        .rating {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 15px;
            margin: 30px 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .rating p {
            color: #fff;
            margin: 10px 0;
            font-size: 18px;
        }
        .rating i {
            color: #ffd700;
            font-size: 30px;
            margin: 0 5px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .rating i:hover {
            transform: scale(1.2);
        }
        .footer {
            margin-top: 30px;
            background: rgba(0, 0, 0, 0.3);
            color: #fff;
            padding: 25px;
            border-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .footer .social {
            text-align: center;
            flex: 1;
        }
        .footer .social p {
            margin: 10px 0;
            font-size: 16px;
            letter-spacing: 1px;
        }
        .footer .social i {
            color: #fff;
            font-size: 24px;
            margin: 0 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .footer .social i:hover {
            transform: translateY(-3px);
            color: #ff3366;
        }
        .footer .qr {
            text-align: center;
            flex: 1;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .footer .qr img {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            margin: 10px 0;
            transition: transform 0.3s ease;
        }
        .footer .qr img:hover {
            transform: scale(1.05);
        }
        .footer .qr p {
            margin: 10px 0;
            font-size: 16px;
            color: #fff;
        }
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            .content img {
                float: none;
                display: block;
                margin: 0 auto 20px auto;
                max-width: 90%;
            }
            .footer {
                flex-direction: column;
                padding: 20px;
            }
            .footer .social, .footer .qr {
                width: 100%;
                margin: 10px 0;
            }
            .footer .qr img {
                width: 80px;
                height: 80px;
            }
        }
        @media (max-width: 480px) {
            .title {
                font-size: 28px;
            }
            .subtitle {
                font-size: 18px;
            }
            .content {
                font-size: 15px;
            }
            .rating i {
                font-size: 24px;
            }
            .footer .social i {
                font-size: 20px;
                margin: 0 10px;
            }
            .footer .qr img {
                width: 70px;
                height: 70px;
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

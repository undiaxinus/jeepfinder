<?php
session_start();
if($_SESSION['Role'] != 'admin'){
    header('Location: ../index.html?error=Access denied');
    exit;
}
$user_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
include '../connection/conn.php';
$alert_message = '';
$average_rating = 0; // Variable to store the average rating

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rate = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);

    if ($rate !== false && $rate >= 1 && $rate <= 5 && $comment !== false) {
        $stmt = $conn->prepare("INSERT INTO ratings (user_id, rate, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $user_id, $rate, $comment);

        if ($stmt->execute()) {
            $alert_message = "Thank you for your feedback!";
        } else {
            $alert_message = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $alert_message = "Invalid input. Please try again.";
    }
}

// Fetch all ratings for the product and calculate the average rating
$stmt = $conn->prepare("SELECT rate FROM ratings");
$stmt->execute();
$result = $stmt->get_result();
$total_ratings = 0;
$rating_count = 0;

while ($row = $result->fetch_assoc()) {
    $total_ratings += $row['rate'];
    $rating_count++;
}

// Calculate the average rating if there are any ratings
if ($rating_count > 0) {
    $average_rating = round($total_ratings / $rating_count, 1); // Round to one decimal place
} else {
    $average_rating = 0; // Set to 0 or some default value when there are no ratings
}

$stmt->close();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SABAT MO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            width: 100%;
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
        .read-comments-btn {
    display: inline-block;
    margin-top: 15px;
    padding: 10px 20px;
    background: linear-gradient(45deg, #ff3366, #ff0000);
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: transform 0.3s ease;
}

.read-comments-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255, 51, 102, 0.3);
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
        .rating-stars {
    display: flex;
    gap: 5px;
}

.feedback-section {
    margin-top: 20px;
    display: none;
}

.feedback-section textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    resize: vertical;
}

.feedback-section button {
    display: block;
    width: 100%;
    padding: 10px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 10px;
}

.feedback-section button:hover {
    background-color: #45a049;
}


.rating-stars input {
    display: none; /* Hide the radio buttons */
}

.rating-stars label {
    font-size: 2rem;
    color: gray;
    cursor: pointer;
    transition: color 0.2s;
}

.rating-stars input:checked ~ label {
    color: gray; /* Reset color after selection */
}

.rating-stars input:checked + label,
.rating-stars input:checked ~ label:hover {
    color: gold; /* Highlight selected star and those before it */
}
.rating-stars:hover .Star-1.Checked {Background Blue}
.transition.animation**Hope your implementation works

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
                <form action="" method="POST">
                    <!-- Radio buttons for star rating -->
                    <div class="rating-stars">
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1" class="fas fa-star"></label>

                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2" class="fas fa-star"></label>

                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3" class="fas fa-star"></label>

                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4" class="fas fa-star"></label>

                        <input type="radio" id="star5" name="rating" value="5">
                        <label for="star5" class="fas fa-star"></label>
                    </div>

                    <div id="feedback-section" class="feedback-section">
                    <textarea id="comment" name="comment" placeholder="Your comment here..." rows="4"></textarea>
                    <button id="submit-button" type="submit">Submit</button>
                </div>
                </form>
                    <div class="average-rating">
                        <p>Average Rating: 
                            <?php echo $average_rating ? $average_rating : 'No ratings yet'; ?> / 5
                        </p>
                        <div class="stars" style="pointer-events: none;">
                            <?php
                                $total_stars = 5;
                                $full_stars = floor($average_rating); // Whole stars
                                $half_star = ($average_rating - $full_stars) >= 0.5 ? 1 : 0; // Half star
                                $empty_stars = $total_stars - $full_stars - $half_star;

                                // Display full stars
                                for ($i = 0; $i < $full_stars; $i++) {
                                    echo '<i class="fas fa-star"></i>';
                                }

                                // Display half star
                                if ($half_star) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                }

                                // Display empty stars
                                for ($i = 0; $i < $empty_stars; $i++) {
                                    echo '<i class="far fa-star"></i>';
                                }
                            ?>
                        </div>
                          <a href="view_comments.php?id=<?php echo $id ?>" class="read-comments-btn">Read Comments</a>
                    </div>
                </div>

            <div class="footer">
                <div class="social">
                    <p>FOR MORE INFO<br>VISIT US ON:</p>
                    <i class="fab fa-facebook-f"></i>
                    <i class="fab fa-instagram"></i>
                    <i class="fab fa-youtube"></i>
                </div>
                 <a href="download.php" style="text-decoration: none;">
                <div class="qr">
                    <p>DOWNLOAD APP NOW</p>
                    <img src="../img/jeepfinderqr.png" alt="JeepFinder QR Code">
                </div>
                </a>
            </div>
        </div>
    </section>
    <?php if ($alert_message): ?>
                    <script>
                        Swal.fire({
                            icon: 'info',
                            title: 'Feedback',
                            text: "<?= $alert_message; ?>",
                        });
                    </script>
                <?php endif; ?>
</body>
<script>
  const stars = document.querySelectorAll('.rating .fa-star');
  const feedbackSection = document.getElementById('feedback-section');

  stars.forEach((star, index) => {
      star.addEventListener('click', () => {
          // Set all stars to gray initially
          stars.forEach(s => s.style.color = 'gray');

          // Set the selected stars to gold
          stars.forEach((s, i) => {
              if (i <= index) {
                  s.style.color = 'gold';
              }
          });

          // Show the feedback section
          feedbackSection.style.display = 'block';
      });
  });
</script>
</html>

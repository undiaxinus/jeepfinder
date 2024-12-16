<?php
session_start();
if($_SESSION['Role'] != 'admin'){
    header('Location: ../index.html?error=Access denied');
    exit;
}
include '../connection/conn.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Comments - SABAT MO</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: rgba(0, 0, 0, 0.1);
            margin: 0;
            padding: 10px;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1000px;
            margin: 10px auto;
            background: rgba(17, 16, 29, 0.95);
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            width: 90%;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .back-btn {
            padding: 8px 15px;
            background: linear-gradient(45deg, #ff3366, #ff0000);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: transform 0.3s ease;
            font-size: 14px;
        }

        .back-btn:hover {
            transform: translateY(-2px);
        }

        h1 {
            color: white;
            text-align: center;
            margin: 10px 0;
            font-size: 24px;
            width: 100%;
        }

        .comments-container {
            display: grid;
            gap: 15px;
        }

        .comment-box {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .read-comments-btn {
    display: inline-block;
    margin-top: 10px;
    padding: 8px 15px;
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

        .rating-display {
            margin-bottom: 10px;
        }

        .rating-display i {
            color: gold;
            margin-right: 2px;
        }

        .comment-text {
            color: white;
            font-size: 14px;
            line-height: 1.4;
            margin: 8px 0;
            word-wrap: break-word;
        }

        .comment-date {
            color: #888;
            font-size: 12px;
            text-align: right;
        }

        .no-comments {
            color: white;
            text-align: center;
            padding: 20px;
        }

        /* Media Queries for Responsive Design */
        @media screen and (max-width: 768px) {
            .container {
                padding: 10px;
                margin: 5px auto;
            }

            .header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .back-btn {
                margin-bottom: 10px;
            }

            h1 {
                font-size: 20px;
                margin: 5px 0;
            }

            .comment-box {
                padding: 10px;
            }

            .rating-display i {
                font-size: 16px;
            }
        }

        @media screen and (max-width: 480px) {
            body {
                padding: 5px;
            }

            .container {
                width: 95%;
            }

            h1 {
                font-size: 18px;
            }

            .comment-text {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <?php include "navigation.php" ?>
    <section class="home-section">
    <div class="container">
        <div class="header">
            <h1>User Comments</h1>
        </div>

        <div class="comments-container">
            <?php
            // Fetch all ratings and comments
            $stmt = $conn->prepare("SELECT * FROM ratings");
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="comment-box">';
                    echo '<div class="rating-display">';
                    // Display stars based on rating
                    for ($i = 0; $i < 5; $i++) {
                        if ($i < $row['rate']) {
                            echo '<i class="fas fa-star"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    echo '</div>';
                    echo '<div class="comment-text">' . htmlspecialchars($row['comment']) . '</div>';
                    
                    echo '</div>';
                }
            } else {
                echo '<div class="no-comments">No comments yet.</div>';
            }
            $stmt->close();
            ?>
        </div>
    </div>
    </section>
</body>
</html> 
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
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1000px;
            margin: 20px auto;
            background: rgba(17, 16, 29, 0.95);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .back-btn {
            padding: 10px 20px;
            background: linear-gradient(45deg, #ff3366, #ff0000);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: transform 0.3s ease;
        }

        .back-btn:hover {
            transform: translateY(-2px);
        }

        h1 {
            color: white;
            text-align: center;
            margin: 0;
        }

        .comments-container {
            display: grid;
            gap: 20px;
        }

        .comment-box {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
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

        .rating-display {
            margin-bottom: 10px;
        }

        .rating-display i {
            color: gold;
            margin-right: 2px;
        }

        .comment-text {
            color: white;
            font-size: 16px;
            line-height: 1.6;
            margin: 10px 0;
        }

        .comment-date {
            color: #888;
            font-size: 14px;
            text-align: right;
        }

        .no-comments {
            color: white;
            text-align: center;
            padding: 20px;
        }
    </style>
</head>
<body>
    <?php include "navigation.php" ?>
    <section class="home-section">
    <div class="container">
        <div class="header">
            <a href="about.php?id=<?php echo $id ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h1>User Comments</h1>
            <div style="width: 100px;"></div> <!-- Spacer for centering -->
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
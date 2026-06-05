<?php

session_start();

require 'database.php';

/** @var mysqli $conn */

if (!isset($_GET['id'])) {

    header("Location: homePage.php");
    exit();
}

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$productID = $_GET['id'];
$userID = $_SESSION['userID'];

//Product Item stuff
$query = "SELECT p.Product_ID, p.Product_Name, pd.FullDescription, pd.Price, pd.Quantity, pd.Rating, pd.ImageDir, pd.DetailsID FROM tblproduct p JOIN tblproductdetails pd ON p.Product_ID = pd.ProductID WHERE p.Product_ID = ?";



$stmt = $conn->prepare($query);
$stmt->bind_param("s", $productID);
$stmt->execute();
$result = $stmt->get_result();
$product = mysqli_fetch_assoc($result);



//Comment section
$commentQuery = "SELECT * FROM tblrating WHERE DetailsID = ?";
$stmtComment = $conn->prepare($commentQuery);
$stmtComment->bind_param("s", $product['DetailsID']);
$stmtComment->execute();
$commentResult = $stmtComment->get_result();



//Calculate average rating
$averageQuery = "SELECT AVG(rating) AS avgRating, COUNT(rating) AS totalReviews FROM tblrating WHERE DetailsID = ?";
$stmtAverage = $conn->prepare($averageQuery);
$stmtAverage->bind_param("s", $product['DetailsID']);
$stmtAverage->execute();
$averageResult = $stmtAverage->get_result();
$average = mysqli_fetch_assoc($averageResult);
$averageRating = !is_null($average['avgRating']) ? round($average['avgRating'], 1) : 0;


if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'addRating') {

    $rating = $_POST['rating'];
    $comment = $_POST['addComment'];
    $detailID = $_POST['detailsID'];

    //check if user is a buyer
    $buyerQuery = "SELECT b.BuyerID, u.username FROM tblbuyer b JOIN tbluser u ON b.userID = u.userID WHERE b.userID = ?";
    $stmtBuyer = $conn->prepare($buyerQuery);
    $stmtBuyer->bind_param("i", $userID);
    $stmtBuyer->execute();
    $buyerResult = $stmtBuyer->get_result();
    $buyer = mysqli_fetch_assoc($buyerResult);

    if ($buyer != null) {

        // Generate Rating ID
        $query = "SELECT ratingID FROM tblrating ORDER BY ratingID DESC LIMIT 1"; // Fetch the last inserted cat ID
        $result = $conn->query($query);
        $lastRow = $result->fetch_assoc();

        if (!$lastRow) {
            $newRatingID = "RA0001"; // Start with BY0001 if no buyers exist
        } else {
            $currentID = $lastRow['ratingID']; // Get the current highest buyer ID

            //Use substr to remove BY by stating the string and number of characters to remove from the front
            $numbers = substr($currentID, 2); // Extract the numeric part of BY0001 to 0001
            $convertToInt = (int)$numbers; // Convert the numeric part to an integer
            $convertToInt++; // Increment the integer by 1
            $newRatingID = "RA" . str_pad($convertToInt, 4, "0", STR_PAD_LEFT); // Pad the incremented number with leading zeros and concatenate with BY to form the new buyer ID

        }


        $ratingQuery = "INSERT INTO tblrating (ratingID, DetailsID, BuyerID, username, rating, comment) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtRating = $conn->prepare($ratingQuery);
        $stmtRating->bind_param("ssssis", $newRatingID, $detailID, $buyer['BuyerID'], $buyer['username'], $rating, $comment);
        $stmtRating->execute();
    }
}

?>



<!DOCTYPE html>

<html lang="en">



<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="Styles/viewStyles.css">
    <script src="JavaScript/viewPage.js" defer></script>
    <title>View Product</title>

</head>
<body>

    <section class="mainForm">

        <div class="panel">

            <a href="homePage.php">
                <button type="button" class="btn-close" aria-label="Close"></button>
            </a>
        </div>
        <h1 id="productTitle">Product Name</h1>

        <div class="container">

            <div class="box">

                <img src="<?php echo $product['ImageDir']; ?>" class="productImage" alt="<?php echo $product['Product_Name']; ?>" id="imgProduct">

            </div>

            <div class="box">

                <h2><?php echo $product['Product_Name']; ?></h2>
                <p><?php echo $product['FullDescription']; ?></p>
                <hr>
                <h3>R <?php echo $product['Price']; ?></h3>
                <hr>
                <p>product rating : ⭐<?php echo $averageRating ?></p>
                <!--Add to cart feature-->

                <form action="homePage.php" method="POST">

                    <input type="hidden" name="productID" value="<?php echo $productID; ?>">
                    <input type="hidden" name="action" value="addToCart">
                    <button type="submit" class="btn btn-secondary">Add to cart</button>

                </form>

            </div>

        </div>

        <div>

            <!--Creating tabs-->

            <div class="tab">

                <button class="tablinks" onclick="tabSetting(event, 'comments')" id="defaultTab">Comments</button>
                <script>
                    document.getElementById("defaultTab").click();
                </script>
                <button class="tablinks" onclick="tabSetting(event, 'addComment')">Add Rating</button>

            </div>

            <!--Content inside tabs-->

            <div id="comments" class="tabcontent">

                <div class="">

                    <h3>Comment Section</h3>
                    <?php if ($commentResult->num_rows > 0) { ?>
                        <?php while ($comment = mysqli_fetch_assoc($commentResult)) { ?>
                            <p>
                                <hr><?php echo $comment['username']; ?> | ⭐<?php echo $comment['rating']; ?> <br> <?php echo $comment['comment']; ?>
                                <hr>
                            </p>
                        <?php } ?>
                    <?php } ?>
                </div>

            </div>

            <div id="addComment" class="tabcontent">

                <Form action="viewPage.php?id=<?php echo $productID; ?>" method="post">
                    <input type="hidden" name="action" value="addRating">
                    <input type="hidden" name="detailsID" value="<?php echo $product['DetailsID']; ?>">
                    <h3>Rating</h3>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="rating" id="inlineRadio1" value="1">
                        <label class="form-check-label" for="inlineRadio1">1⭐</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="rating" id="inlineRadio2" value="2">
                        <label class="form-check-label" for="inlineRadio2">2⭐</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="rating" id="inlineRadio3" value="3">
                        <label class="form-check-label" for="inlineRadio3">3⭐</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="rating" id="inlineRadio4" value="4">
                        <label class="form-check-label" for="inlineRadio4">4⭐</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="rating" id="inlineRadio5" value="5">
                        <label class="form-check-label" for="inlineRadio5">5⭐</label>
                    </div>

                    <div class="mb-3">
                        <label for="exampleFormControlTextArea1" class="form-label">Comment</label>
                        <textarea class="form-control" name="addComment" id="exampleFormControlTextArea1" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Comment</button>

                </Form>

            </div>

        </div>

    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>



</html>
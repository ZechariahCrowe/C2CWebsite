<?php
//session start

session_start();

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$username = $_SESSION['username'];

require 'database.php';
/** @var mysqli $conn */
//-----------------------------------

// Category section
$categoryQuery = "SELECT * FROM tblcategories";
$categoryResult = mysqli_query($conn, $categoryQuery);

// Get selected category, defaults to first if non selected
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

//Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($search != '') {

    $query = "SELECT p.Product_ID, p.Product_Name, pd.FullDescription, pd.ImageDir FROM tblproduct p JOIN tblproductdetails pd ON p.Product_ID = pd.ProductID WHERE p.Product_Name LIKE ?";
    $stmt = $conn->prepare($query);
    $searchParam = "%" . $search . "%"; //Wild cards
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
} else {

    //Get prodycts filtered by catgeory
    if ($selectedCategory != '') {
        $query = "SELECT p.Product_ID, p.Product_Name, pd.FullDescription, pd.ImageDir FROM tblproduct p JOIN tblproductdetails pd ON p.Product_ID = pd.ProductID WHERE p.Product_Category = ? LIMIT 20";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $selectedCategory);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $query = "SELECT p.Product_ID, p.Product_Name, pd.FullDescription, pd.ImageDir FROM tblproduct p JOIN tblproductdetails pd ON p.Product_ID = pd.ProductID LIMIT 20";
        $result = mysqli_query($conn, $query);
    }
}

// populating cards
// Select ID, Name, Description and image directory from tblProducts and tblProductDetails
$query = "SELECT p.Product_ID, p.Product_Name, pd.FullDescription, pd.ImageDir FROM tblproduct p JOIN tblproductdetails pd ON p.Product_ID = pd.ProductID ORDER BY p.Product_ID DESC LIMIT 10";
$results = mysqli_query($conn, $query);

//Cart section

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'addToCart') { // tests if theres a post request, if its an action and if the action is addToCart from the view product page

    $productID = $_POST['productID'];

    //generate seller id
    $query = "SELECT CartID FROM tblcart ORDER BY CartID DESC LIMIT 1"; // Fetch the last inserted seller ID
    $result = $conn->query($query);
    $lastRow = $result->fetch_assoc();

    if (!$lastRow) {
        $newCartID = "CT0001"; // Start with SL0001 if no sellers exist
    } else {
        $currentID = $lastRow['CartID']; // Get the current highest Seller ID
        //Use substr to remove SL by stating the string and number of characters to remove from the front
        $numbers = substr($currentID, 2); // Extract the numeric part of SL0001 to 0001
        $convertToInt = (int)$numbers; // Convert the numeric part to an integer
        $convertToInt++; // Increment the integer by 1
        $newCartID = "CT" . str_pad($convertToInt, 4, "0", STR_PAD_LEFT); // Pad the incremented number with leading zeros and concatenate with SL to form the new seller ID
    }

    //insert into the cart table
    $cartQuery = "INSERT INTO tblcart (CartID, userID, ProductID) VALUES (?, ? ,?)";
    $stmtCart = $conn->prepare($cartQuery);
    $stmtCart->bind_param("sss", $newCartID, $userID, $productID);
    $stmtCart->execute();

    header("Location: homePage.php");
    exit();
}

//Cart to off canvas section
$cartDisplayQuery = "SELECT c.CartID, c.ProductID, p.Product_Name, pd.Price, pd.ImageDir FROM tblcart c JOIN tblproduct p ON c.ProductID = p.Product_ID JOIN tblproductdetails pd ON c.ProductID = pd.ProductID WHERE c.userID = ?";
$stmtCartItems = $conn->prepare($cartDisplayQuery);
$stmtCartItems->bind_param("s", $userID);
$stmtCartItems->execute();
$cartItems = $stmtCartItems->get_result();

// declare total variable
$total = 0;

//Delete items from cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'removeItem') {

    $cartID = $_POST['CartID'];
    $removeItems = "DELETE FROM tblcart WHERE CartID = ?";
    $stmtRemoveItems = $conn->prepare($removeItems);
    $stmtRemoveItems->bind_param("s", $cartID);
    if ($stmtRemoveItems->execute()) {
        echo "Item deleted";
    } else {
        echo "Item not found";
    }

    header("Location: homePage.php");
    exit();
}

//Confirm purchase section
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'confirmPurchase') {

    //Buyer ID
    $buyerIDQuery = "SELECT BuyerID FROM tblbuyer WHERE userID = ?";
    $stmtBuyerID = $conn->prepare($buyerIDQuery);
    $stmtBuyerID->bind_param("i", $userID);
    $stmtBuyerID->execute();

    $buyerIDResult = $stmtBuyerID->get_result();
    $buyerData = mysqli_fetch_assoc($buyerIDResult);
    $buyerID = $buyerData['BuyerID'];

    //Cart Items
    $cartQuery = "SELECT c.ProductID, p.Seller_ID FROM tblcart c JOIN tblproduct p ON c.ProductID = p.Product_ID WHERE c.userID = ?";
    $stmtCart = $conn->prepare($cartQuery);
    $stmtCart->bind_param("i", $userID);
    $stmtCart->execute();
    $cartResult = $stmtCart->get_result();
    $cartItems = $cartResult->fetch_all(MYSQLI_ASSOC);

    //Generate receipt ID
    $query = "SELECT Receipt_ID FROM tblreceipt ORDER BY Receipt_ID DESC LIMIT 1"; // Fetch the last inserted seller ID
    $result = $conn->query($query);
    $lastRow = $result->fetch_assoc();

    if (!$lastRow) {
        $newReceiptID = "RC0001"; // Start with SL0001 if no sellers exist
    } else {
        $currentID = $lastRow['Receipt_ID']; // Get the current highest Seller ID
        //Use substr to remove SL by stating the string and number of characters to remove from the front
        $numbers = substr($currentID, 2); // Extract the numeric part of SL0001 to 0001
        $convertToInt = (int)$numbers; // Convert the numeric part to an integer
        $convertToInt++; // Increment the integer by 1
        $newReceiptID = "RC" . str_pad($convertToInt, 4, "0", STR_PAD_LEFT); // Pad the incremented number with leading zeros and concatenate with SL to form the new seller ID
    }

    //Get seller ID from first Cart Item
    $sellerID = $cartItems[0]['Seller_ID'] ?? null;

    //Insert into tblreceipt
    $insertReceiptQuery = "INSERT INTO tblreceipt (Receipt_ID, Seller_ID, Buyer_ID) VALUES (?, ?, ?)";
    $stmtReceipt = $conn->prepare($insertReceiptQuery);
    $stmtReceipt->bind_param("sss", $newReceiptID, $sellerID, $buyerID);
    $stmtReceipt->execute();

    //Insert rows into tblreceiptdetails
    foreach ($cartItems as $cartItem) {
        $detailsQuery = "SELECT DetailsID FROM tblreceiptdetails ORDER BY DetailsID DESC LIMIT 1";
        $detailsResult = $conn->query($detailsQuery);
        $lastDetail = $detailsResult->fetch_assoc();

        if (!$lastDetail) {
            $newDetailsID = "DT0001";
        } else {
            $numbers = substr($lastDetail['DetailsID'], 2);
            $convertToInt = (int)$numbers;
            $convertToInt++;
            $newDetailsID = "DT" . str_pad($convertToInt, 4, "0", STR_PAD_LEFT);
        }

        $insertDetails = "INSERT INTO tblreceiptdetails (DetailsID, ReceiptID, ProductID) VALUES (?, ?, ?)";
        $stmtDetails = $conn->prepare($insertDetails);
        $stmtDetails->bind_param("sss", $newDetailsID, $newReceiptID, $cartItem['ProductID']);
        $stmtDetails->execute();
    }

    //Clear cart after purchase
    $clearCartQuery = "DELETE FROM tblcart WHERE userID = ?";
    $stmtClear = $conn->prepare($clearCartQuery);
    $stmtClear->bind_param("i", $userID);
    $stmtClear->execute();

    header("Location: homePage.php");
    exit();
}


?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="Styles/homeStyles.css">
    <title>Home Page</title>
</head>

<body>
    <header>

        <div>

            <ul class="navbar">
                <li><img src="images/logo.png" alt="placeholder" class="logo"></li>
                <li><a href="homePage.php">Home</a></li>
            </ul>

        </div>

        <div>

            <ul class="navbar">
                <li><a href="login.php">Login</a></li>
                <li><a href="register.html">Register</a></li>
                <li><a class="btn btn-primary" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample"><img src="images/grocery-store.png" alt="shopping cart" class="shoppingCart"></a></li>
            </ul>

        </div>
    
    </header>

    <!--Off canvas section-->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">

        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasExampleLabel">Shopping Cart</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">

            <div>
                These are the items in your cart
            </div>

            <?php if ($cartItems->num_rows > 0) { ?>
                <?php while ($item = mysqli_fetch_assoc($cartItems)) { ?>
                    <?php $total += $item['Price'] ?>
                    <div class="card w-75 mb-3">

                        <div class="card-body">
                            <h5 class="card-title"><?php echo $item['Product_Name'] ?></h5>
                            <p class="card-text">R <?php echo $item['Price'] ?></p>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                <a href="viewPage.php?id=<?php echo $item['ProductID'] ?>" class="btn btn-primary">View</a>

                                <form action="homePage.php" method="POST">
                                    <input type="hidden" name="CartID" value="<?php echo $item["CartID"]; ?>">
                                    <input type="hidden" name="action" value="removeItem">
                                    <button class="btn btn-danger">Remove</button>
                                </form>

                            </div>

                        </div>

                    </div>
                <?php } ?>
            <?php  } else { ?>
                <p>No items available</p>
            <?php  } ?>

            <hr>

            <h3>Total Price: R<?php echo number_format($total, 2) ?></h3> <!--Display total with 2 decimals-->
            
            <hr>
            <form action="homePage.php" method="POST">
                <input type="hidden" name="action" value="confirmPurchase">
                <button class="btn btn-primary" type="submit">Confirm Purchase</button>
            </form>

        </div>

    </div>



    <section class="topSection">

        <nav class="navbar bg-body-tertiary">

            <div class="container-fluid">

                <a class="navbar-brand">

                    <div class="dropdown">

                        <button class="dropbtn"><img src="images/menu.png" alt="menu"></button>

                        <div class="dropdown-content">
                            <a href="account.php">Account</a>
                            <a href="logout.php">Logout</a>
                        </div>

                    </div>

                </a>

                <form class="d-flex" action="homePage.php" role="search" method="GET">
                    <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" name="search">
                    <button class="btn btn-outline-success" type="submit">Search</button>
                </form>

            </div>

        </nav>

    </section>

    <!-- Placeholder for new products -->
    <section class="categories">
        <h2>What's New</h2>

        <div class="container">

            <?php while ($row = mysqli_fetch_assoc($results)) {  ?>

                <div class="card" style="width: 18rem;">
                    <img src="<?php echo $row['ImageDir']; ?>" class="card-img-top" alt="<?php echo $row['Product_Name']; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $row['Product_Name']; ?></h5>
                        <p class="card-text"><?php echo $row['FullDescription']; ?></p>
                        <a href="viewPage.php?id=<?php echo $row['Product_ID']; ?>" class="btn btn-primary">View</a>
                    </div>

                </div>

            <?php } ?>

        </div>

    </section>



    <section class="seperator"> <!--Put an advertisement over here-->

        <div id="carouselExample" class="carousel slide">

            <div class="carousel-inner">

                <div class="carousel-item active">
                    <img src="Images/ad1.png" alt="">
                </div>
                <div class="carousel-item">
                    <img src="Images/ad2.png" alt="">
                </div>
                <div class="carousel-item">
                    <img src="Images/placeholder.jpg" alt="">
                </div>

            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>

            <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>

        </div>

    </section>

    <section class="categories">

        <form method="GET" action="">

            <!--Dropdown to sort according to category-->
            <label for="category">Select a category</label>
            <select name="category" id="category" onchange="this.form.submit()">
                <option value="">All Catgeories</option>
                <?php while ($cat = mysqli_fetch_assoc($categoryResult)) { ?>
                    <option value="<?php echo $cat['CategoryName']; ?>" <?php echo ($selectedCategory == $cat['CategoryName']) ? 'selected' : ''; ?>>
                        <?php echo $cat['CategoryName']; ?>
                    </option>
                <?php } ?>
            </select>

        </form>

        <div class="container">

            <?php while ($row = mysqli_fetch_assoc($result)) {  ?>
                <div class="card" style="width: 18rem;">
                    <img src="<?php echo $row['ImageDir']; ?>" class="card-img-top" alt="<?php echo $row['Product_Name']; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $row['Product_Name']; ?></h5>
                        <p class="card-text"><?php echo $row['FullDescription']; ?></p>
                        <a href="viewPage.php?id=<?php echo $row['Product_ID']; ?>" class="btn btn-primary">View</a>
                    </div>

                </div>

            <?php } ?>

        </div>

    </section>

    <footer>

        <p>c Trenches Online (Pty) Ltd.</p>

    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>



</html>
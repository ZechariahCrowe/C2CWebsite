<?php
//Session
session_start();
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

require 'database.php'; //connect file
/** @var mysqli $conn */
// Extract the categories
$queryCategory = "SELECT * FROM tblcategories";
$result = mysqli_query($conn, $queryCategory);
$userID = $_SESSION['userID'];


if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$status = "";
$statusType = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //Variables
    $productName = $_POST['productName'];
    $option = $_POST['category'];
    $amount = floatval($_POST['amount']);
    $quantity = intval($_POST['quantity']);
    $description = $_POST['description'];

    //Dummy Info
    //$sellerid = 'SL0001';
    //$productid = 'PT0001';
    //$detailsid = 'DL0001';

    //get seller id
    $sellerQuery = "SELECT SellerID FROM tblsellerfinal WHERE userID = ?";
    $stmtSellerID = $conn->prepare($sellerQuery);
    $stmtSellerID->bind_param('i', $userID);
    $stmtSellerID->execute();
    $sellerResult = $stmtSellerID->get_result();
    $sellerData = mysqli_fetch_assoc($sellerResult);
    $sellerID = $sellerData['SellerID'] ?? NULL;



    //Generate product and detail ID
    $query = "SELECT Product_ID FROM tblproduct ORDER BY Product_ID DESC LIMIT 1"; // Fetch the last inserted buyer ID
    $result = $conn->query($query);
    $lastRow = $result->fetch_assoc();

    if (!$lastRow) {
        $newProductID = "PT0001"; // Start with BY0001 if no buyers exist
    } else {
        $currentID = $lastRow['Product_ID']; // Get the current highest buyer ID

        //Use substr to remove BY by stating the string and number of characters to remove from the front
        $numbers = substr($currentID, 2); // Extract the numeric part of BY0001 to 0001

        $convertToInt = (int)$numbers; // Convert the numeric part to an integer

        $convertToInt++; // Increment the integer by 1

        $newProductID = "PT" . str_pad($convertToInt, 4, "0", STR_PAD_LEFT); // Pad the incremented number with leading zeros and concatenate with BY to form the new buyer ID

    } // I made use of W3Schools PHP string functions to assist : https://www.w3schools.com/php/php_ref_string.asp

    //$buyerID = "BY1"; // TEMPORARY FIX: Generate a unique buyer ID (you can implement a better logic for this)

    $query = "SELECT DetailsID FROM tblproductdetails ORDER BY DetailsID DESC LIMIT 1"; // Fetch the last inserted buyer ID
    $result = $conn->query($query);
    $lastRow = $result->fetch_assoc();

    if (!$lastRow) {
        $newDetailsID = "DL0001"; // Start with BY0001 if no buyers exist
    } else {
        $currentID = $lastRow['DetailsID']; // Get the current highest buyer ID

        //Use substr to remove BY by stating the string and number of characters to remove from the front
        $numbers = substr($currentID, 2); // Extract the numeric part of BY0001 to 0001

        $convertToInt = (int)$numbers; // Convert the numeric part to an integer

        $convertToInt++; // Increment the integer by 1

        $newDetailsID = "DL" . str_pad($convertToInt, 4, "0", STR_PAD_LEFT); // Pad the incremented number with leading zeros and concatenate with BY to form the new buyer ID


        //File attemp
        $file_name = $_FILES['uploadImage']['name'];
        $tempname = $_FILES['uploadImage']['tmp_name'];
        $folder = 'productImages/' . $file_name;

        //$query1 = mysqli_query($conn, "INSERT INTO tblProducts (Product_ID, 'Product_Name,' 'Product_Category', 'Seller_ID') VALUES ($productid, )");
        $stmt = $conn->prepare("INSERT INTO tblproduct (Product_ID, Product_Name, Product_Category, Seller_ID) VALUES (?, ?, ?, ?)"); // insert username, email and password into tbluser
        $stmt->bind_param("ssss", $newProductID, $productName, $option, $sellerID);

        if ($stmt->execute()) {
            $stmt = $conn->prepare("INSERT INTO tblproductdetails (DetailsID, ProductID, FullDescription, Price, Quantity, ImageDir) VALUES (?, ?, ?, ?, ?, ?)"); // insert username, email and password into tbluser
            $stmt->bind_param("sssdis", $newDetailsID, $newProductID, $description, $amount, $quantity, $folder);

            if ($stmt->execute()) {

                if (move_uploaded_file($tempname, $folder)) {
                    $status = "Product Successful Added";
                    $statusType = "Success";
                }
            } else {
                $status = "Something Went Wrong";
                $statusType = "Fail";
            }
        } else {
            $status = "Something Went Wrong";
            $statusType = "Fail";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <link rel="stylesheet" href="Styles/productPage.css">
    <script src="JavaScript/productPage.js" defer></script>
    <title>Product Page</title>
</head>

<body>
    <section class="mainForm">
        <div class="panel">
            <a href="account.php">
                <button type="button" class="btn-close" aria-label="Close"></button>
            </a>
        </div>
        <h1>Add Product</h1>
        <form action="productPage.php" method="POST" enctype="multipart/form-data">

            <!--Name-->
            <div class="input-group mb-3">
                <span class="input-group-text" id="inputGroup-sizing-default">Product Name</span>
                <input type="text" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default" name="productName">
            </div>

            <!--Dropdown-->
            <div class="input-group mb-3">
                <label class="input-group-text" for="inputGroupSelect01">Options</label>
                <select class="form-select" id="inputGroupSelect01" name="category">
                    <option selected>Choose Category...</option>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <option value="<?php echo $row['CategoryID']; ?>">
                            <?php echo $row['CategoryName'] ?>
                        </option>

                    <?php } ?>
                </select>
            </div>

            <!--Amount-->
            <div class="input-group mb-3">
                <span class="input-group-text">R</span>
                <span class="input-group-text">0.00</span>
                <input type="text" class="form-control" aria-label="ZAR amount (two decimals)" name="amount">
            </div>

            <!--Image-->
            <div class="input-group mb-3">
                <label class="input-group-text" for="inputGroupFile01">Upload</label>
                <input type="file" class="form-control" id="inputGroupFile01" name="uploadImage">
            </div>

            <!--Slider Label-->
            <div>
                <label for="range4" class="form-label">Quantity Of Items</label>
                <input type="range" class="form-range" min="1" max="100" value="50" id="range4">
                <output for="range4" id="rangeValue" aria-hidden="true"></output>
                <input type="hidden" name="quantity" id="quantityValue">
            </div>

            <!--Description-->
            <div class="input-group">
                <span class="input-group-text">Description</span>
                <textarea class="form-control" aria-label="With textarea" name="description" id="description"></textarea>
            </div>

            <div class="col-12">
                <br><button class="btn btn-primary" type="submit">Confirm</button>
            </div>

            <?php if ($status != "") { ?>
                <div class="alert alert-<?php echo $statusType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $status; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

        </form>
    </section>

</body>

</html>
<?php

//Session
session_start();
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

require 'database.php';
/** @var mysqli $conn */
/** @var mysqli_result $receiptResult */

$userID = $_SESSION['userID'];
$username = $_SESSION['username'];

//userQuery
$userQuery = "SELECT u.*, b.Address, s.Bank, s.BankNumber FROM tbluser u LEFT JOIN tblbuyer b ON u.userID = b.userID LEFT JOIN tblsellerfinal s ON u.userID = s.userID WHERE u.userID = ?"; // u.* means userID across all tables and left join is used to return values even if it is null since a user an be a buy or seller or both.

$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userID);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = mysqli_fetch_assoc($userResult);

//check if user is buyer
$buyerQuery = "SELECT * FROM tblbuyer WHERE userID = ?";
$stmtBuyer = $conn->prepare($buyerQuery);
$stmtBuyer->bind_param("i", $userID);
$stmtBuyer->execute();
$BuyerResult = $stmtBuyer->get_result();
$buyerData = mysqli_fetch_assoc($BuyerResult);
$isBuyer = $buyerData != null;
$buyerID = $buyerData['BuyerID'] ?? null;

//check if user is buyer
$sellerQuery = "SELECT * FROM tblsellerfinal WHERE userID = ?";
$stmtSeller = $conn->prepare($sellerQuery);
$stmtSeller->bind_param("i", $userID);
$stmtSeller->execute();
$isSeller = $stmtSeller->get_result()->num_rows > 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Delete Product section
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'removeProduct') {

        $removeProductID = $_POST['productID'];
        echo "Removing product: " . $removeProductID;
        //Delete product details
        $detailsQuery = "DELETE FROM tblproductdetails WHERE ProductID = ?";
        $stmtDetails = $conn->prepare($detailsQuery);
        $stmtDetails->bind_param("s", $removeProductID);
        $stmtDetails->execute();

        if (!$stmtDetails->execute()) {
            echo "Details delete error: " . $stmtDetails->error;
        }

        //Delete product
        $productQuery = "DELETE FROM tblproduct WHERE Product_ID = ?";
        $stmtProducts = $conn->prepare($productQuery);
        $stmtProducts->bind_param("s", $removeProductID);
        $stmtProducts->execute();

        if (!$stmtProducts->execute()) {
            echo "Products delete error: " . $stmtProducts->error;
        }

        header("Location: account.php");
        exit();

    }

    // Edit profile section
    $editUsername = $_POST['editUsername'];
    $editEmail = $_POST['editEmail'];
    $editAddress = $_POST['editAddress'];
    $editBank = $_POST['editBank'] ?? '';
    $editBankNumber = $_POST['editBankNumber'];
    $isBuyerChecked = isset($_POST['buyer']);
    $isSellerChecked = isset($_POST['seller']);

    if ($editUsername !=  '') {

        $usernameQuery = "UPDATE tbluser SET username = ? WHERE userID = ?";
        $stmtUsername = $conn->prepare($usernameQuery);
        $stmtUsername->bind_param("ss", $editUsername, $userID);
        $stmtUsername->execute();

    }

    if ($editEmail != '') {

        $emailQuery = "UPDATE tbluser SET email = ? WHERE userID = ?";
        $stmtEmail = $conn->prepare($emailQuery);
        $stmtEmail->bind_param("ss", $editEmail, $userID);
        $stmtEmail->execute();

    }

    if ($isBuyer) {

        if ($editAddress != '') {

            $addressQuery = "UPDATE tblbuyer SET Address = ? WHERE userID = ?";
            $stmtAddress = $conn->prepare($addressQuery);
            $stmtAddress->bind_param("ss", $editAddress, $userID);
            $stmtAddress->execute();

        }

    } else {

        //Attempt to auto-generate a unique buyer ID by fetching the last inserted buyer ID and incrementing it
        if ($editAddress != '') {

            $query = "SELECT BuyerID FROM tblbuyer ORDER BY BuyerID DESC LIMIT 1"; // Fetch the last inserted buyer ID
            $result = $conn->query($query);
            $lastRow = $result->fetch_assoc();

            if (!$lastRow) {
                $newBuyerID = "BY0001"; // Start with BY0001 if no buyers exist
            } else {

                $currentID = $lastRow['BuyerID']; // Get the current highest buyer ID

                //Use substr to remove BY by stating the string and number of characters to remove from the front
                $numbers = substr($currentID, 2); // Extract the numeric part of BY0001 to 0001
                $convertToInt = (int)$numbers; // Convert the numeric part to an integer
                $convertToInt++; // Increment the integer by 1
                $newBuyerID = "BY" . str_pad($convertToInt, 4, "0", STR_PAD_LEFT); // Pad the incremented number with leading zeros and concatenate with BY to form the new buyer ID

            } // I made use of W3Schools PHP string functions to assist : https://www.w3schools.com/php/php_ref_string.asp

            $buyerQuery2 = "INSERT INTO tblbuyer (BuyerID, userID, Address) VALUES (?, ?, ?)";
            $stmtBuyer2 = $conn->prepare($buyerQuery2);
            $stmtBuyer2->bind_param("sis", $newBuyerID, $userID, $editAddress);
            $stmtBuyer2->execute();

        }

    }



    if ($isSeller) {

        if ($editBank != '' && $editBankNumber != '') {

            $bankQuery = "UPDATE tblsellerfinal SET Bank = ?, BankNumber = ? WHERE userID = ?";
            $stmtBank = $conn->prepare($bankQuery);
            $stmtBank->bind_param("sss", $editBank, $editBankNumber, $userID);

            if (!$stmtBank->execute()) {
                echo "Update Error" . $stmtBank->error;
            }

        }

    } else {

        if ($editBank != '' && $editBankNumber != '') {

            //generate seller id
            $query = "SELECT SellerID FROM tblsellerfinal ORDER BY SellerID DESC LIMIT 1"; // Fetch the last inserted seller ID
            $result = $conn->query($query);
            $lastRow = $result->fetch_assoc();

            if (!$lastRow) {
                $newSellerID = "SL0001"; // Start with SL0001 if no sellers exist
            } else {

                $currentID = $lastRow['SellerID']; // Get the current highest Seller ID

                //Use substr to remove SL by stating the string and number of characters to remove from the front
                $numbers = substr($currentID, 2); // Extract the numeric part of SL0001 to 0001
                $convertToInt = (int)$numbers; // Convert the numeric part to an integer
                $convertToInt++; // Increment the integer by 1
                $newSellerID = "SL" . str_pad($convertToInt, 4, "0", STR_PAD_LEFT); // Pad the incremented number with leading zeros and concatenate with SL to form the new seller ID

            }

            $sellerQuery2 = "INSERT INTO tblsellerfinal (SellerID, userID, Bank, BankNumber) VALUES (?, ?, ?, ?)";
            $stmtSeller2 = $conn->prepare($sellerQuery2);
            $stmtSeller2->bind_param("siss", $newSellerID, $userID, $editBank, $editBankNumber);

            if (!$stmtSeller2->execute()) {
                echo "Insert Error: " . $stmtSeller2->error;
            }

        }

    }

    // redirect to refresh the page after any updates
    header("Location: account.php");
    exit();

}





$receiptResult = null;

if ($isBuyer) {

    //Receipt section
    $receiptQuery = "SELECT r.Receipt_ID, r.Date, r.Seller_ID, p.Product_Name, pd.Price FROM tblreceipt r JOIN tblreceiptdetails rd ON r.Receipt_ID = rd.ReceiptID JOIN tblproduct p ON rd.ProductID = p.Product_ID JOIN tblproductdetails pd ON rd.ProductID = pd.ProductID WHERE r.Buyer_ID = ? ORDER BY r.Receipt_ID";
    $stmtReceipt = $conn->prepare($receiptQuery);
    $stmtReceipt->bind_param("s", $buyerID);
    $stmtReceipt->execute();
    $receiptResult = $stmtReceipt->get_result();

}



//Products section

//Check if user has a seller ID
$sellerIDQuery = "SELECT SellerID FROM tblsellerfinal WHERE userID = ?";
$stmtSellerID = $conn->prepare($sellerIDQuery);
$stmtSellerID->bind_param("i", $userID);
$stmtSellerID->execute();
$sellerResult = $stmtSellerID->get_result();
$sellerData = mysqli_fetch_assoc($sellerResult);
$sellerID = $sellerData['SellerID'] ?? null; // exctract the seller ID or null

$productResult = null;

if ($sellerID != null) {

    $productQuery = "SELECT p.Product_ID, p.Product_Name, pd.ImageDir, pd.Price, pd.FullDescription FROM tblproduct p JOIN tblproductdetails pd ON p.Product_ID = pd.ProductID WHERE p.Seller_ID = ?";
    $stmtProducts = $conn->prepare($productQuery);
    $stmtProducts->bind_param("s", $sellerID);
    $stmtProducts->execute();
    $productResult = $stmtProducts->get_result();

}

?>



<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Styles/accountStyles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="JavaScript/account.js" defer></script>

    <title>Account Page</title>

</head>



<body>



    <section class="mainForm">

        <a href="homePage.php">
            <button type="button" class="btn-close" aria-label="Close"></button>
        </a>

        <h1>Account Page</h1>
        <div class="profile">

            <img src="images/buttonPlaceholder.jpg" alt="Profile Picture" class="profilePicture">
            <h3 id="username"><?php echo $username; ?></h3><br>

        </div>

        <!--Creating tabs-->
        <div class="tab">

            <button class="tablinks" onclick="tabSetting(event, 'information')" id="defaultTab">Information</button>

            <script>
                window.addEventListener('load', function() {
                    document.getElementById("defaultTab").click();
                })

            </script>
            <button class="tablinks" onclick="tabSetting(event, 'editProfile')">Edit Profile</button>
            <button class="tablinks" onclick="tabSetting(event, 'receipt')">Check Data</button>
            <button class="tablinks" onclick="tabSetting(event, 'products')">Products</button>

        </div>

        <!--Content inside tabs-->
        <div id="information" class="tabcontent">

            <div class="userInformation">
                <h3>User Information</h3>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="inputGroup-sizing-default">Username</span>
                    <span class="form-control"><?php echo $username; ?></span>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text" id="inputGroup-sizing-default">Email</span>
                    <span class="form-control"><?php echo $userData['email']; ?></span>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text" id="inputGroup-sizing-default">Address</span>
                    <span class="form-control"><?php echo $userData['Address'] ?? 'No Address added'; ?></span>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text" id="inputGroup-sizing-default">Bank</span>
                    <span class="form-control"><?php echo $userData['Bank'] ?? 'No Bank Chosen'; ?></span>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text" id="inputGroup-sizing-default">Bank Number</span>
                    <span class="form-control"><?php echo $userData['BankNumber'] ?? 'No bank Number added'; ?></span>
                </div>

            </div>

        </div>

        <div id="editProfile" class="tabcontent">
            <h3>Edit Profile</h3>

            <form action="account.php" method="POST">

                <div class="input-group mb-3" id="usernameField">
                    <span class="input-group-text" id="inputGroup-sizing-default"><?php echo $username; ?></span>
                    <input type="text" class="form-control" placeholder="Change Username" aria-label="Username" aria-describedby="basic-addon1" name="editUsername">
                </div>

                <div class="input-group mb-3" id="emailField">
                    <span class="input-group-text" id="inputGroup-sizing-default"><?php echo $userData['email']; ?></span>
                    <input type="text" class="form-control" placeholder="Change Email" aria-label="Username" aria-describedby="basic-addon1" name="editEmail">
                </div>

                <!--Select buyer or seller or both-->
                <div class="d-flex gap-2 mb-3">
                    <input type="checkbox" class="btn-check" id="btn-check-4" autocomplete="off" name="buyer" <?php echo $isBuyer ? 'checked' : ''; ?>>
                    <label class="btn" for="btn-check-4">Buyer</label>
                    <input type="checkbox" class="btn-check" id="btn-check-5" autocomplete="off" name="seller" <?php echo $isSeller ? 'checked' : ''; ?>>
                    <label class="btn" for="btn-check-5">Seller</label>
                </div>

                <div class="input-group mb-3" id="addressField">
                    <span class="input-group-text" id="inputGroup-sizing-default"><?php echo $userData['Address'] ?? 'No Address added'; ?></span>
                    <input type="text" class="form-control" placeholder="Change Address" aria-label="Username" aria-describedby="basic-addon1" name="editAddress">
                </div>

                <div class="input-group mb-3" id="bankField">
                    <span class="input-group-text" id="inputGroup-sizing-default"><?php echo $userData['Bank'] ?? 'No Bank Chosen'; ?></span>
                    <select name="editBank" id="bank" class="form-select">
                        <option selected>--Choose a Bank--</option>
                        <option value="Absa">Absa</option>
                        <option value="Capetic">Capetic</option>
                        <option value="FNB">First National Bank</option>
                        <option value="NedBank">NedBank</option>
                        <option value="StandardBank">Standard Bank</option>
                    </select>

                </div>

                <div class="input-group mb-3" id="bankNumberField">
                    <span class="input-group-text" id="inputGroup-sizing-default"><?php echo $userData['BankNumber'] ?? 'No bank Number added'; ?></span>
                    <input type="text" class="form-control" placeholder="Enter Bank Number" aria-label="Username" aria-describedby="basic-addon1" name="editBankNumber">
                </div>

                <button type="submit">Confirm</button>

            </form>

        </div>

        <div id="receipt" class="tabcontent">
            <h3>Receipt</h3>
            <!--Show receipts from the tblReceipts-->
            <div class="list-group list-group-flush">

                <?php
                if ($receiptResult != null && $receiptResult->num_rows > 0) {
                    $currentReceiptID = null;
                    while ($receipt = mysqli_fetch_assoc($receiptResult)) {
                        //start receipt grup when receipt i changes

                        if ($receipt['Receipt_ID'] != $currentReceiptID) {
                            //Close the previous receipt once a new receipt ID is brought
                            if ($currentReceiptID != null) {
                                echo "</div></div>";
                            }
                            $currentReceiptID = $receipt['Receipt_ID'];
                            echo "<div class ='list-group-item'>
                            <strong>Receipt : {$receipt['Receipt_ID']} | Date: {$receipt['Date']}</strong>
                            <div class='ms-4'";
                        }

                        //Print each product in receipt
                        echo "<p class='mb-1'>
                    <i>{$receipt['Product_Name']}</i>
                    <i>R {$receipt['Price']}</i>
                    <i>{$receipt['Seller_ID']}</i>
                    </p>";
                    }

                    //Close the last receipt
                    if ($currentReceiptID != null) {
                        echo "</div></div>";
                    }

                } else {

                    echo "<div class ='list-group-item'>
                            <strong>Receipt : No receipts found <br>Check if user buyer section is filled in 'Edit Profile' tab</strong>
                            </div>";
                }

                ?>

            </div>
            <br>
        </div>

        <div id="products" class="tabcontent">

            <br>
            <nav class="navbar bg-body-tertiary">

                <div class="container-fluid">
                    <a class="navbar-brand">Products </a><a href="productPage.php" class="button">Add Product</a>

                    <form class="d-flex" action="account.php" role="search">
                        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                        <button class="btn btn-outline-success" type="submit">Search</button>
                    </form>

                </div>

            </nav>

            <div class="container">

                <?php if ($isSeller && $sellerID != null) { ?>
                    <?php $productResult->data_seek(0); ?>
                    <?php while ($row = mysqli_fetch_assoc($productResult)) { ?>

                        <div class="card" style="width: 18rem;">

                            <form action="account.php" method="POST">
                                <input type="hidden" name="action" value="removeProduct">
                                <input type="hidden" name="productID" value="<?php echo $row['Product_ID']; ?>">
                                <button type="submit" class="btn btn-close"></button>
                            </form>

                            <img src="<?php echo $row['ImageDir'] ?>" class="card-img-top" alt="product">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $row['Product_Name'] ?></h5>
                                <p class="card-text"><?php echo $row['FullDescription'] ?></p>
                                <a href="viewPage.php?id=<?php echo $row['Product_ID'] ?>" class="btn btn-primary">View</a>
                            </div>

                        </div>

                    <?php } ?>

                <?php } else { ?>
                    <p>You have no products</p>
                <?php } ?>

            </div>

        </div>

    </section>

</body>

</html> 


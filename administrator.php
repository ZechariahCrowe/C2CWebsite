<?php
require 'database.php';
/** @var mysqli $conn */

//Display Users
$usersQuery = "SELECT u.*, b.BuyerID, b.Address, s.SellerID, s.Bank, s.BankNumber FROM tbluser u LEFT JOIN tblbuyer b ON u.userID = b.userID LEFT JOIN tblsellerfinal s ON u.userID = s.userID";
$userResult = mysqli_query($conn, $usersQuery);

//Display Sellers
$sellersQuery = "SELECT s.SellerID, u.username, u.email FROM tblsellerfinal s JOIN tbluser u ON s.userID = u.userID";
$sellersResult = mysqli_query($conn, $sellersQuery);

//Display Cart
$categoryQuery = "SELECT * FROM tblcategories";
$stmtCategory = mysqli_query($conn, $categoryQuery);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'insertCategory') {

    $categoryName = $_POST['categoryName'];

    //Generate category ID :

    $query = "SELECT CategoryID FROM tblcategories ORDER BY CategoryID DESC LIMIT 1"; // Fetch the last inserted cat ID
    $result = $conn->query($query);
    $lastRow = $result->fetch_assoc();

    if (!$lastRow) {
        $newCategoryID = "CT0001"; // Start with BY0001 if no buyers exist
    } else {
        $currentID = $lastRow['CategoryID']; // Get the current highest buyer ID

        //Use substr to remove BY by stating the string and number of characters to remove from the front
        $numbers = substr($currentID, 2); // Extract the numeric part of BY0001 to 0001

        $convertToInt = (int)$numbers; // Convert the numeric part to an integer

        $convertToInt++; // Increment the integer by 1

        $newCategoryID = "CT" . str_pad($convertToInt, 4, "0", STR_PAD_LEFT); // Pad the incremented number with leading zeros and concatenate with BY to form the new buyer ID

    }
    $numItems = 0;

    $insertCategoryQuery = "INSERT INTO tblcategories (CategoryID, CategoryName, NumItems) VALUES (?, ?, ?)";
    $stmtInsertCat = $conn->prepare($insertCategoryQuery);
    $stmtInsertCat->bind_param("ssi", $newCategoryID, $categoryName, $numItems);
    $stmtInsertCat->execute();

    header("Location: administrator.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'deleteCategory') {
    $removeCategory = $_POST['category'];

    $removeQuery = "DELETE FROM tblcategories WHERE CategoryID = ?";
    $stmtremoveCat = $conn->prepare($removeQuery);
    $stmtremoveCat->bind_param("s", $removeCategory);
    $stmtremoveCat->execute();

    header("Location: administrator.php");
    exit();
}



if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'deleteUser') {

    $deleteUserID = $_POST['deleteUserID'];

    // delete receipt details
    $receiptDetailsQuery = "DELETE rd FROM tblreceiptdetails rd JOIN tblreceipt r ON rd.ReceiptID = r.Receipt_ID = (SELECT BuyerID FROM tblbuyer WHERE userID = ?)";
    $stmtDetails = $conn->prepare($receiptDetailsQuery);
    $stmtDetails->bind_param("i", $deleteUserID);
    $stmtDetails->execute();

    //Delete Receipts
    $receiptQuery = "DELETE FROM tblreceipt WHERE Buyer_ID = (SELECT BuyerID FROM tblbuyer WHERE userID = ?)";
    $stmtReceipt = $conn->prepare($receiptQuery);
    $stmtReceipt->bind_param("i", $deleteUserID);
    $stmtReceipt->execute();

    //Delete cart items
    $cartQuery = "DELETE FROM tblcart WHERE userID = ?";
    $stmtCart = $conn->prepare($cartQuery);
    $stmtCart->bind_param("i", $deleteUserID);
    $stmtCart->execute();

    //Delete product details
    $productDetailsQuery = "DELETE pd FROM tblproductdetails pd JOIN tblproduct p ON pd.ProductID = p.Product_ID WHERE p.Seller_ID = (SELECT SellerID FROM tblsellerfinal WHERE userID = ?)";
    $stmtProductDetails = $conn->prepare($productDetailsQuery);
    $stmtProductDetails->bind_param("i", $deleteUserID);
    $stmtProductDetails->execute();

    //Delete product
    $productQuery = "DELETE FROM tblproduct WHERE Seller_ID = (SELECT SellerID FROM tblsellerfinal WHERE userID = ?)";
    $stmtProduct = $conn->prepare($productQuery);
    $stmtProduct->bind_param("i", $deleteUserID);
    $stmtProduct->execute();

    //Delete Seller
    $sellerQuery = "DELETE FROM tblsellerfinal WHERE userID = ?";
    $stmtSeller = $conn->prepare($sellerQuery);
    $stmtSeller->bind_param("i", $deleteUserID);
    $stmtSeller->execute();

    //Delete Buyer
    $buyerQuery = "DELETE FROM tblbuyer WHERE userID = ?";
    $stmtBuyer = $conn->prepare($buyerQuery);
    $stmtBuyer->bind_param("i", $deleteUserID);
    $stmtBuyer->execute();

    //Delete User
    $userQuery = "DELETE FROM tbluser WHERE userID = ?";
    $stmtUser = $conn->prepare($userQuery);
    $stmtUser->bind_param("i", $deleteUserID);
    $stmtUser->execute();

    header("Location: administrator.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'toggleStatus') {
    $toggleUserID = $_POST['toggleUserID'];
    $toggleQuery = "UPDATE tbluser SET status = IF(status = 1, 0, 1) WHERE userID = ?";
    $stmtToggle = $conn->prepare($toggleQuery);
    $stmtToggle->bind_param("i", $toggleUserID);
    $stmtToggle->execute();

    header("Location: administrator.php");
    exit();
}

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


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="Styles/administratorStyles">
    <script src="JavaScript/administrator.js" defer></script>
    <title>Administrator Page</title>
</head>

<body>

    <section class="mainForm">
        <!--Navigation section-->

        <h1>Administrator</h1>
        <div class="profile">

            <img src="images/buttonPlaceholder.jpg" alt="Profile Picture" class="profilePicture">
            <h3 id="username">Admin</h3><br>

        </div>

        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Category</button>
                <button class="nav-link" id="nav-user-tab" data-bs-toggle="tab" data-bs-target="#nav-user" type="button" role="tab" aria-controls="nav-user" aria-selected="true">Users</button>
                <button class="nav-link" id="nav-product-tab" data-bs-toggle="tab" data-bs-target="#nav-product" type="button" role="tab" aria-controls="nav-product" aria-selected="true">Products</button>
            </div>
        </nav>
        <!--Panel section-->
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab" tabindex="0">
                <h1>Category</h1>

                <form action="administrator.php" method="POST">
                    <!--Enter new catgory-->
                    <div class="input-group mb-3">

                        <span class="input-group-text" id="inputGroup-sizing-default">Enter New Category Name</span>
                        <input type="text" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default" name="categoryName">
                        <input type="hidden" name="action" value="insertCategory">

                        <button type="submit" class="btn btn-primary">Enter</button>
                    </div>
                </form>

                <form action="administrator.php" method="POST">

                    <!--Dropdown-->
                    <div class="input-group mb-3">
                        <label class="input-group-text" for="inputGroupSelect01">Category</label>
                        <select class="form-select" id="inputGroupSelect01" name="category">
                            <option selected>View Category...</option>
                            <?php while ($row = mysqli_fetch_assoc($stmtCategory)) { ?>
                                <option value="<?php echo $row['CategoryID']; ?>">
                                    <?php echo $row['CategoryName'] ?>

                                </option>


                            <?php } ?>
                            <input type="hidden" name="action" value="deleteCategory">
                            <input type="hidden" name="categoryID" value="<?php echo $row['CategoryID'] ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </select>

                    </div>

                </form>

            </div>
            <div class="tab-pane fade" id="nav-user" role="tabpanel" aria-labelledby="nav-user-tab" tabindex="0">
                <h1>User</h1>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($userResult) > 0) { ?>
                            <?php while ($user = mysqli_fetch_assoc($userResult)) { ?> <!--Data tags used to store information on each row and send it to the javascript file-->
                                <tr class="user-row" style="cursor: pointer;" data-id="<?php echo $user['userID']; ?>" data-username="<?php echo $user['username']; ?>" data-email="<?php echo $user['email']; ?>" data-status="<?php echo $user['status'] == 1 ? 'Active' : 'Inactive'; ?>" data-buyerID="<?php echo $user['BuyerID'] ?? 'Not a buyer'; ?>" data-address="<?php echo $user['Address'] ?? 'No Address'; ?>" data-sellerID="<?php echo $user['SellerID'] ?? 'Not a seller'; ?>" data-bank="<?php echo $user['Bank'] ?? 'No Bank'; ?>" data-banknumber="<?php echo $user['BankNumber'] ?? 'No bank number'; ?>">
                                    <td><?php echo $user['userID']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['email'] ?></td>
                                    <td><?php echo $user['status'] == 1 ? 'Active' : 'Inactive'; ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colespan="4">No users found</td>
                            </tr>
                        <?php } ?>
                    </tbody>

                </table>

                <!--User detail section-->
                <div id="userDetails" style="display:none;" class="card p-3 mt-3">
                    <h5>User Details</h5>
                    <hr>

                    <p><strong>User ID:</strong> <span id="detailID"></span></p>
                    <p><strong>Username:</strong> <span id="detailUsername"></span></p>
                    <p><strong>Email:</strong> <span id="detailEmail"></span></p>
                    <p><strong>Status:</strong> <span id="detailStatus"></span></p>
                    <hr>

                    <h6>Buyer Details</h6>
                    <p><strong>Buyer ID:</strong> <span id="detailBuyerID"></span></p>
                    <p><strong>Address:</strong> <span id="detailAddress"></span></p>
                    <hr>

                    <h6>Seller Details</h6>
                    <p><strong>Seller ID:</strong> <span id="detailSellerID"></span></p>
                    <p><strong>Bank:</strong> <span id="detailBank"></span></p>
                    <p><strong>Bank Number:</strong> <span id="detailBankNumber"></span></p>

                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">

                        <form action="administrator.php" method="POST">
                            <input type="hidden" name="action" value="deleteUser">
                            <input type="hidden" name="deleteUserID" id="deleteUserID">
                            <button type="submit" class="btn btn-danger">Remove User</button>
                        </form>

                        <!--Toggle status section-->
                        <form action="administrator.php" method="POST">

                            <input type="hidden" name="action" value="toggleStatus">
                            <input type="hidden" name="toggleUserID" id="toggleUserID">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" role="switch" id="switchCheckDefault" name="status" onchange="this.form.submit()">
                                <label for="switchCheckDefault" class="form-check-label" id="switchLabel">Active</label>
                            </div>

                        </form>


                    </div>

                </div>
            </div>
            <div class="tab-pane fade" id="nav-product" role="tabpanel" aria-labelledby="nav-product-tab" tabindex="0">
                <h1>Product</h1>
                <table class="table table-striped">
                    <thead>

                        <tr>
                            <th>SellerID</th>
                            <th>Username</th>
                            <th>Email</th>
                        </tr>

                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($sellersResult) > 0) { ?>
                            <?php while ($seller = mysqli_fetch_assoc($sellersResult)) { ?>
                                <tr class="seller-row" style="cursor: pointer" data-sellerid="<?php echo $seller['SellerID']; ?>" data-username="<?php echo $seller['username']; ?>" data-email="<?php echo $seller['email']; ?>">
                                    <td><?php echo $seller['SellerID']; ?></td>
                                    <td><?php echo $seller['username']; ?></td>
                                    <td><?php echo $seller['email']; ?></td>

                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="3">No sellers found</td>
                            </tr>
                        <?php } ?>
                    </tbody>

                </table>

                <div id="sellerDetails" class="card p-3 mt-3">

                </div>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>
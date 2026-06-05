<?php 
require 'database.php';
/** @var mysqli $conn */

$sellerID = $_GET['sellerID'];

$query = "SELECT p.Product_ID, p.Product_Name, pd.Price, pd.ImageDir FROM tblproduct p JOIN tblproductdetails pd ON p.Product_ID = pd.ProductID WHERE p.Seller_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $sellerID);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while($row = mysqli_fetch_assoc($result)){
    $products[] = $row;
}

echo json_encode($products);
?>
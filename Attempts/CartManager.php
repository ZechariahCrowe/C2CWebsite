<?php
class cartManager{
    private $db;

    public function __construct($dbObject)
    {
        $this->db = $dbObject;
    }

    public function addToCart($userID, $productID){
        $newCartID = $this->db->generateCustomID('tblcart', 'CartID', 'CT');
        $sql = "INSERT INTO tblcart (CartID, userID, ProductID) VALUES (?, ?, ?)";
        return $this->db->query($sql, [$newCartID, $userID, $productID], "sss");
    }

    public function removeItem($cartID) {
        $sql = "DELETE FROM tblcart WHERE CartID = ?";
        return $this->db->query($sql, [$cartID], "s");
    }

    public function getCartItems($userID){
        $sql = "SELECT c.CartID, c.ProductID, p.Product_Name, pd.Price, pd.ImageDir FROM tblcart c JOIN tblproduct p ON c.ProductID = p.Product_ID JOIN tblproductdetails pd ON c.ProductID = pd.ProductID WHERE c.userID = ?";
        return $this->db->query($sql, [$userID], "s");
    }

    public function confirmPurchase($userID){
     // fetch buyer id
     $buyerResult = $this->db->query("SELECT BuyerID FROM tblbuyer WHERE userID = ?", [$userID], "i");
     $buyerData = $buyerResult->fetch_assoc();
     $buyerID = $buyerData['BuyerID'] ?? null;

     //Fetch cart items
     $cartResult = $this->db->query("SELECT c.ProductID, p.Seller_ID FROM tblcart c JOIN tblproduct p ON c.ProductID = p.Product_ID WHERE c.userID = ?", [$userID], "i");
     $cartItems = $cartResult->fetch_all(MYSQLI_ASSOC);

     if(empty($cartItems)) return false;

     //generate receipt and insert
     $newreceiptID = $this->db->generateCustomID('tblreceipt', 'Receipt_ID', 'RC');
     $sellerID = $cartItems[0]['Seller_ID'] ?? null;

     $this->db->query("INSERT INTO tblreceipt (Receipt_ID, Seller_ID, Buyer_ID) VALUES (?, ?, ?)", [$newreceiptID, $sellerID, $buyerID], "sss");

     // Receipt details
     foreach($cartItems as $item){
        $newDetailsID = $this->db->generateCustomID('tblreceiptdetails', 'DetailsID', 'DT');
        $this->db->query("INSERT INTO tblreceiptdetails (DetailsID, ReceiptID, ProductID) VALUES (?, ?, ?)", [$newDetailsID, $newreceiptID, $item['ProductID']], "sss");

     }

     //clear cart
     return $this->db->query("DELETE FROM tblcart WHERE userID = ?", [$userID], "i");
    }
}
?>
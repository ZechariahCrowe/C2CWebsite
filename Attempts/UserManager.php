<?php

use LDAP\Result;

class UserManager
{
    private $db;

    public function __construct($dbObject)
    {
        $this->db = $dbObject;
    }

    // LOGIN FUNCTIONS
    public function login($username, $password)
    {
        $sql = "SELECT userID, username FROM tbluser WHERE username = ? AND password = ? LIMIT 1";
        $result = $this->db->query($sql, [$username, $password], "ss");

        if ($result && $result->num_rows === 1) {
            return $result->fetch_assoc(); // returns user data 
        } else {
            return false; // login failed
        }
    }

    //input validation
    public function validataInputs($username, $password)
    {
        $errors = [];

        if (empty($username)) {
            $errors[] = "Username is required.";
        } elseif (strlen($username) < 3) {
            $errors[] = "Username must be at least 3 characters long.";
        }

        if (empty($password)) {
            $errors[] = "Password is required.";
        } elseif (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long";
        }

        return $errors;
    }
    //--------------------------
    //PROFILE FUNCTIONS

    //get user data including buyer and seller
    public function getProfileData($userID)
    {
        $sql = "SELECT u.*, b.Address, s.Bank, s.BankNumber FROM tbluser u LEFT JOIN tblbuyer b ON u.userID = b.userID LEFT JOIN tblsellerfinal s ON u.userID = s.userID WHERE u.userID = ? LIMIT 1";
        $result = $this->db->query($sql, [$userID], "i");
        return $result ? $result->fetch_assoc() : null;
    }

    //get buyer data
    public function getBuyerData($userID)
    {
        $sql = "SELECT * FROM tblbuyer WHERE userID = ? LIMIT 1";
        $result = $this->db->query($sql, ['userID'], "i");
        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    }

    // Check if user is a seller
    public function isSeller($userID){
        $sql = "SELECT SellerID FROM tblsellerfinal WHERE userID = ? LIMIT 1";
        $result = $this->db->query($sql, [$userID], 'i');
        return ($result && $result->num_rows > 0);
    }

    //extract sellerid
    public function getSellerID($userID)
    {
        $sql = "SELECT SellerID FROM tblsellerfinal WHERE userID = ? LIMIT 1";
        $result = $this->db->query($sql, [$userID], "i");
        if ($result && $result->num_rows) {
            $row = $result->fetch_assoc();
            return $row['SellerID'];
        } else {
            return null;
        }
    }
    //------------------------
    //update profile section

    public function updateProfile($userID, $username, $email, $address, $bank, $bankNumber, $hasBuyer, $hasSeller)
    {
        //update fields
        if ($username !== '') {
            $this->db->query("UPDATE tbluser SET username = ? WHERE userID = ?", [$username, $userID], "ss");
            $_SESSION['username'] = $username; // CHECK THIS PART
        }
        if ($email !== '') {
            $this->db->query("UPDATE tbluser SET email = ? WHERE userID = ?", [$email, $userID], "ss");
        }

        //Update buyer information and create buyer profile if not a buyer
        if ($hasBuyer) {
            if ($address !== '') {
                $this->db->query("UPDATE tblbuyer SET Address = ? WHERE userID = ?", [$address, $userID], "ss");
            }
        } else {
            if ($address !== '') {
                $newBuyerID = $this->db->generateCustomID('tblbuyer', 'BuyerID', 'BY');
                $this->db->query("INSERT INTO tblbuyer (BuyerID, userID, Address) VALUES (?, ?, ?)", [$newBuyerID, $userID, $address]);
            }
        }

        //Update seller information and create seller profile if not a seller
        if ($hasSeller) {
            if ($bank !== '' && $bankNumber !== '') {
                $this->db->query("UPDATE tblsellerfinal SET Bank = ?, BankNumber = ? WHERE userID = ?", [$bank, $bankNumber, $userID], "ssi");
            } else {
                if ($bank !== '' && $bankNumber !== '') {
                    $newSellerID = $this->db->generateCustomeID('tblsellerfinal', 'SellerID', 'SL');
                    $this->db->query("INSERT INTO tblsellerfinal (SellerID, userID, Bank, BankNumber) VALUES (?, ?, ?, ?)", [$newSellerID, $userID, $bank, $bankNumber], "siss");
                }
            }
        }
    }

    //show receipt log history
    public function getReceipt($buyerID) {
        if(!$buyerID) return [];
        $sql = "SELECT r.ReceiptID, r.Date, r.Seller_ID, p.Product_Name, pd.Price FROM tblreceipt r JOIN tblreceiptdetails rd ON r.Receipt_ID = rd.Receipt_ID JOIN tblproduct p ON rd.ProductID = p.Product_ID JOIN tblproductdetails pd ON rd.ProductID = pd.ProductID WHERE r.Buyer_ID = ? ORDER BY r.Receipt_ID ASC";
        $result = $this->db->query($sql, [$buyerID], "s");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    //Get seller products
    public function getSellerProducts($sellerID){
        if(!$sellerID) return [];
        $sql = "SELECT p.Product_ID, p.Product_Name, pd.ImageDir, pd.Price, pd.FullDescription FROM tblproduct p JOIN tblproductdetails pd ON p.Product_ID = pd.ProductID WHERE p.Seller_ID = ?";
        $result = $this->db->query($sql, [$sellerID], "s");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function removeProduct($productID){
        $this->db->query("DELETE FROM tblproductdetails WHERE ProductID = ?", [$productID], "s");
        return $this->db->query("DELETE FROM tblproduct WHERE ProductID = ?", [$productID], "s");
    }
}

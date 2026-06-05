<?php
class ProductManager
{
    private $db;

    public function __construct($dbObject)
    {
        $this->db = $dbObject;
    }

    public function getCategories()
    {
        return $this->db->query("SELECT * FROM tblcategories");
    }
    public function getWhatsNew()
    {
        $sql = "SELECT p.Product_ID, p.Product_Name, pd.FullDescription, pd.ImageDir FROM tblproduct p JOIN tblproductdetails pd ON p.Product_ID = pd.ProductID ORDER BY p.Product_ID DESC LIMIT 10";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProducts($search = '', $category = '')
    {

        // search function including wildcards
        if ($search != '') {
            $sql = "SELECT p.Product_ID, p.Product_Name, pd.FullDescription, pd.ImageDir FROM tblproduct p JOIN tblproductdetails pd ON p.Product_ID = pd.ProductID WHERE p.Product_Name LIKE ?";
            $result = $this->db->query($sql, ["%" . $search . "%"], "s");
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        // search according to category
        if ($category != '') {
            $sql = "SELECT p.Product_ID, p.Product_Name, pd.FullDescription, pd.ImageDir FROM tblproduct p JOIN tblproductdetails pd ON p.Product_ID = pd.ProductID WHERE p.Product_Category = ? LIMIT 20";
            $result = $this->db->query($sql, [$category], "s");
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        $sql = "SELECT p.Product_ID, p.Product_Name, pd.FullDescription, pd.ImageDir FROM tblproduct p JOIN tblproductdetails pd ON p.Product_ID = pd.ProductID ORDER BY p.Product_ID DESC LIMIT 20";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    //View single product
    public function getProductbyID($productID){
        $sql = "SELECT p.Product_ID, p.Product_Name, pd.FullDescription, pd.Price, pd.Quantity, pd.Rating, pd.ImageDir, pd.DetailsID FROM tblproduct p JOIN tblproductdetails pd ON p.Product_ID = pd.ProductID WHERE p.Product_ID = ? LIMIT 1";
        $result = $this->db->query($sql, [$productID], "s");
        return $result->fetch_assoc();
    }

    //fetch comments
    public function getCommentsByDetailsID($detailsID){
        $sql = "SELECT * FROM tblrating WHERE DetailsID = ?";
        $result = $this->db->query($sql, [$detailsID], "s");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    //Calculate the avarage of stars
    public function calculateAverage($detailsID){
        $sql = "SELECT AVG(rating) AS avgRating, COUNT(rating) AS totalReviews FROM tblrating WHERE DetailsID = ?";
        $result = $this->db->query($sql, [$detailsID], "s");
        $average = $result->fetch_assoc();

        return !is_null($average['avgRating']) ? round($average['avgRating'], 1): 0;
    }

    //insert product rating as a buyer
    public function addProductRating($userID, $detailsID, $rating, $comment){
        //Check if user is a valid buyer
        $buyerSql = "SELECT b.BuyerID, u.username FROM tblbuyer b JOIN tbluser u ON b.userID = u.userID WHERE b.userID = ? LIMIT 1";
        $buyerResult = $this->db->query($buyerSql, [$userID], "i");
        $buyer = $buyerResult->fetch_assoc();

        if($buyer){
            //generate rating ID
            $newRatingID = $this->db->generateCustomID('tblrating', 'ratingID', 'RA');

            $sql = "INSERT INTO tblrating (ratingID, DetailsID, BuyerID, username, rating, comment) VALUES (?, ?, ?, ?, ?, ?)";
            return $this->db->query($sql, [$newRatingID, $detailsID, $buyer['BuyerID'], $buyer['username'], $rating, $comment], "ssssis");

        }else{
            return false;
        }
    }

}

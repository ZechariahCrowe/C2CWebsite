<?php
//die(print_r($_POST));
echo '<link rel="stylesheet" href="register.css">';
echo "<div class='form-container'>";



if($_SERVER["REQUEST_METHOD"]=== 'POST'){

    //Database Connection Parameters
    try{
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn=new mysqli("localhost", "root","", "c2c_db");
    }catch(mysqli_sql_exception $e){
        die("<h2>Database Connection Failed!</h2><p>Make sure 'c2c_db' exists in phpMyAdmin.</p><p>Error: " . $e->getMessage() . "</p></div>");
    }
    

    if($conn->connect_error){
        die("Connection failed: " . $conn->connect_error);
    }

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirmPassword'];
    $user_type = $_POST['TypeSelection'];

    echo "Username: ($username)<br>";
    echo "Email: ($email)<br>";
    echo "Password: ($password)<br>";

    // Testing user type selection
    if($user_type === "Buyer"){ // if buyer then get address
        echo "User Type: Buyer<br>";
        $address = $_POST['address'];
        echo "Address: ($address)<br>";

        if(empty($address)){ // test if address is empty
            $errors[] = "Address is required.";
        }
        if(strlen($address) < 5){ // test if address is less than 5 characters
            $errors[] = "Address must be at least 5 characters long.";
        }
    }

    if($user_type === "Seller"){ // if seller then get bank details
        echo "User Type: Seller<br>";
        $bank_name = $_POST['bankName'];
        echo "Bank Name: ($bank_name)<br>";
        $account_number = $_POST['accountNumber'];
        echo "Account Number: ($account_number)<br>";

        if($bank_name === ""){ // test if bank name is empty
            $errors[] = "Bank Name is required.";
        }

        if($account_number === ""){ // test if account number is empty
            $errors[] = "Account Number is required.";
        }
        if(strlen($account_number) <= 10 || strlen($account_number) >= 15){ // test if account number is less than 15 digits
            $errors[] = "Account Number must be at least 15 digits long.";
        }
        if(!preg_match("/^[0-9]{11,14}$/", $account_number)){ 
        $errors[] = "Account Number must be between 11 and 14 digits long.";
}

    }

    // Normal user validation
    if(empty($username)){
        $errors[] = "Username is required.";
    }
    if(strlen($username) < 3){
        $errors[] = "Username must be at least 3 characters long.";
    }
    if(empty($email)){
        $errors[] = "Email is required.";
    }
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "Invalid email format.";
    }
    if(empty($password)){
        $errors[] = "Password is required.";
    }
    if(strlen($password) < 6){
        $errors[] = "Password must be at least 6 characters long.";
    }
    if($password !== $confirm_password){
        $errors[] = "Passwords do not match.";
    }

    // loop through all the errors and display them
    if(!empty($errors)){

        echo "<h2>Errors:</h2>";
        foreach($errors as $error){
            echo "<p class='error-message'>$error</p>";
        }

        echo "<div class= 'switch-link'><a href='index.html'> Go back to registration form</a></div>";
    }else{

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        //Prepare and bind the SQL statement to prevent SQL injection
        $stmt=$conn->prepare("INSERT INTO tbluser (email, username, password) VALUES (?, ?, ?)"); // insert username, email and password into tbluser
        $stmt->bind_param("sss", $email, $username, $hashed_password);
        
        if($stmt->execute()){

            $new_user_id = $stmt->insert_id; // get the ID of the newly inserted user

            if($user_type === "Buyer"){ // Test if user type is buyer and insert into tblbuyer
                //Attempt to auto-generate a unique buyer ID by fetching the last inserted buyer ID and incrementing it

                $query = "SELECT BuyerID FROM tblbuyer ORDER BY BuyerID DESC LIMIT 1"; // Fetch the last inserted buyer ID
                $result = $conn->query($query);
                $lastRow = $result->fetch_assoc();

                if(!$lastRow){
                    $newBuyerID = "BY0001"; // Start with BY0001 if no buyers exist
                }else{
                    $currentID = $lastRow['BuyerID']; // Get the current highest buyer ID

                    //Use substr to remove BY by stating the string and number of characters to remove from the front
                    $numbers = substr($currentID, 2); // Extract the numeric part of BY0001 to 0001

                    $convertToInt = (int)$numbers; // Convert the numeric part to an integer

                    $convertToInt++; // Increment the integer by 1

                    $newBuyerID = "BY".str_pad($convertToInt, 4, "0", STR_PAD_LEFT); // Pad the incremented number with leading zeros and concatenate with BY to form the new buyer ID

                } // I made use of W3Schools PHP string functions to assist : https://www.w3schools.com/php/php_ref_string.asp

                //$buyerID = "BY1"; // TEMPORARY FIX: Generate a unique buyer ID (you can implement a better logic for this)

                $stmt_buyer = $conn->prepare("INSERT INTO tblbuyer(BuyerID, userID, address) VALUES (?, ?, ?)"); // Remember column names in tblbuyer are BuyerID, userID and address
                $stmt_buyer->bind_param("sis", $newBuyerID, $new_user_id, $address);

                if($stmt_buyer->execute()){
                    echo "<p class='success-message'>Buyer details saved successfully!</p>";
                }else{
                    echo "<p class='error-message'>Error saving buyer details: " . $stmt_buyer->error . "</p>";
                }
                $stmt_buyer->close();

            } 
            elseif($user_type === "Seller"){ // Test if user type is seller and insert into tblsellerfinal

                //Attempt to auto-generate a unique seller ID by fetching the last inserted seller ID and incrementing it

                $query = "SELECT SellerID FROM tblsellerfinal ORDER BY SellerID DESC LIMIT 1"; // Fetch the last inserted seller ID
                $result = $conn->query($query);
                $lastRow = $result->fetch_assoc();

                if(!$lastRow){
                    $newSellerID = "SL0001"; // Start with SL0001 if no sellers exist
                }else{
                    $currentID = $lastRow['SellerID']; // Get the current highest Seller ID

                    //Use substr to remove SL by stating the string and number of characters to remove from the front
                    $numbers = substr($currentID, 2); // Extract the numeric part of SL0001 to 0001
                    $convertToInt = (int)$numbers; // Convert the numeric part to an integer
                    $convertToInt++; // Increment the integer by 1
                    $newSellerID = "SL".str_pad($convertToInt, 4, "0", STR_PAD_LEFT); // Pad the incremented number with leading zeros and concatenate with SL to form the new seller ID
                }

                $stmt_seller = $conn->prepare("INSERT INTO tblsellerfinal(SellerID, UserID, Bank, BankNumber) VALUES (?, ?, ?, ?)"); // Remember column names in tblseller are SellerID, userID, bankName and accountNumber
                $stmt_seller->bind_param("siss", $newSellerID, $new_user_id, $bank_name, $account_number);

                if($stmt_seller->execute()){
                    echo "<p class='success-message'>Seller details saved successfully!</p>";
                }else{
                    echo "<p class='error-message'>Error saving seller details: " . $stmt_seller->error . "</p>";
                }
                $stmt_seller->close();
            }
            echo "<h2>Successfully registered!</h2>";
            echo "<p class='success-message'>Welcome, $username!</p>";
        }else{
             echo "<p class='error-message'>Error: ".$stmt->error."</p>";
        }

        echo "<div class='switch-link'><a href='register.html'>Go back to registration form</a></div>";
        
        $stmt->close();
    }
    $conn->close();
}
else{
     echo "<h2> Access Denied</h2>";
    echo "<p class='error-message'>Please submit the form to register.</p>";
    echo "<div class='switch-link'><a href='register.html'>Go back to registration form</a></div>";
}
echo "</div>"

?>
  
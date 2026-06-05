-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 05, 2026 at 05:12 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `c2c_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblbuyer`
--

CREATE TABLE `tblbuyer` (
  `BuyerID` varchar(6) NOT NULL,
  `userID` int(6) NOT NULL,
  `Address` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblbuyer`
--

INSERT INTO `tblbuyer` (`BuyerID`, `userID`, `Address`) VALUES
('BY0001', 8, '123 Avenue, Cape Town'),
('BY0003', 1, '21 Crusty Street, Western Cape');

-- --------------------------------------------------------

--
-- Table structure for table `tblcart`
--

CREATE TABLE `tblcart` (
  `CartID` varchar(8) NOT NULL,
  `userID` varchar(8) NOT NULL,
  `ProductID` varchar(8) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1,
  `Date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblcart`
--

INSERT INTO `tblcart` (`CartID`, `userID`, `ProductID`, `Quantity`, `Date`) VALUES
('CT0001', '1', 'PT0006', 1, '2026-06-04'),
('CT0002', '1', 'PT0005', 1, '2026-06-04');

-- --------------------------------------------------------

--
-- Table structure for table `tblcategories`
--

CREATE TABLE `tblcategories` (
  `CategoryID` varchar(6) NOT NULL,
  `CategoryName` varchar(30) NOT NULL,
  `NumItems` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblcategories`
--

INSERT INTO `tblcategories` (`CategoryID`, `CategoryName`, `NumItems`) VALUES
('CT0001', 'Fitness', 0),
('CT0002', 'Kitchen', 0),
('CT0003', 'Outdoor', 0),
('CT0004', 'Pet', 0),
('CT0005', 'Technology', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tblproduct`
--

CREATE TABLE `tblproduct` (
  `Product_ID` varchar(6) NOT NULL,
  `Product_Name` varchar(40) NOT NULL,
  `Product_Category` varchar(25) NOT NULL,
  `Seller_ID` varchar(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblproduct`
--

INSERT INTO `tblproduct` (`Product_ID`, `Product_Name`, `Product_Category`, `Seller_ID`) VALUES
('PT0001', 'Bravia 3 TV', 'Technology', 'SL0001'),
('PT0005', 'Dumbells 10KG', 'Fitness', 'SL0001'),
('PT0006', 'Stainless steel microwave', 'CT0002', 'SL0001'),
('PT0007', 'Toaster', 'CT0002', 'SL0003');

-- --------------------------------------------------------

--
-- Table structure for table `tblproductdetails`
--

CREATE TABLE `tblproductdetails` (
  `DetailsID` varchar(6) NOT NULL,
  `ProductID` varchar(6) NOT NULL,
  `FullDescription` varchar(50) NOT NULL,
  `Price` decimal(6,0) NOT NULL,
  `Rating` int(11) NOT NULL DEFAULT 0,
  `Quantity` int(11) NOT NULL,
  `ImageDir` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblproductdetails`
--

INSERT INTO `tblproductdetails` (`DetailsID`, `ProductID`, `FullDescription`, `Price`, `Rating`, `Quantity`, `ImageDir`) VALUES
('DL0001', 'PT0001', 'SUPER ULTIMATE 4K SMART TV', 1200, 0, 0, 'productImages/BRAVIA_3.PNG'),
('DL0005', 'PT0005', 'Everlast 10KG Cast Iron Dumbell Set', 200, 0, 2, 'productImages/dumbell.jpg'),
('DL0006', 'PT0006', 'Hisense 34L Digital Microwave Matt Black H34MOBS17', 3000, 0, 3, 'productImages/microwave.jpg'),
('DL0007', 'PT0007', 'Grey 250KW, 30L Toaster with LED lights on the out', 750, 0, 2, 'productImages/images.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tblrating`
--

CREATE TABLE `tblrating` (
  `ratingID` varchar(6) NOT NULL,
  `DetailsID` varchar(6) NOT NULL,
  `BuyerID` varchar(6) NOT NULL,
  `username` varchar(30) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblrating`
--

INSERT INTO `tblrating` (`ratingID`, `DetailsID`, `BuyerID`, `username`, `rating`, `comment`) VALUES
('RA0002', 'DL0007', 'BY0003', 'ZeeeeWorld', 3, 'It broke after 2 uses'),
('RA0003', 'DL0006', 'BY0003', 'ZeeeeWorld', 3, 'Its perfect');

-- --------------------------------------------------------

--
-- Table structure for table `tblreceipt`
--

CREATE TABLE `tblreceipt` (
  `Receipt_ID` varchar(6) NOT NULL,
  `Seller_ID` varchar(6) NOT NULL,
  `Buyer_ID` varchar(6) NOT NULL,
  `Date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblreceipt`
--

INSERT INTO `tblreceipt` (`Receipt_ID`, `Seller_ID`, `Buyer_ID`, `Date`) VALUES
('RC0001', 'SL0001', 'BY0003', '2026-05-31'),
('RC0002', 'SL0001', 'BY0003', '2026-05-31'),
('RC0003', 'SL0003', 'BY0003', '2026-06-04'),
('RC0004', 'SL0001', 'BY0003', '2026-06-04');

-- --------------------------------------------------------

--
-- Table structure for table `tblreceiptdetails`
--

CREATE TABLE `tblreceiptdetails` (
  `DetailsID` varchar(6) NOT NULL,
  `ReceiptID` varchar(6) NOT NULL,
  `ProductID` varchar(6) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblreceiptdetails`
--

INSERT INTO `tblreceiptdetails` (`DetailsID`, `ReceiptID`, `ProductID`, `Quantity`) VALUES
('DT0001', 'RC0003', 'PT0007', 1),
('DT0002', 'RC0004', 'PT0006', 1),
('DT0003', 'RC0004', 'PT0005', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tblsellerfinal`
--

CREATE TABLE `tblsellerfinal` (
  `SellerID` varchar(8) NOT NULL,
  `userID` int(8) NOT NULL,
  `Bank` varchar(20) NOT NULL,
  `BankNumber` varchar(15) NOT NULL,
  `Rating` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblsellerfinal`
--

INSERT INTO `tblsellerfinal` (`SellerID`, `userID`, `Bank`, `BankNumber`, `Rating`) VALUES
('SL0001', 11, 'Capetic', '092817238012', 0),
('SL0002', 15, 'Absa', '023817238012', 0),
('SL0003', 1, 'FNB', '0291823018241', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbluser`
--

CREATE TABLE `tbluser` (
  `userID` int(8) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbluser`
--

INSERT INTO `tbluser` (`userID`, `username`, `email`, `password`, `status`) VALUES
(1, 'ZeeeeWorld', 'jerry@gmail.com', 'Jerry123', 1),
(2, 'ZeeeWorlds', 'zeeeworld@gmail.com', 'Zee123', 1),
(3, 'Ryle', 'ryle@gmail.com', 'Ryle123', 0),
(8, 'Manner', 'manner@gmail.com', 'Manner123', 1),
(11, 'Josh', 'josh@gmail.com', 'Josh321', 1),
(15, 'Seamus', 'seamus@outlook.com', 'newBosvark1', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblbuyer`
--
ALTER TABLE `tblbuyer`
  ADD KEY `fk_user_buyer` (`userID`);

--
-- Indexes for table `tblcart`
--
ALTER TABLE `tblcart`
  ADD PRIMARY KEY (`CartID`);

--
-- Indexes for table `tblcategories`
--
ALTER TABLE `tblcategories`
  ADD PRIMARY KEY (`CategoryName`);

--
-- Indexes for table `tblproduct`
--
ALTER TABLE `tblproduct`
  ADD PRIMARY KEY (`Product_ID`),
  ADD KEY `fk_seller_product` (`Seller_ID`),
  ADD KEY `fk_category_product` (`Product_Category`);

--
-- Indexes for table `tblproductdetails`
--
ALTER TABLE `tblproductdetails`
  ADD PRIMARY KEY (`DetailsID`),
  ADD KEY `fk_product_details` (`ProductID`);

--
-- Indexes for table `tblrating`
--
ALTER TABLE `tblrating`
  ADD PRIMARY KEY (`ratingID`);

--
-- Indexes for table `tblreceipt`
--
ALTER TABLE `tblreceipt`
  ADD PRIMARY KEY (`Receipt_ID`);

--
-- Indexes for table `tblreceiptdetails`
--
ALTER TABLE `tblreceiptdetails`
  ADD PRIMARY KEY (`DetailsID`);

--
-- Indexes for table `tblsellerfinal`
--
ALTER TABLE `tblsellerfinal`
  ADD PRIMARY KEY (`SellerID`),
  ADD KEY `fk_user_seller` (`userID`);

--
-- Indexes for table `tbluser`
--
ALTER TABLE `tbluser`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbluser`
--
ALTER TABLE `tbluser`
  MODIFY `userID` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblbuyer`
--
ALTER TABLE `tblbuyer`
  ADD CONSTRAINT `fk_user_buyer` FOREIGN KEY (`UserID`) REFERENCES `tbluser` (`userID`);

--
-- Constraints for table `tblproduct`
--
ALTER TABLE `tblproduct`
  ADD CONSTRAINT `fk_seller_product` FOREIGN KEY (`Seller_ID`) REFERENCES `tblsellerfinal` (`SellerID`);

--
-- Constraints for table `tblproductdetails`
--
ALTER TABLE `tblproductdetails`
  ADD CONSTRAINT `fk_product_details` FOREIGN KEY (`ProductID`) REFERENCES `tblproduct` (`Product_ID`);

--
-- Constraints for table `tblsellerfinal`
--
ALTER TABLE `tblsellerfinal`
  ADD CONSTRAINT `fk_user_seller` FOREIGN KEY (`UserID`) REFERENCES `tbluser` (`userID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

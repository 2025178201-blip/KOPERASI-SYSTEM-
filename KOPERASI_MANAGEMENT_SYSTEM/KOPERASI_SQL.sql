CREATE TABLE Supplier (
    supplierID NUMBER PRIMARY KEY,
    companyName VARCHAR2(100),
    personInCharge VARCHAR2(100),
    contactNumber VARCHAR2(20),
    companyAddress VARCHAR2(200)
);

CREATE TABLE Product (
    productID NUMBER PRIMARY KEY,
    supplierID NUMBER,
    productName VARCHAR2(100),
    sellingPrice NUMBER(10,2),
    unitSize VARCHAR2(50),
    description VARCHAR2(200),
    quantityInStock NUMBER,
    lastDateRestock DATE,
    reorderLevel NUMBER,
    category VARCHAR2(50),
    FOREIGN KEY (supplierID) REFERENCES Supplier(supplierID)
);

CREATE TABLE UserAccount (
    userID NUMBER PRIMARY KEY,
    name VARCHAR2(100),
    username VARCHAR2(50),
    password VARCHAR2(100),
    email VARCHAR2(100),
    contact_number VARCHAR2(20),
    status VARCHAR2(20)
);

CREATE TABLE ProductTransaction (
    transactionID NUMBER PRIMARY KEY,
    productID NUMBER,
    userID NUMBER,
    transactionDate DATE,
    transactionTime TIMESTAMP,
    quantitySold NUMBER,
    FOREIGN KEY (productID) REFERENCES Product(productID),
    FOREIGN KEY (userID) REFERENCES UserAccount(userID)
);

CREATE TABLE RestockDetail (
    restockDetailID NUMBER PRIMARY KEY,
    supplierID NUMBER,
    productID NUMBER,
    quantityRestock NUMBER,
    dateTimeRestock TIMESTAMP,
    unitCost NUMBER(10,2),
    totalCost NUMBER(10,2),
    invoiceRef VARCHAR2(50),
    invoicePdf VARCHAR2(200),
    FOREIGN KEY (supplierID) REFERENCES Supplier(supplierID),
    FOREIGN KEY (productID) REFERENCES Product(productID)
);

CREATE TABLE Sales (
    saleID NUMBER PRIMARY KEY,
    userID NUMBER,
    saleDate DATE,
    totalAmount NUMBER(10,2),
    FOREIGN KEY (userID) REFERENCES UserAccount(userID)
);

CREATE TABLE SalesDetail (
    salesDetailID NUMBER PRIMARY KEY,
    saleID NUMBER,
    productID NUMBER,
    quantitySold NUMBER,
    unitPrice NUMBER(10,2),
    totalPrice NUMBER(10,2),
    FOREIGN KEY (saleID) REFERENCES Sales(saleID),
    FOREIGN KEY (productID) REFERENCES Product(productID)
);

CREATE TABLE Teacher (
    userID NUMBER PRIMARY KEY,
    subjectTeaching VARCHAR2(100),
    FOREIGN KEY (userID) REFERENCES UserAccount(userID)
);

CREATE TABLE StudentClub (
    userID NUMBER PRIMARY KEY,
    class VARCHAR2(50),
    joinDate DATE,
    FOREIGN KEY (userID) REFERENCES UserAccount(userID)
);

CREATE TABLE Food (
    productID NUMBER PRIMARY KEY,
    brand VARCHAR2(50),
    flavour VARCHAR2(50),
    expiryDate DATE,
    FOREIGN KEY (productID) REFERENCES Product(productID)
);

CREATE TABLE SchoolClothing (
    productID NUMBER PRIMARY KEY,
    sizeCloth VARCHAR2(20),
    colour VARCHAR2(30),
    sportsHouse VARCHAR2(30),
    sleeveType VARCHAR2(30),
    FOREIGN KEY (productID) REFERENCES Product(productID)
);

CREATE TABLE SchoolMerchandise (
    productID NUMBER PRIMARY KEY,
    itemType VARCHAR2(50),
    sizeMerch VARCHAR2(20),
    FOREIGN KEY (productID) REFERENCES Product(productID)
);

CREATE TABLE Seasonal (
    productID NUMBER PRIMARY KEY,
    eventName VARCHAR2(100),
    startDateEvent DATE,
    endDateEvent DATE,
    limitedEdition VARCHAR2(10),
    FOREIGN KEY (productID) REFERENCES Product(productID)
);

-- Food (IDs: 109, 110, 111...)
CREATE SEQUENCE food_seq START WITH 109 INCREMENT BY 1 NOCACHE;

-- School Clothing (IDs: 208, 209, 210...)
CREATE SEQUENCE clothing_seq START WITH 208 INCREMENT BY 1 NOCACHE;

-- School Merchandise (IDs: 308, 309, 310...)
CREATE SEQUENCE merch_seq START WITH 308 INCREMENT BY 1 NOCACHE;

-- Seasonal (IDs: 405, 406, 407...)
CREATE SEQUENCE seasonal_seq START WITH 405 INCREMENT BY 1 NOCACHE;

-- Restock_Detail
CREATE SEQUENCE restock_detail_seq START WITH 9700 INCREMENT BY 1 NOCACHE;

-- Supplier
CREATE SEQUENCE supplier_seq START WITH 70 INCREMENT BY 10 NOCACHE;

SELECT * FROM USERACCOUNT;
SELECT * FROM SUPPLIER; 
SELECT * FROM PRODUCT;
SELECT * FROM PRODUCTTRANSACTION; 
SELECT * FROM RESTOCKDETAIL; 
SELECT * FROM SALES; 
SELECT * FROM SALESDETAIL; 
SELECT * FROM TEACHER; 
SELECT * FROM STUDENTCLUB; 
SELECT * FROM FOOD; 
SELECT * FROM SCHOOLCLOTHING; 
SELECT * FROM SCHOOLMERCHANDISE; 
SELECT * FROM SEASONAL; 

--DESCRIPTION, LASTDATERESTOCK, COMPANYNAME-- 

-- 1. SUPPLIERS (IDs start at 10)
INSERT INTO Supplier VALUES (10, 'Selangor Food Distros', 'Azman Ali', '012-3344556', 'Lot 12, Seksyen 15, Shah Alam');
INSERT INTO Supplier VALUES (20, 'Global Apparel Co.', 'Siti Nur', '017-9988776', 'Pusat Bandar Bangi, Selangor');
INSERT INTO Supplier VALUES (30, 'School Spirit Merch', 'Kevin Tan', '011-5544332', 'Petaling Jaya, Selangor');

-- 2. USER ACCOUNTS (IDs are real-world IC numbers)
-- Format: YYMMDD-PB-### (Using 12 digits)
INSERT INTO UserAccount VALUES (010515105543, 'Adam Haikal', 'adam01', 'hash_pwd_1', 'adam@school.edu.my', '019-2223334', 'ACTIVE');
INSERT INTO UserAccount VALUES (020820146678, 'Nurul Izzah', 'izzah02', 'hash_pwd_2', 'izzah@school.edu.my', '013-4445556', 'ACTIVE');
INSERT INTO UserAccount VALUES (850101104433, 'Cikgu Sarah', 'sarah85', 'hash_pwd_3', 'sarah_t@school.edu.my', '012-7778889', 'ACTIVE');

-- 3. PRODUCTS (IDs start at 100/200/300)
-- Food (100s)
INSERT INTO Product VALUES (101, 10, 'Nasi Lemak Bungkus', 2.50, '1 pack', 'Fresh daily', 50, DATE '2026-01-02', 10, 'Food');
INSERT INTO Product VALUES (102, 10, 'Iced Milo', 1.50, '250ml', 'Canned drink', 100, DATE '2026-01-01', 20, 'Food');
-- Clothing (200s)
INSERT INTO Product VALUES (201, 20, 'School Tie', 15.00, '1 unit', 'Silk blend', 30, DATE '2025-12-15', 5, 'Clothing');
-- Merchandise (300s)
INSERT INTO Product VALUES (301, 30, 'School Notebook', 4.00, 'A5', '80 pages', 200, DATE '2025-12-20', 50, 'Merchandise');

-- 4. SUB-CATEGORY TABLES (Tallying with Product IDs)
INSERT INTO Food VALUES (101, 'Mak Cik Kantin', 'Original', DATE '2026-01-03');
INSERT INTO Food VALUES (102, 'Nestle', 'Chocolate', DATE '2027-06-30');
INSERT INTO SchoolClothing VALUES (201, 'Standard', 'Dark Blue', NULL, 'N/A');
INSERT INTO SchoolMerchandise VALUES (301, 'Stationery', 'A5');

-- 5. ROLES (Tallying with IC Numbers)
INSERT INTO Teacher VALUES (850101104433, 'History');
INSERT INTO StudentClub VALUES (010515105543, '5 Science 1', DATE '2024-01-10');

-- 6. SALES & TRANSACTIONS (ID starting at 1000 for uniqueness)
-- Sale 1: Student buying breakfast
INSERT INTO Sales VALUES (1001, 010515105543, DATE '2026-01-02', 4.00);
INSERT INTO SalesDetail VALUES (5001, 1001, 101, 1, 2.50, 2.50); -- 1 Nasi Lemak
INSERT INTO SalesDetail VALUES (5002, 1001, 102, 1, 1.50, 1.50); -- 1 Milo

-- Sale 2: Teacher buying a notebook
INSERT INTO Sales VALUES (1002, 850101104433, DATE '2026-01-02', 4.00);
INSERT INTO SalesDetail VALUES (5003, 1002, 301, 1, 4.00, 4.00);

-- 7. STOCK MANAGEMENT
-- Restocking 50 more ties from the Apparel supplier
INSERT INTO RestockDetail VALUES (8001, 20, 201, 50, SYSTIMESTAMP, 10.00, 500.00, 'INV-2026-001', 'invoice_201.pdf');

-- Suppliers (30-40)
INSERT INTO Supplier VALUES (40, 'Musim Festive Trading', 'Muthu Samy', '016-55443322', 'Klang, Selangor');

-- Users (Students & Teachers with IC numbers)
-- Students
INSERT INTO UserAccount VALUES (050312101122, 'Zulhelmi Arif', 'zul99', 'pass1', 'zul@school.com', '011-1234567', 'ACTIVE');
INSERT INTO UserAccount VALUES (061125145566, 'Chloe Ng', 'chloe06', 'pass2', 'chloe@school.com', '012-3344556', 'ACTIVE');
INSERT INTO UserAccount VALUES (070102108899, 'Vishnu Raj', 'vishnu7', 'pass3', 'vishnu@school.com', '013-6677889', 'ACTIVE');
INSERT INTO UserAccount VALUES (050505102233, 'Siti Aminah', 'siti05', 'pass4', 'siti@school.com', '014-9988776', 'ACTIVE');
-- Teachers
INSERT INTO UserAccount VALUES (781212105544, 'Cikgu Rahman', 'rahman78', 'pass5', 'rahman@school.com', '019-1122334', 'ACTIVE');
INSERT INTO UserAccount VALUES (820404146677, 'Madam Lim', 'lim82', 'pass6', 'lim@school.com', '017-5566778', 'ACTIVE');

-- Roles
INSERT INTO Teacher VALUES (781212105544, 'Physics');
INSERT INTO Teacher VALUES (820404146677, 'English');
INSERT INTO StudentClub VALUES (050312101122, '5 Perdana', DATE '2024-01-05');
INSERT INTO StudentClub VALUES (061125145566, '4 Maju', DATE '2025-02-10');

-- Stationery (300s)
INSERT INTO Product VALUES (302, 30, 'Scientific Calculator', 45.00, '1 unit', 'Casio model', 15, DATE '2025-12-01', 5, 'Merchandise');
INSERT INTO Product VALUES (303, 30, 'Geometry Set', 12.50, '1 box', 'Full set', 40, DATE '2025-12-05', 10, 'Merchandise');

-- Seasonal (400s)
INSERT INTO Product VALUES (401, 40, 'Chinese New Year Angpow', 2.00, '1 pack', 'Red packets', 100, DATE '2026-01-01', 20, 'Seasonal');
INSERT INTO Product VALUES (402, 40, 'Ramadan Dates (Kurma)', 8.50, '250g', 'Premium dates', 60, DATE '2026-01-02', 15, 'Seasonal');

-- Detail Tables
INSERT INTO SchoolMerchandise VALUES (302, 'Calculator', 'Standard');
INSERT INTO SchoolMerchandise VALUES (303, 'Geometry', 'Standard');
INSERT INTO Seasonal VALUES (401, 'CNY 2026', DATE '2026-01-15', DATE '2026-02-15', 'YES');
INSERT INTO Seasonal VALUES (402, 'Ramadan 2026', DATE '2026-02-20', DATE '2026-03-25', 'YES');

-- TRANSACTION 1: Cikgu Rahman buys calculators for his class
INSERT INTO Sales VALUES (2001, 781212105544, DATE '2025-12-20', 225.00);
INSERT INTO SalesDetail VALUES (6001, 2001, 302, 5, 45.00, 225.00);

-- TRANSACTION 2: Student Chloe buys uniform and food
INSERT INTO Sales VALUES (2002, 061125145566, DATE '2026-01-01', 19.00);
INSERT INTO SalesDetail VALUES (6002, 2002, 102, 2, 1.50, 3.00); -- 2 Milos
INSERT INTO SalesDetail VALUES (6003, 2002, 201, 1, 15.00, 15.00); -- 1 Tie

-- TRANSACTION 3: Student Zul buys snacks and stationery
INSERT INTO Sales VALUES (2003, 050312101122, DATE '2026-01-02', 17.50);
INSERT INTO SalesDetail VALUES (6004, 2003, 101, 2, 2.50, 5.00); -- 2 Nasi Lemak
INSERT INTO SalesDetail VALUES (6005, 2003, 303, 1, 12.50, 12.50); -- 1 Geometry set

-- TRANSACTION 4: Madam Lim buys seasonal gifts
INSERT INTO Sales VALUES (2004, 820404146677, DATE '2026-01-02', 40.00);
INSERT INTO SalesDetail VALUES (6006, 2004, 401, 20, 2.00, 40.00); -- 20 Angpow packs

-- More Students (Form 1 to Form 5)
INSERT INTO UserAccount VALUES (100512140011, 'Haris Bin Farhan', 'haris10', 'p@ss1', 'haris@school.com', '011-2222333', 'ACTIVE');
INSERT INTO UserAccount VALUES (090220104422, 'Siti Nurhaliza Jr', 'siti09', 'p@ss2', 'siti_jr@school.com', '012-3334444', 'ACTIVE');
INSERT INTO UserAccount VALUES (110830105533, 'Ravi Kumar', 'ravi11', 'p@ss3', 'ravi11@school.com', '013-4445555', 'ACTIVE');
INSERT INTO UserAccount VALUES (081225146644, 'Mei Ling', 'meiling08', 'p@ss4', 'meiling@school.com', '014-5556666', 'ACTIVE');
INSERT INTO UserAccount VALUES (100101101155, 'Muhammad Ali', 'ali10', 'p@ss5', 'ali@school.com', '015-6667777', 'ACTIVE');

-- Assign to Student Clubs
INSERT INTO StudentClub VALUES (100512140011, '4 Amanah', DATE '2025-01-02');
INSERT INTO StudentClub VALUES (090220104422, '5 Bestari', DATE '2025-01-05');
INSERT INTO StudentClub VALUES (110830105533, '3 Cekal', DATE '2025-02-15');

-- More Food (100s)
INSERT INTO Product VALUES (103, 10, 'Karipap (3 pcs)', 2.00, '1 set', 'Handmade snacks', 40, DATE '2026-01-02', 15, 'Food');
INSERT INTO Product VALUES (104, 10, 'Apple Juice Box', 2.20, '200ml', 'Tetra Pak', 85, DATE '2026-01-01', 20, 'Food');

-- More Clothing (200s)
INSERT INTO Product VALUES (202, 20, 'School Tracks', 28.00, 'L Size', 'Microfiber', 25, DATE '2025-12-10', 5, 'Clothing');
INSERT INTO Product VALUES (203, 20, 'White School Shirt', 22.00, 'M Size', 'Cotton', 40, DATE '2025-12-10', 10, 'Clothing');

-- Details
INSERT INTO Food VALUES (103, 'Homemade', 'Potato/Egg', DATE '2026-01-04');
INSERT INTO Food VALUES (104, 'Marigold', 'Apple', DATE '2026-12-31');
INSERT INTO SchoolClothing VALUES (202, 'L', 'Black', 'N/A', 'Long');
INSERT INTO SchoolClothing VALUES (203, 'M', 'White', 'N/A', 'Short');

-- SALES 3001 - 3005 (The Morning Rush)
-- Haris buys breakfast
INSERT INTO Sales VALUES (3001, 100512140011, DATE '2026-01-02', 4.50);
INSERT INTO SalesDetail VALUES (7001, 3001, 101, 1, 2.50, 2.50); -- Nasi Lemak
INSERT INTO SalesDetail VALUES (7002, 3001, 103, 1, 2.00, 2.00); -- Karipap

-- Ravi buys a calculator and shirt (Big Spender)
INSERT INTO Sales VALUES (3002, 110830105533, DATE '2026-01-02', 67.00);
INSERT INTO SalesDetail VALUES (7003, 3002, 302, 1, 45.00, 45.00); -- Calc
INSERT INTO SalesDetail VALUES (7004, 3002, 203, 1, 22.00, 22.00); -- Shirt

-- Ali buys bulk snacks for a club meeting
INSERT INTO Sales VALUES (3003, 100101101155, DATE '2026-01-02', 20.00);
INSERT INTO SalesDetail VALUES (7005, 3003, 103, 10, 2.00, 20.00); -- 10 sets of Karipap

-- Restocking Food (Supplier 10)
INSERT INTO RestockDetail VALUES (9001, 10, 101, 100, SYSTIMESTAMP, 1.80, 180.00, 'INV-FD-101', 'inv1.pdf');
INSERT INTO RestockDetail VALUES (9002, 10, 102, 200, SYSTIMESTAMP, 1.10, 220.00, 'INV-FD-102', 'inv2.pdf');

-- Restocking Stationery (Supplier 30)
INSERT INTO RestockDetail VALUES (9003, 30, 301, 500, SYSTIMESTAMP, 2.50, 1250.00, 'INV-PJ-301', 'inv3.pdf');

-- SUPPLIERS (50-60)
INSERT INTO Supplier VALUES (50, 'Sukan Emas Sdn Bhd', 'Coach Rashid', '019-3322110', 'Bukit Jalil, KL');
INSERT INTO Supplier VALUES (60, 'TechEdu Solutions', 'Ms. Wong', '012-4455667', 'Subang Jaya, Selangor');

-- PRODUCTS (100-400 series)
-- More Food
INSERT INTO Product VALUES (105, 10, 'Chicken Curry Puff', 1.50, '1 pc', 'Spicy filling', 60, DATE '2026-01-02', 15, 'Food');
-- More Clothing (Sports focus)
INSERT INTO Product VALUES (204, 50, 'Tracksuit Bottoms', 35.00, 'L Size', 'Microfiber', 30, DATE '2025-12-28', 10, 'Clothing');
INSERT INTO Product VALUES (205, 50, 'School Sports Jersey', 25.00, 'M Size', 'Dry-fit', 50, DATE '2025-12-28', 15, 'Clothing');
-- More Merchandise
INSERT INTO Product VALUES (306, 60, 'USB Drive 32GB', 25.00, '1 unit', 'School Branded', 20, DATE '2025-12-15', 5, 'Merchandise');
-- More Seasonal
INSERT INTO Product VALUES (403, 40, 'School Anniversary Medal', 12.00, '1 unit', 'Limited Edition', 100, DATE '2026-01-01', 10, 'Seasonal');

-- SUB-CATEGORY TABLES
INSERT INTO Food VALUES (105, 'Local Bakery', 'Spicy Chicken', DATE '2026-01-04');
INSERT INTO SchoolClothing VALUES (204, 'L', 'Black', 'All Houses', 'Long');
INSERT INTO SchoolClothing VALUES (205, 'M', 'Yellow', 'Yellow House', 'Short');
INSERT INTO SchoolMerchandise VALUES (306, 'Electronics', '32GB');
INSERT INTO Seasonal VALUES (403, '60th Anniversary', DATE '2026-01-01', DATE '2026-12-31', 'YES');

-- Restock for Sports Gear
INSERT INTO RestockDetail VALUES (9010, 50, 204, 40, SYSTIMESTAMP, 22.00, 880.00, 'INV-SE-204', 'rec_204.pdf');
INSERT INTO RestockDetail VALUES (9011, 50, 205, 60, SYSTIMESTAMP, 15.00, 900.00, 'INV-SE-205', 'rec_205.pdf');

-- Restock for Tech
INSERT INTO RestockDetail VALUES (9012, 60, 306, 25, SYSTIMESTAMP, 18.00, 450.00, 'INV-TE-306', 'rec_306.pdf');

-- Restock for Food (Frequent low-value)
INSERT INTO RestockDetail VALUES (9013, 10, 105, 100, SYSTIMESTAMP, 0.90, 90.00, 'INV-FD-105', 'rec_105.pdf');

-- SALE 4001: A teacher (Madam Lim) buying prizes for a quiz
INSERT INTO Sales VALUES (4001, 820404146677, DATE '2026-01-02', 150.00);
INSERT INTO SalesDetail VALUES (8001, 4001, 306, 6, 25.00, 150.00); 
INSERT INTO ProductTransaction VALUES (4001, 306, 820404146677, DATE '2026-01-02', SYSTIMESTAMP, 6);

-- SALE 4002: Student (Mei Ling) buying sports kit
INSERT INTO Sales VALUES (4002, 081225146644, DATE '2026-01-02', 60.00);
INSERT INTO SalesDetail VALUES (8002, 4002, 204, 1, 35.00, 35.00);
INSERT INTO SalesDetail VALUES (8003, 4002, 205, 1, 25.00, 25.00);
INSERT INTO ProductTransaction VALUES (4002, 204, 081225146644, DATE '2026-01-02', SYSTIMESTAMP, 1);
INSERT INTO ProductTransaction VALUES (4003, 205, 081225146644, DATE '2026-01-02', SYSTIMESTAMP, 1);

-- SALE 4003: Quick snack purchase by Haris
INSERT INTO Sales VALUES (4003, 100512140011, DATE '2026-01-02', 3.00);
INSERT INTO SalesDetail VALUES (8004, 4003, 105, 2, 1.50, 3.00);
INSERT INTO ProductTransaction VALUES (4004, 105, 100512140011, DATE '2026-01-02', SYSTIMESTAMP, 2);

-- FOOD (106-110)
INSERT INTO Product VALUES (106, 10, 'Nasi Lemak Ayam', 4.50, '1 pack', 'Lunch special', 40, DATE '2026-01-01', 10, 'Food');
INSERT INTO Product VALUES (107, 10, 'Sausage Roll', 2.00, '1 pc', 'Bakery', 60, DATE '2026-01-01', 15, 'Food');
INSERT INTO Product VALUES (108, 10, 'Soy Milk', 1.80, '300ml', 'Bottled', 120, DATE '2026-01-02', 30, 'Food');

-- CLOTHING (206-208)
INSERT INTO Product VALUES (206, 50, 'School Tie (Primary)', 12.00, '1 unit', 'Elastic', 50, DATE '2025-12-15', 10, 'Clothing');
INSERT INTO Product VALUES (207, 50, 'Prefect Blazer', 85.00, 'L Size', 'Premium', 10, DATE '2025-12-20', 2, 'Clothing');

-- MERCHANDISE (307-310)
INSERT INTO Product VALUES (307, 60, 'School Lanyard', 5.00, '1 unit', 'Blue Nylon', 200, DATE '2026-01-01', 50, 'Merchandise');
INSERT INTO Product VALUES (308, 60, 'Exam Pad (100s)', 6.50, '1 pack', 'A4 Ruled', 150, DATE '2026-01-01', 40, 'Merchandise');

-- SEASONAL (404-405)
INSERT INTO Product VALUES (404, 40, 'Hari Raya Packet', 2.00, '10 pcs', 'Green packets', 300, DATE '2026-01-02', 50, 'Seasonal');

-- DETAIL TABLES
INSERT INTO Food VALUES (106, 'Mak Cik Kantin', 'Spicy', DATE '2026-01-03');
INSERT INTO Food VALUES (107, 'Daily Bake', 'Savoury', DATE '2026-01-05');
INSERT INTO Food VALUES (108, 'Yeos', 'Original', DATE '2027-01-01');
INSERT INTO SchoolClothing VALUES (206, 'S', 'Dark Blue', NULL, 'N/A');
INSERT INTO SchoolClothing VALUES (207, 'L', 'Black', NULL, 'Long');
INSERT INTO SchoolMerchandise VALUES (307, 'Lanyard', 'Standard');
INSERT INTO SchoolMerchandise VALUES (308, 'Paper', 'A4');
INSERT INTO Seasonal VALUES (404, 'Raya 2026', DATE '2026-03-01', DATE '2026-04-15', 'YES');

-- Restocking Food every few days
INSERT INTO RestockDetail VALUES (9100, 10, 106, 50, SYSTIMESTAMP - 5, 3.00, 150.00, 'INV-B1', 'f1.pdf');
INSERT INTO RestockDetail VALUES (9101, 10, 107, 100, SYSTIMESTAMP - 5, 1.20, 120.00, 'INV-B2', 'f2.pdf');
INSERT INTO RestockDetail VALUES (9102, 10, 108, 200, SYSTIMESTAMP - 2, 1.00, 200.00, 'INV-B3', 'f3.pdf');

-- Bulk Merch Restock
INSERT INTO RestockDetail VALUES (9103, 60, 307, 500, SYSTIMESTAMP - 10, 2.00, 1000.00, 'INV-M1', 'm1.pdf');
INSERT INTO RestockDetail VALUES (9104, 60, 308, 200, SYSTIMESTAMP - 10, 4.00, 800.00, 'INV-M2', 'm2.pdf');

-- Clothing Restock
INSERT INTO RestockDetail VALUES (9105, 50, 207, 15, SYSTIMESTAMP - 20, 60.00, 900.00, 'INV-C1', 'c1.pdf');

-- DAY 1: JANUARY 1st RUSH
INSERT INTO Sales VALUES (5001, 010515105543, DATE '2026-01-01', 25.50);
INSERT INTO SalesDetail VALUES (10001, 5001, 106, 2, 4.50, 9.00);
INSERT INTO SalesDetail VALUES (10002, 5001, 108, 3, 1.80, 5.40);
INSERT INTO SalesDetail VALUES (10003, 5001, 307, 2, 5.00, 10.00);

INSERT INTO Sales VALUES (5002, 020820146678, DATE '2026-01-01', 95.00);
INSERT INTO SalesDetail VALUES (10004, 5002, 207, 1, 85.00, 85.00);
INSERT INTO SalesDetail VALUES (10005, 5002, 308, 1, 6.50, 6.50);

-- DAY 2: JANUARY 2nd RUSH (Morning Break)
-- Multiple students buying snacks simultaneously
INSERT INTO Sales VALUES (5003, 050312101122, DATE '2026-01-02', 6.50);
INSERT INTO SalesDetail VALUES (10006, 5003, 106, 1, 4.50, 4.50);
INSERT INTO SalesDetail VALUES (10007, 5003, 107, 1, 2.00, 2.00);

INSERT INTO Sales VALUES (5004, 061125145566, DATE '2026-01-02', 8.10);
INSERT INTO SalesDetail VALUES (10008, 5004, 108, 2, 1.80, 3.60);
INSERT INTO SalesDetail VALUES (10009, 5004, 106, 1, 4.50, 4.50);

INSERT INTO Sales VALUES (5005, 070102108899, DATE '2026-01-02', 20.00);
INSERT INTO SalesDetail VALUES (10010, 5005, 404, 10, 2.00, 20.00);

-- Teacher buying bulk stationery
INSERT INTO Sales VALUES (5006, 850101104433, DATE '2026-01-02', 130.00);
INSERT INTO SalesDetail VALUES (10011, 5006, 308, 20, 6.50, 130.00);

10 MIX QUERIES FOR YOUR DATABASE
1️⃣ List all products with their supplier names -- (SELECT, FROM, JOIN, ON)
SELECT p.productID, p.productName, p.category, s.companyName AS Supplier
FROM Product p
JOIN Supplier s ON p.supplierID = s.supplierID;

2️⃣ List all Food products that expire after 2025-12-31 -- (SELECT, FROM, JOIN, WHERE, DATE)
SELECT f.productID, f.brand, f.flavour, f.expiryDate, p.productName
FROM Food f
JOIN Product p ON f.productID = p.productID
WHERE f.expiryDate > DATE '2025-12-31';

3️⃣ Total spending amount per user -- (SELECT, FROM, JOIN, GROUP BY, ORDER BY, DESC)
SELECT u.name, SUM(s.totalAmount) AS TotalSpent
FROM Sales s
JOIN UserAccount u ON s.userID = u.userID
GROUP BY u.name
ORDER BY TotalSpent DESC;

4️⃣ finds products whose total quantity sold is higher than the average sales volume of all products. -- (HAVING CLAUSE) 
SELECT p.productName, SUM(sd.quantitySold) AS Total_Sold
FROM SalesDetail sd
JOIN Product p ON sd.productID = p.productID
GROUP BY p.productName
HAVING SUM(sd.quantitySold) > (
    SELECT AVG(SUM(quantitySold)) 
    FROM SalesDetail 
    GROUP BY productID
);

5️⃣ Top 5 most sold products -- (NESTING GROUP FUNCTION) 
SELECT p.productName, SUM(sd.quantitySold) AS TotalSold
FROM SalesDetail sd
JOIN Product p ON sd.productID = p.productID
GROUP BY p.productName
HAVING SUM(sd.quantitySold) >= (
    SELECT AVG(SUM(quantitySold)) 
    FROM SalesDetail 
    GROUP BY productID
)
ORDER BY TotalSold DESC
FETCH FIRST 5 ROWS ONLY;

6️⃣ Sales with product details for a specific user (e.g., userID = 1) -- (LEFT OUTER JOIN & COALESCE)
SELECT u.name, 
       COALESCE(TO_CHAR(s.saleID), 'No Sales') AS Transaction_Ref,
       COALESCE(p.productName, 'N/A') AS Item
FROM UserAccount u
LEFT OUTER JOIN Sales s ON u.userID = s.userID
LEFT OUTER JOIN SalesDetail sd ON s.saleID = sd.saleID
LEFT OUTER JOIN Product p ON sd.productID = p.productID
WHERE u.status = 'ACTIVE'
ORDER BY u.name;

7️⃣ Restock details and total cost per supplier -- (CASE EXPRESSION & JOIN MORE THAN 2 TABLES)
SELECT s.companyName, 
       p.productName, 
       r.totalCost,
       CASE 
          WHEN r.totalCost > 1000 THEN 'HIGH EXPENSE'
          WHEN r.totalCost BETWEEN 500 AND 1000 THEN 'MID EXPENSE'
          ELSE 'LOW EXPENSE'
       END AS Expense_Category
FROM RestockDetail r
JOIN Supplier s ON r.supplierID = s.supplierID
JOIN Product p ON r.productID = p.productID
ORDER BY r.totalCost DESC;

8️⃣ Identify "Dead Stock" (Products never sold) -- (MINUS) 
-- This identifies products that exist in the Product table but NOT in SalesDetail
SELECT productID, productName FROM Product
MINUS
SELECT p.productID, p.productName FROM Product p 
JOIN SalesDetail sd ON p.productID = sd.productID;

9️⃣ Calculates how many months since the last restock -- (SELECT, MONTHS_BETWEEN, ROUND)
SELECT productName,
       ROUND(MONTHS_BETWEEN(SYSDATE, lastDateRestock)) AS months_since_restock
FROM Product;


🔟 Total quantity sold per product -- (SELECT, SUM, FROM, JOIN, ON, GROUP BY, ORDER BY, DESC)
SELECT p.productName, SUM(sd.quantitySold) AS QuantitySold
FROM SalesDetail sd
JOIN Product p ON sd.productID = p.productID
GROUP BY p.productName
ORDER BY QuantitySold DESC;

COMMIT;
COMMIT;
COMMIT;
COMMIT;

SELECT 
    u.name AS Customer_Name, 
    u.email AS Contact,
    p.productName AS Product, 
    sd.quantitySold AS Quantity, 
    sd.totalPrice AS Total_Paid,
    s.saleDate AS Purchase_Date
FROM UserAccount u
JOIN Sales s ON u.userID = s.userID
JOIN SalesDetail sd ON s.saleID = sd.saleID
JOIN Product p ON sd.productID = p.productID
ORDER BY s.saleDate DESC;

-- More Students from different years
INSERT INTO UserAccount VALUES (080520101234, 'Siti Sarah', 'sarah08', 'pwd', 'sarah@sch.my', '011-9998881', 'ACTIVE');
INSERT INTO UserAccount VALUES (091212145678, 'Lim Wei Kang', 'weikang', 'pwd', 'lim@sch.my', '011-9998882', 'ACTIVE');
INSERT INTO UserAccount VALUES (100315102233, 'Arul Selvan', 'arul10', 'pwd', 'arul@sch.my', '011-9998883', 'ACTIVE');

-- Assign to Clubs
INSERT INTO StudentClub VALUES (080520101234, '4 Gamma', DATE '2025-01-05');
INSERT INTO StudentClub VALUES (091212145678, '3 Beta', DATE '2025-01-05');

-- Jan 2025: Huge Uniform Sales
INSERT INTO Sales VALUES (7001, 080520101234, DATE '2025-01-05', 107.00);
INSERT INTO SalesDetail VALUES (20001, 7001, 207, 1, 85.00, 85.00); -- Blazer
INSERT INTO SalesDetail VALUES (20002, 7001, 203, 1, 22.00, 22.00); -- Shirt

-- Feb 2025: Stationery & Chinese New Year
INSERT INTO Sales VALUES (7002, 091212145678, DATE '2025-02-10', 47.00);
INSERT INTO SalesDetail VALUES (20003, 7002, 302, 1, 45.00, 45.00); -- Calculator
INSERT INTO SalesDetail VALUES (20004, 7002, 401, 1, 2.00, 2.00);   -- Angpow

-- May 2025: Sports Day Prep
INSERT INTO Sales VALUES (7003, 100315102233, DATE '2025-05-15', 60.00);
INSERT INTO SalesDetail VALUES (20005, 7003, 204, 1, 35.00, 35.00); -- Trackbottoms
INSERT INTO SalesDetail VALUES (20006, 7003, 205, 1, 25.00, 25.00); -- Jersey

-- August 2025: Trial Exam Prep
INSERT INTO Sales VALUES (7004, 010515105543, DATE '2025-08-20', 17.50);
INSERT INTO SalesDetail VALUES (20007, 7004, 308, 2, 6.50, 13.00); -- 2 Exam Pads
INSERT INTO SalesDetail VALUES (20008, 7004, 106, 1, 4.50, 4.50);   -- Nasi Lemak Ayam

-- Q1 Restock (January)
INSERT INTO RestockDetail VALUES (9500, 20, 207, 50, TIMESTAMP '2025-01-01 08:00:00', 50.00, 2500.00, 'INV-25-001', 'jan_stock.pdf');
INSERT INTO RestockDetail VALUES (9501, 30, 302, 20, TIMESTAMP '2025-01-02 09:30:00', 30.00, 600.00, 'INV-25-002', 'jan_calc.pdf');

-- Q2 Restock (April)
INSERT INTO RestockDetail VALUES (9502, 10, 106, 200, TIMESTAMP '2025-04-15 07:45:00', 2.50, 500.00, 'INV-25-003', 'apr_food.pdf');
INSERT INTO RestockDetail VALUES (9503, 50, 205, 100, TIMESTAMP '2025-05-10 10:00:00', 15.00, 1500.00, 'INV-25-004', 'may_sports.pdf');

-- Q3 Restock (August)
INSERT INTO RestockDetail VALUES (9504, 60, 308, 300, TIMESTAMP '2025-08-01 08:15:00', 4.00, 1200.00, 'INV-25-005', 'aug_exam.pdf');

-- Q4 Restock (November)
INSERT INTO RestockDetail VALUES (9505, 40, 404, 500, TIMESTAMP '2025-11-20 09:00:00', 1.00, 500.00, 'INV-25-006', 'nov_seasonal.pdf');

-- JAN: Back to School
INSERT INTO Sales VALUES (8001, 010515105543, DATE '2025-01-10', 85.00);
INSERT INTO SalesDetail VALUES (30001, 8001, 207, 1, 85.00, 85.00);

-- FEB: Mid-term
INSERT INTO Sales VALUES (8002, 020820146678, DATE '2025-02-14', 15.00);
INSERT INTO SalesDetail VALUES (30002, 8002, 201, 1, 15.00, 15.00);

-- MAR: Stationery rush
INSERT INTO Sales VALUES (8003, 080520101234, DATE '2025-03-20', 4.00);
INSERT INTO SalesDetail VALUES (30003, 8003, 301, 1, 4.00, 4.00);

-- APR: Food focus
INSERT INTO Sales VALUES (8004, 050312101122, DATE '2025-04-12', 4.50);
INSERT INTO SalesDetail VALUES (30004, 8004, 106, 1, 4.50, 4.50);

-- MAY: Sports Month
INSERT INTO Sales VALUES (8005, 061125145566, DATE '2025-05-25', 60.00);
INSERT INTO SalesDetail VALUES (30005, 8005, 204, 1, 35.00, 35.00);
INSERT INTO SalesDetail VALUES (30006, 8005, 205, 1, 25.00, 25.00);

-- JUN/JUL/AUG: General Daily Sales
INSERT INTO Sales VALUES (8006, 070102108899, DATE '2025-06-15', 5.00);
INSERT INTO SalesDetail VALUES (30007, 8006, 307, 1, 5.00, 5.00);
INSERT INTO Sales VALUES (8007, 850101104433, DATE '2025-07-05', 13.00);
INSERT INTO SalesDetail VALUES (30008, 8007, 308, 2, 6.50, 13.00);
INSERT INTO Sales VALUES (8008, 010515105543, DATE '2025-08-18', 45.00);
INSERT INTO SalesDetail VALUES (30009, 8008, 302, 1, 45.00, 45.00);

-- SEP/OCT/NOV/DEC: Year End
INSERT INTO Sales VALUES (8009, 100315102233, DATE '2025-09-10', 2.00);
INSERT INTO SalesDetail VALUES (30010, 8009, 401, 1, 2.00, 2.00);
INSERT INTO Sales VALUES (8010, 080520101234, DATE '2025-10-22', 2.50);
INSERT INTO SalesDetail VALUES (30011, 8010, 101, 1, 2.50, 2.50);
INSERT INTO Sales VALUES (8011, 091212145678, DATE '2025-11-30', 20.00);
INSERT INTO SalesDetail VALUES (30012, 8011, 404, 10, 2.00, 20.00);
INSERT INTO Sales VALUES (8012, 100512140011, DATE '2025-12-15', 2.20);
INSERT INTO SalesDetail VALUES (30013, 8012, 104, 1, 2.20, 2.20);

-- MARCH & APRIL: Daily Snacks and Stationery
INSERT INTO Sales (saleID, userID, saleDate, totalAmount) VALUES (8100, 010515105543, DATE '2025-03-05', 12.50);
INSERT INTO SalesDetail VALUES (40001, 8100, 101, 5, 2.50, 12.50);

INSERT INTO Sales (saleID, userID, saleDate, totalAmount) VALUES (8101, 090220104422, DATE '2025-04-10', 4.00);
INSERT INTO SalesDetail VALUES (40002, 8101, 301, 1, 4.00, 4.00);

-- JUNE & JULY: Mid-Year Refresh
INSERT INTO Sales (saleID, userID, saleDate, totalAmount) VALUES (8102, 110830105533, DATE '2025-06-12', 22.00);
INSERT INTO SalesDetail VALUES (40003, 8102, 203, 1, 22.00, 22.00);

INSERT INTO Sales (saleID, userID, saleDate, totalAmount) VALUES (8103, 100101101155, DATE '2025-07-20', 15.00);
INSERT INTO SalesDetail VALUES (40004, 8103, 201, 1, 15.00, 15.00);

-- SEPTEMBER & OCTOBER: Final Exam Preparation
INSERT INTO Sales (saleID, userID, saleDate, totalAmount) VALUES (8104, 050312101122, DATE '2025-09-15', 51.50);
INSERT INTO SalesDetail VALUES (40005, 8104, 302, 1, 45.00, 45.00); -- Scientific Calc
INSERT INTO SalesDetail VALUES (40006, 8104, 308, 1, 6.50, 6.50);   -- Exam Pad

INSERT INTO Sales (saleID, userID, saleDate, totalAmount) VALUES (8105, 850101104433, DATE '2025-10-05', 65.00);
INSERT INTO SalesDetail VALUES (40007, 8105, 308, 10, 6.50, 65.00); -- Bulk Exam Pads for class

-- Q1: Heavy Clothing Restock
INSERT INTO RestockDetail VALUES (9601, 20, 203, 100, TIMESTAMP '2025-01-15 10:00:00', 12.00, 1200.00, 'INV-25-C1', 'jan_shirts.pdf');

-- Q2: Sports Gear Restock for Sports Day
INSERT INTO RestockDetail VALUES (9602, 50, 204, 60, TIMESTAMP '2025-05-01 09:00:00', 20.00, 1200.00, 'INV-25-S1', 'may_tracks.pdf');
INSERT INTO RestockDetail VALUES (9603, 50, 205, 80, TIMESTAMP '2025-05-02 09:00:00', 15.00, 1200.00, 'INV-25-S2', 'may_jerseys.pdf');

-- Q3: Tech and Lanyards
INSERT INTO RestockDetail VALUES (9604, 60, 306, 50, TIMESTAMP '2025-08-10 14:00:00', 18.00, 900.00, 'INV-25-T1', 'aug_usb.pdf');
INSERT INTO RestockDetail VALUES (9605, 60, 307, 300, TIMESTAMP '2025-09-01 11:00:00', 2.00, 600.00, 'INV-25-M1', 'sep_lanyard.pdf');

-- Q4: Seasonal Prep
INSERT INTO RestockDetail VALUES (9606, 40, 401, 500, TIMESTAMP '2025-12-10 08:30:00', 0.50, 250.00, 'INV-25-CNY', 'dec_angpow.pdf');

-- WEEK 1: Jan 1 (Thu) to Jan 2 (Fri) [Short Week]
INSERT INTO Sales VALUES (7001, 010515105543, DATE '2026-01-01', 15.00);
INSERT INTO Sales VALUES (7002, 050312101122, DATE '2026-01-01', 25.50);
INSERT INTO Sales VALUES (7003, 081225146644, DATE '2026-01-01', 4.00);
INSERT INTO Sales VALUES (7004, 781212105544, DATE '2026-01-01', 12.00);
INSERT INTO Sales VALUES (7005, 090220104422, DATE '2026-01-01', 8.50);
INSERT INTO Sales VALUES (7006, 010515105543, DATE '2026-01-02', 22.00);
INSERT INTO Sales VALUES (7007, 110830105533, DATE '2026-01-02', 45.00);
INSERT INTO Sales VALUES (7008, 050312101122, DATE '2026-01-02', 3.00);
INSERT INTO Sales VALUES (7009, 850101104433, DATE '2026-01-02', 17.50);
INSERT INTO Sales VALUES (7010, 100512140011, DATE '2026-01-02', 5.00);

-- WEEK 2: Jan 5 (Mon) to Jan 9 (Fri)
BEGIN
  FOR d IN 5..9 LOOP
    FOR t IN 1..5 LOOP
      INSERT INTO Sales (saleID, userID, saleDate, totalAmount) 
      VALUES (7010 + (d*5) + t, 010515105543, TO_DATE('2026-01-'||d, 'YYYY-MM-DD'), ROUND(DBMS_RANDOM.VALUE(5, 50), 2));
    END LOOP;
  END LOOP;
END;
/

-- WEEK 3: Jan 12 (Mon) to Jan 16 (Fri)
BEGIN
  FOR d IN 12..16 LOOP
    FOR t IN 1..5 LOOP
      INSERT INTO Sales (saleID, userID, saleDate, totalAmount) 
      VALUES (7100 + (d*5) + t, 050312101122, TO_DATE('2026-01-'||d, 'YYYY-MM-DD'), ROUND(DBMS_RANDOM.VALUE(5, 50), 2));
    END LOOP;
  END LOOP;
END;
/

-- WEEK 4: Jan 19 (Mon) to Jan 23 (Fri)
BEGIN
  FOR d IN 19..23 LOOP
    FOR t IN 1..5 LOOP
      INSERT INTO Sales (saleID, userID, saleDate, totalAmount) 
      VALUES (7200 + (d*5) + t, 081225146644, TO_DATE('2026-01-'||d, 'YYYY-MM-DD'), ROUND(DBMS_RANDOM.VALUE(5, 50), 2));
    END LOOP;
  END LOOP;
END;
/

-- WEEK 5: Jan 26 (Mon) to Jan 30 (Fri)
BEGIN
  FOR d IN 26..30 LOOP
    FOR t IN 1..5 LOOP
      INSERT INTO Sales (saleID, userID, saleDate, totalAmount) 
      VALUES (7300 + (d*5) + t, 850101104433, TO_DATE('2026-01-'||d, 'YYYY-MM-DD'), ROUND(DBMS_RANDOM.VALUE(5, 50), 2));
    END LOOP;
  END LOOP;
END;
/

COMMIT;

-- INSERT
INSERT INTO Supplier VALUES (1, 'ABC Supplies', 'Ali Ahmad', '0123456789', 'Johor Bahru');

-- SELECT
SELECT * FROM Supplier;

-- UPDATE
UPDATE Supplier
SET contactNumber = '0198887777'
WHERE supplierID = 1;

-- DELETE
DELETE FROM Supplier WHERE supplierID = 1;

-- INSERT
INSERT INTO Product VALUES (101, 1, 'Mineral Water', 1.50, '500ml', 'Drinking water', 100, SYSDATE, 20, 'Food');

-- SELECT
SELECT * FROM Product;

-- UPDATE
UPDATE Product
SET quantityInStock = 80
WHERE productID = 101;

-- DELETE
DELETE FROM Product WHERE productID = 101;

-- INSERT
INSERT INTO UserAccount VALUES (10, 'Farah Delisya', 'farah01', 'pass123','farah@email.com', '0112233445', 'ACTIVE');

-- SELECT
SELECT * FROM UserAccount;

-- UPDATE
UPDATE UserAccount
SET status = 'INACTIVE'
WHERE userID = 10;

-- DELETE
DELETE FROM UserAccount WHERE userID = 10;

-- INSERT
INSERT INTO ProductTransaction VALUES (1001, 101, 10, SYSDATE, SYSTIMESTAMP, 2);

-- SELECT
SELECT * FROM ProductTransaction;

-- UPDATE
UPDATE ProductTransaction
SET quantitySold = 3
WHERE transactionID = 1001;

-- DELETE
DELETE FROM ProductTransaction WHERE transactionID = 1001;

-- INSERT
INSERT INTO RestockDetail VALUES (501, 1, 101, 50, SYSTIMESTAMP, 1.00, 50.00, 'INV001', 'invoice1.pdf');

-- SELECT
SELECT * FROM RestockDetail;

-- UPDATE
UPDATE RestockDetail
SET quantityRestock = 60, totalCost = 60.00
WHERE restockDetailID = 501;

-- DELETE
DELETE FROM RestockDetail WHERE restockDetailID = 501;

-- INSERT
INSERT INTO Sales VALUES (9001, 10, SYSDATE, 15.00);

-- SELECT
SELECT * FROM Sales;

-- UPDATE
UPDATE Sales
SET totalAmount = 18.00
WHERE saleID = 9001;

-- DELETE
DELETE FROM Sales WHERE saleID = 9001;

-- INSERT
INSERT INTO SalesDetail VALUES (8001, 9001, 101, 3, 1.50, 4.50);

-- SELECT
SELECT * FROM SalesDetail;

-- UPDATE
UPDATE SalesDetail
SET quantitySold = 4, totalPrice = 6.00
WHERE salesDetailID = 8001;

-- DELETE
DELETE FROM SalesDetail WHERE salesDetailID = 8001;

-- INSERT
INSERT INTO Teacher VALUES (10, 'Mathematics');

-- SELECT
SELECT * FROM Teacher;

-- UPDATE
UPDATE Teacher
SET subjectTeaching = 'Science'
WHERE userID = 10;

-- DELETE
DELETE FROM Teacher WHERE userID = 10;

-- INSERT
INSERT INTO StudentClub VALUES (10, '5 Alpha', SYSDATE);

-- SELECT
SELECT * FROM StudentClub;

-- UPDATE
UPDATE StudentClub
SET class = '5 Beta'
WHERE userID = 10;

-- DELETE
DELETE FROM StudentClub WHERE userID = 10;

-- INSERT
INSERT INTO Food VALUES (101, 'Spritzer', 'Original', SYSDATE + 365);

-- SELECT
SELECT * FROM Food;

-- UPDATE
UPDATE Food
SET flavour = 'Lemon'
WHERE productID = 101;

-- DELETE
DELETE FROM Food WHERE productID = 101;

-- INSERT
INSERT INTO SchoolClothing VALUES (102, 'M', 'Blue', 'Red', 'Short');

-- SELECT
SELECT * FROM SchoolClothing;

-- UPDATE
UPDATE SchoolClothing
SET sizeCloth = 'L'
WHERE productID = 102;

-- DELETE
DELETE FROM SchoolClothing WHERE productID = 102;

-- INSERT
INSERT INTO SchoolMerchandise VALUES (103, 'Mug', 'Medium');

-- SELECT
SELECT * FROM SchoolMerchandise;

-- UPDATE
UPDATE SchoolMerchandise
SET sizeMerch = 'Large'
WHERE productID = 103;

-- DELETE
DELETE FROM SchoolMerchandise WHERE productID = 103;

-- INSERT
INSERT INTO Seasonal VALUES (104, 'Sports Day', SYSDATE, SYSDATE + 7, 'YES');

-- SELECT
SELECT * FROM Seasonal;

-- UPDATE
UPDATE Seasonal
SET limitedEdition = 'NO'
WHERE productID = 104;

-- DELETE
DELETE FROM Seasonal WHERE productID = 104;

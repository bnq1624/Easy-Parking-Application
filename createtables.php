<?php
require_once "database.php";

$db = new Database();

# Create the tables
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT UNIQUE KEY,
    username VARCHAR(100) NOT NULL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    type ENUM('Administrator', 'User') NOT NULL
);";

if ($db->query($sql)) {
    echo "<p>Table 'users' created successfully.</p>";
} else {
    die("Error creating tables: " . $db->conn->error);
}

$sql = "
CREATE TABLE IF NOT EXISTS parkingLocations (
    id INT AUTO_INCREMENT UNIQUE KEY,
    location VARCHAR(100) NOT NULL PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    capacity INT NOT NULL,
    costPH DECIMAL(4, 2) NOT NULL,
    lateCheckoutCostPH DECIMAL(4, 2) NOT NULL,
    availableSpaces INT NOT NULL
);";

if ($db->query($sql)) {
    echo "<p>Table 'parkingLocations' created successfully.</p>";
} else {
    die("Error creating tables: " . $db->conn->error);
}

$sql = "
CREATE TABLE IF NOT EXISTS checkIns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    checkInTime DATETIME NOT NULL,
    intendedDuration INT NOT NULL,
    checkOutTime DATETIME,
    FOREIGN KEY (username) REFERENCES users(username),
    FOREIGN KEY (location) REFERENCES parkingLocations(location)
);";

if ($db->query($sql)) {
    echo "<p>Table 'checkIns' created successfully.</p>";
} else {
    die("Error creating tables: " . $db->conn->error);
}

$sql = "
CREATE TABLE IF NOT EXISTS pastCheckOuts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    checkInTime DATETIME NOT NULL,
    checkOutTime DATETIME NOT NULL,
    intendedDuration INT NOT NULL
)";

if($db->query($sql)) {
    echo "<p>Table 'pastCheckOuts' created successfully.</p>";
}else {
    die("Error creating tables: " . $db->conn->error);
}

# encode password before inserting into the database
$encodedPasswordBinh = md5("binh1234");
$encodedPasswordKay = md5("kay1234");
$encodedPasswordKen = md5("ken1234");

# insert initial values for users
$sqlInsert = "
INSERT INTO users (username, name, surname, phone, email, password, type) VALUES
('binh', 'Binh', 'Nguyen', '0123456789', 'binhnguyen@gmail.com', '{$encodedPasswordBinh}', 'Administrator'),
('kay', 'Kay', 'Tran', '0234567891', 'kaytran@gmail.com', '{$encodedPasswordKay}', 'User'),
('ken', 'Ken', 'Nguyen', '0345678912', 'kennguyen@gmail.com', '{$encodedPasswordKen}', 'User');";

if ($db->query($sqlInsert)) {
    echo "<p>Data for 'users' table inserted successfully.</p>";
} else {
    die("Error inserting data: " . $db->conn->error);
}

# insert initial values for parking locations
$sqlInsert = "
INSERT INTO parkingLocations (location, description, capacity, costPH, lateCheckoutCostPH, availableSpaces) VALUES
('Wollongong Central', 'A parking station near Wollongong Central Shopping Mall', 100, 3, 2, 50),
('Fairy Meadow', 'Parking station next to Fairy Meadow Railway Station', 10, 2, 2, 1),
('North Wollongong', 'Public parking station near North Wollongong Beach', 30, 2, 1, 5),
('Marrickville', 'Parking station in Marrickville, Sydney', 50, 4, 3, 6),
('Central Station', 'Parking slots next to Central Station, Sydney', 200, 2, 3, 70);";

if ($db->query($sqlInsert)) {
    echo "<p>Data for 'parkingLocations' table inserted successfully.</p>";
} else {
    die("Error inserting data: " . $db->conn->error);
}

$db->close();
?>

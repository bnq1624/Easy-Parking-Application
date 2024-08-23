<?php
class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    # sign up functionality
    public function signUp($username, $name, $surname, $phone, $email, $password, $type) {
        $encodedPassword = md5($password);

        # insert data that the user entered into the database
        $sql = "INSERT INTO users (username, name, surname, phone, email, password, type) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssssss", $username, $name, $surname, $phone, $email, $encodedPassword, $type);

        return $stmt->execute();
    }

    # log in functionality
    public function login($username, $password) {
        # retrieve all data from the row that has the username entered by user
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            # if the password entered by the user matches with the password in the database, return the data
            if (md5($password) == $row["password"]) {
                return $row;
            }
        }

        return false;
    }

    # check if the username exists already when signing up
    public function userExists($username) {
        $sql = "SELECT username FROM users WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }


    ### used to list all parking locations with currently available spaces functionality
    public function listParking() {
        $sql = "SELECT location, availableSpaces FROM parkingLocations";
        $result = $this->db->query($sql);

        return $result;
    }

    // retrieve location, cost per hour, and late check out cost per hour from the entered parking location
    ### used to search for a parking location functionality
    public function getDetailsAfterSearch($location) {     # user enters location
        $sql = "SELECT location, costPH, lateCheckoutCostPH FROM parkingLocations WHERE location = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $location);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result;
        } else {
            return null;
        }
    }

    # prevent duplicated checkIn (1 user check-in to the same location multiple times)
    ### used to check-in for a parking functionality
    public function checkDuplicatedCheckIn($username, $location) {
        $sqlDuplicate = "SELECT * FROM checkIns WHERE username = ? AND location = ?";
        $stmt = $this->db->prepare($sqlDuplicate);
        $stmt->bind_param("ss", $username, $location);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
            return true;
        }else {
            return false;
        }
    }

    // Check for currently available spaces
    ### used to check-in for a parking functionality
    public function checkAvailableSpaces($location) {       # user enters location
        $sql = "SELECT availableSpaces FROM parkingLocations WHERE location = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $location);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $availableSpaces = $row["availableSpaces"];
            return $availableSpaces;
        } else {
            return null;
        }
    }

    ### used to check-in for a parking
    public function checkIn($username, $location, $intendedDuration, $adminUsername = null) {
        if($this->checkDuplicatedCheckIn($username, $location)) {
            $error = "duplicatedCheckIn";

            $duplicatedMessage = "You have checked in for this parking location already.";
            
            return [
                "error" => $error,
                "duplicatedMessage" => $duplicatedMessage
            ];
        }else {
            # retrieve available space in the entered location
            $availableSpaces = $this->checkAvailableSpaces($location);

            # only allows check in if the parking location still have available spaces
            if ($availableSpaces > 0) {
                $checkInTime = date("Y-m-d H:i:s");
                $checkOutTime = date("Y-m-d H:i:s", strtotime("$checkInTime + $intendedDuration hours"));

                # add a record into 'checkIns' table
                $sqlAdd = "INSERT INTO checkIns (username, location, checkInTime, intendedDuration, checkOutTime) VALUES (?, ?, ?, ?, ?)";
                $stmtAdd = $this->db->prepare($sqlAdd);
                $stmtAdd->bind_param("sssds", $username, $location, $checkInTime, $intendedDuration, $checkOutTime);
                $stmtAdd->execute();

                # after checking in for a parking location successfully, decrease the available spaces of that parking location by 1
                $sqlUpdate = "UPDATE parkingLocations SET availableSpaces = availableSpaces - 1 WHERE location = ?";
                $stmtUpdate = $this->db->prepare($sqlUpdate);
                $stmtUpdate->bind_param("s", $location);
                $stmtUpdate->execute();

                // reuse the function to retrieve cost and late checkout cost
                $searchedInfoResult = $this->getDetailsAfterSearch($location);
                $searchedInfo = $searchedInfoResult->fetch_assoc();
                $totalCost = $intendedDuration * $searchedInfo["costPH"];
                $lateCheckoutCostPH = $searchedInfo["lateCheckoutCostPH"];

                return [
                    "error" => null,
                    "checkInTime" => $checkInTime,
                    "checkOutTime" => $checkOutTime,
                    "totalCost" => $totalCost,
                    "lateCheckoutCostPH" => $lateCheckoutCostPH
                ];
            } else {
                $error = "capacityReached";
                $capacityReachedMessage = "Check-in unsuccessful. The parking location has reached its capacity.";

                return [
                    "error" => $error,
                    "capacityReachedMessage" => $capacityReachedMessage
                ];
            }
        }
    }

    // Retrieve current check-ins for a user
    public function listCurrentCheckIns($username) {
        $sql = "SELECT location, checkInTime, checkOutTime, intendedDuration FROM checkIns WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result;
    }

    // Calculate cost based on check-out time
    public function calculateCost($checkInTime, $checkOutTime, $intendedDuration, $location) {
        $sql = "SELECT costPH, lateCheckoutCostPH FROM parkingLocations WHERE location = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $location);
        $stmt->execute();
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();

        $costPH = $row["costPH"];
        $lateCheckoutCostPH = $row["lateCheckoutCostPH"];

        $checkInTimeInSeconds = strtotime($checkInTime);
        $checkOutTimeInSeconds = strtotime($checkOutTime);

        $duration = ($checkOutTimeInSeconds - $checkInTimeInSeconds) / 3600;    # change from second (output of strtotime() function) to hour
        $extraHours = max(0, $duration - $intendedDuration);

        $totalCost = $intendedDuration * $costPH + $extraHours * $lateCheckoutCostPH;

        return [
            "totalCost" => $totalCost,
            "extraHours" => $extraHours,
            "lateCheckoutCost" => $extraHours * $lateCheckoutCostPH
        ];
    }

    // Check out from a parking location
    public function checkOut($username, $location, $adminUsername = null) {
        $sql = "SELECT checkInTime, checkOutTime, intendedDuration FROM checkIns WHERE username = ? AND location = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $username, $location);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $checkInTime = $row["checkInTime"];
        $checkOutTime = date("Y-m-d H:i:s");            # Current time when user performs check-out function
        $intendedDuration = $row["intendedDuration"];

        // Delete the check-in record
        $sqlDelete = "DELETE FROM checkIns WHERE username = ? AND location = ?";
        $stmtDelete = $this->db->prepare($sqlDelete);
        $stmtDelete->bind_param("ss", $username, $location);
        $stmtDelete->execute();

        // After checking out from a parking location, increase the available spaces by 1
        $sqlUpdate = "UPDATE parkingLocations SET availableSpaces = availableSpaces + 1 WHERE location = ?";
        $stmtUpdate = $this->db->prepare($sqlUpdate);
        $stmtUpdate->bind_param("s", $location);
        $stmtUpdate->execute();

        // retrieve the cost details
        $costDetails = $this->calculateCost($checkInTime, $checkOutTime, $intendedDuration, $location);

        return [
            "totalCost" => $costDetails["totalCost"],
            "extraHours" => $costDetails["extraHours"],
            "lateCheckoutCost" => $costDetails["lateCheckoutCost"],
            "checkOutTime" => $checkOutTime
        ];
    }

    public function addPastCheckOut($username, $location, $checkInTime, $checkOutTime, $intendedDuration) {
        $sql = "INSERT INTO pastCheckOuts (username, location, checkInTime, checkOutTime, intendedDuration) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssi", $username, $location, $checkInTime, $checkOutTime, $intendedDuration);
        $stmt->execute();
    }

    public function listPastCheckOuts($username) {
        $sql = "SELECT location FROM pastCheckOuts WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result;
    }

    // List all users
    public function listUsers() {
        $sql = "SELECT username FROM users";
        $result = $this->db->query($sql);
    
        return $result;
    }

    // Search information of a user
    public function searchUser($username) {
        $sql = "SELECT username, name, phone, email FROM users WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
    
        return $result;
    }

    // List all users are checking in parking locations
    public function listCheckInUsers() {
        $sql = "SELECT username, location FROM checkIns";
        $result = $this->db->query($sql);
    
        return $result;
    }

    // Insert a parking location
    public function insertLocation($location, $description, $capacity, $costPH, $lateCheckoutCostPH, $availableSpaces) {
        $sql = "INSERT INTO parkingLocations (location, description, capacity, costPH, lateCheckoutCostPH, availableSpaces) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssiddi", $location, $description, $capacity, $costPH, $lateCheckoutCostPH, $availableSpaces);
        $stmt->execute();

        $result = $stmt->affected_rows;
        return $result > 0;
    }

    // Edit a parking location
    public function editLocation($location, $newCapacity) {
        $sql = "UPDATE parkingLocations SET capacity = ? WHERE location = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("is", $newCapacity, $location);
        $stmt->execute();

        $result = $stmt->affected_rows;
        return $result > 0;
    }
}
?>

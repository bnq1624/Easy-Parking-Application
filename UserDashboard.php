<?php
session_start();
if (!isset($_SESSION["authenticated"]) || $_SESSION["type"] != "User") {
    header("Location: startPage.php");
    exit();
}

require_once "Database.php";
require_once "User.php";

$db = new Database();
$user = new User($db);

### list all parking locations with currently available spaces functionality
if(isset($_POST["showLocations"])) {
    $locationsAndSpaces = $user->listParking();
}
$showLocations = isset($_POST["showLocations"]) && $_POST["showLocations"] == "true";   # show/hide the displayed locations


### search for a parking location functionality
$searchedInfo = "";
if (isset($_POST["searchLocation"])) {
    $location = $_POST["location"];
    $searchedInfoResult = $user->getDetailsAfterSearch($location);    # retrieve an array [location, costPH, lateCheckoutCostPH]
    $searchedInfo = $searchedInfoResult->fetch_assoc();
}


### used for checking-in functionality
$checkInMessage = "";
if (isset($_POST["checkIn"])) {
    $location = $_POST["checkInLocation"]; 
    $intendedDuration = $_POST["intendedDuration"];
    $result = $user->checkIn($_SESSION["username"], $location, $intendedDuration);

    # display information based on each case
    if($result["error"] == "duplicatedCheckIn") {
        $checkInMessage = $result["duplicatedMessage"];
    }elseif($result["error"] == "capacityReached") {
        $checkInMessage = $result["capacityReachedMessage"];
    }else {
        $checkInMessage = "<p>Check-in successful!</p>";
        $checkInMessage .= "<p>Check-in time:" . $result["checkInTime"] . "</p>";
        $checkInMessage .= "<p>Check-out time:" . $result["checkOutTime"] . "</p>";
        $checkInMessage .= "<p>Total cost: $" . $result["totalCost"] . "</p>";
        $checkInMessage .= "<p>Late check-out cost per hour: $" . $result["lateCheckoutCostPH"] . "</p>";
    }
}


### used for displaying current check-ins and check-out functionality
$currentCheckIns = [];
if (isset($_POST["showCheckIns"])) {
    $currentCheckInsResult = $user->listCurrentCheckIns($_SESSION["username"]);
    
    while ($row = $currentCheckInsResult->fetch_assoc()) {
        $currentCheckIns[] = $row;
    }
}

### used for check-out functionality
$checkOutMessage = "";
if (isset($_POST["checkOut"])) {
    $location = $_POST["location"];

    # before checking out, retrieve the check-in details
    ### used for listing the previous using check-ins in the past
    $currentCheckInsResult = $user->listCurrentCheckIns($_SESSION["username"]);

    while ($row = $currentCheckInsResult->fetch_assoc()) {
        if ($row["location"] == $location) {        # if the location that the user check-out matches with location in the currentCheckInList
            $checkInDetails = $row;                 # retrieve check-in information of that matching check-in row (that is about to be checked out)      
            break;
        }
    }

    # after successfully retrieving the check-in details of the parking to be checked out, perform check out
    $result = $user->checkOut($_SESSION["username"], $location);

    if(isset($checkInDetails)) {
        # retrieve the information of that record (which the user checked out)
        $location = $checkInDetails["location"];
        $checkInTime = $checkInDetails["checkInTime"];
        $checkOutTime = $result["checkOutTime"];                    // checkout time is retrieved after checking out
        $intendedDuration = $checkInDetails["intendedDuration"];

        # add that record into the past check-out
        $user->addPastCheckOut($_SESSION["username"], $location, $checkInTime, $checkOutTime, $intendedDuration);
    }

    $checkOutMessage = "You have successfully checked out from $location.";
    $checkOutMessage .= "<p>Total cost: $" . $result["totalCost"] . "</p>";
    if ($result["extraHours"] > 0) {
        $checkOutMessage .= "<p>You checked out " . $result["extraHours"] . " hours late.</p>";
        $checkOutMessage .= "<p>Additional cost for late check-out: $" . $result["lateCheckoutCost"] . "</p>";
    }

    # Update current checkins after checking out
    $currentCheckInsResult = $user->listCurrentCheckIns($_SESSION["username"]);
    $currentCheckIns = [];
    while ($row = $currentCheckInsResult->fetch_assoc()) {
        $currentCheckIns[] = $row;
    }
}


### used for listing past checkout functionality
$pastCheckOuts = [];
if(isset($_POST["showPastCheckOuts"])) {
    $pastCheckOutsResult = $user->listPastCheckOuts($_SESSION["username"]);
    while($row = $pastCheckOutsResult->fetch_assoc()) {
        $pastCheckOuts[] = $row;
    }
}

$db->close();
?>

<html>
<head>
    <title>User Dashboard</title>
    <style>
        body {
            font-family: Georgia;
            background-color: gray;
        }

        #header {
            text-align: center;
            color: white;
            background-color: green;
            padding: 5px;
        }

        #container {
            width: 70%;
            margin: auto;
        }

        .section {
            padding: 15px;
            margin: 15px;
            background-color: white;
        }

        .button {
            border: none;
            color: white;
            background-color: #40a040;
            border-radius: 5px;
            cursor: pointer;
            padding: 10px 20px;
        }

        .button:hover {
            background-color: #89c489;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: lightskyblue;
        }
    </style>
</head>
<body>
    <div id="header">
        <h2>Hi <?php echo $_SESSION["username"]; ?>, Welcome to Easy Parking</h2>
    </div>

    <div id="container">
        <!--------------------------------- List All Parking Locations ------------------------------>
        <div class="section">
        <h3>All Parking Locations</h3>
            <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                <?php if (!$showLocations): ?>
                    <input type="hidden" name="showLocations" value="true" />
                    <button type="submit" class="button">Show Parking Locations</button>
                <?php else: ?>
                    <input type="hidden" name="showLocations" value="false" />
                    <button type="submit" class="button">Hide Parking Locations</button>
                <?php endif; ?>
            </form>

            <?php if ($showLocations): ?>
                <table>
                    <tr>
                        <th>Location</th>
                        <th>Available Spaces</th>
                    </tr>
                    <?php while ($row = $locationsAndSpaces->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row["location"]; ?></td>
                            <td><?php echo $row["availableSpaces"]; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php endif; ?>
        </div>

        <!-------------------------- Search for Parking Location ----------------------------->
        <div class="section">
            <h3>Search for Parking Location</h3>
            <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                <input type="text" name="location" placeholder="Enter a Parking Location" required />
                <button type="submit" name="searchLocation" class="button">Search</button>
            </form>

            <?php if (isset($_POST["searchLocation"])): ?>
                <?php if ($searchedInfo): ?>
                    <table>
                        <tr>
                            <th>Location</th>
                            <th>Parking cost per hour</th>
                            <th>Parking cost per hour if check-out late</th>
                        </tr>
                        <tr>
                            <td><?php echo $searchedInfo["location"]; ?></td>
                            <td><?php echo $searchedInfo["costPH"]; ?></td>
                            <td><?php echo $searchedInfo["lateCheckoutCostPH"]; ?></td>
                        </tr>
                    </table>
                <?php else: ?>
                    <p>There is no matching location with your entered location, please try again.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-----------------------------Check-in for a parking ---------------------------->
        <div class="section">
            <h3>Check-in for Parking</h3>
            <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                <input type="text" name="checkInLocation" placeholder="Enter Parking Location" required />
                <input type="number" name="intendedDuration" placeholder="Enter Intended Duration" required />
                <button type="submit" name="checkIn" class="button">Check In</button>
            </form>

            <?php if (isset($_POST["checkIn"])): ?>
                <p><?php echo $checkInMessage; ?></p>
            <?php endif; ?>
        </div>

        <!----------------------------- Display Current Check-ins and Checkout ---------------------------->
        <div class="section">
            <h3>Check-out</h3>
            <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                <button type="submit" name="showCheckIns" class="button">Show your current Check-ins</button>
            </form>

            <?php if ($checkOutMessage): ?>
                <p><?php echo $checkOutMessage; ?></p>
            <?php endif; ?>

            <?php if (isset($_POST["showCheckIns"])): ?>
                <?php if (count($currentCheckIns) > 0): ?>
                    <table>
                        <tr>
                            <th>Your current check-ins</th>
                            <th>Checkout</th>
                        </tr>
                        <?php foreach ($currentCheckIns as $currentCheckIn): ?>
                            <tr>
                                <td><?php echo $currentCheckIn["location"]; ?> (Check-in Time: <?php echo $currentCheckIn["checkInTime"]; ?>, Check-out Time: <?php echo $currentCheckIn["checkOutTime"]; ?>)</td>
                                <td>
                                    <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                                        <input type="hidden" name="location" value="<?php echo $currentCheckIn["location"]; ?>" />
                                        <button type="submit" name="checkOut" class="button">Check-out</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p>You haven't checked-in for any parking location.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!------------------------ Show past parkings that the user has used before ---------------------------->
        <div class="section">
            <h3>Past Check-outs</h3>
            <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                <button type="submit" name="showPastCheckOuts" class="button">Show Past Check-outs</button>
            </form>
            <?php if (count($pastCheckOuts) > 0): ?> 
                <ul>
                    <?php foreach ($pastCheckOuts as $checkOut): ?>
                        <li><?php echo $checkOut["location"]; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No past check-outs found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

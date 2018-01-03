<?php
include "connection.php";
include "utility.php";

session_start();

if(isset($_POST["type"]) && is_session_active()){
    
    $_SESSION["start"] = time();
    $type = sanitizeMYSQL($connection,$_POST["type"]);
    $returned_value = "";
    
    
    switch($type){
        case "cars":            
            $returned_value = display_cars($connection);
            break;
        case "searched":
            $returned_value =  display_searched($connection, sanitizeMYSQL($connection,$_POST["term"]));
            break;
        case "rented":
            $returned_value = display_rented($connection);
            break;
        case "returned":
            $returned_value = display_returned($connection);
            break;
        case "rentCar":
            rentCar($connection, sanitizeMYSQL($connection,$_POST["id"]));
            $returned_value = display_cars($connection);
            break;
        case "returnCar":
            returnCar($connection, sanitizeMYSQL($connection,$_POST["id"]));
            $returned_value = display_rented($connection);
            break;
        case "logout":
            logout();
            $returned_value = "success";
            break;
    }
    echo $returned_value;
}

function is_session_active() {
    return isset($_SESSION) && count($_SESSION) > 0 && time() < $_SESSION['start'] + 60 * 5; //check if it has been 5 minutes
}
function display_cars($connection){
    $query = "SELECT Car.ID AS ID, Size, Color, YearMade, Model, Make, Picture_Type, Picture "
            . "FROM Car JOIN CarSpecs ON Car.CarSpecsID = CarSpecs.ID WHERE status = 1";
    $result = mysqli_query($connection, $query);
    $final_result=array();
    $final_result["cars"]=array();
    if ($result) {
        $row_count = mysqli_num_rows($result);
        for($i=0;$i<$row_count;++$i){
            $row = mysqli_fetch_array($result);
            
            $car = array("ID"=>$row["ID"],"size"=>$row["Size"], "color"=>$row["Color"], "year"=>$row["YearMade"], "model"=>$row["Model"], "make"=>$row["Make"], "picture"=>'data:' . $row["Picture_Type"] . ';base64,' . base64_encode($row["Picture"]));
            $final_result["cars"][]=$car;
        }
    }
    return json_encode($final_result);
}
function display_searched($connection,$term){
    
    $query = "SELECT Car.ID AS ID, Size, Color, YearMade, Model, Make, Picture_Type, Picture FROM Car JOIN CarSpecs ON Car.CarSpecsID = CarSpecs.ID "
            ."WHERE status = 1 AND (  "
            ."Color LIKE '%$term%' OR YearMade LIKE '%$term%' OR Make LIKE '%$term%' OR "
            ."Model LIKE '%$term%' OR Size LIKE '%$term%')";
    
    $result = mysqli_query($connection, $query);
    $final_result=array();
    $final_result["cars"]=array();
    if ($result) {
        $row_count = mysqli_num_rows($result);
        for($i=0;$i<$row_count;++$i){
            $row = mysqli_fetch_array($result);
            
            $car = array("ID"=>$row["ID"],"size"=>$row["Size"], "color"=>$row["Color"], "year"=>$row["YearMade"], "model"=>$row["Model"], "make"=>$row["Make"], "picture"=>'data:'.$row["Picture_Type"].';base64,'. base64_encode($row["Picture"]));
            $final_result["cars"][]=$car;
        }
    }
    return json_encode($final_result);
}

function display_rented($connection){
    $query = "SELECT Picture_Type, Picture, Make, Model, YearMade, Size, Rental.ID as ID, rentDate, Rental.`CustomerID` "
    ."FROM Customer JOIN Rental ON Rental.CustomerID = Customer.ID " 
    ."JOIN Car ON Car.ID = Rental.carID " 
    ."JOIN CarSpecs ON Car.`CarSpecsID` = Carspecs.`ID` "
    ."WHERE CustomerID = '" . $_SESSION["username"] . "' AND Rental.status = 1";
    $result = mysqli_query($connection, $query);
    $final_result=array();
    $final_result["rented_car"]=array();
    if ($result) {
        $row_count = mysqli_num_rows($result);
        for($i=0;$i<$row_count;++$i){
            $row = mysqli_fetch_array($result);
            
            $car = array("size"=>$row["Size"], "rental_ID"=>$row["ID"], "rent_date"=>$row["rentDate"], "year"=>$row["YearMade"], "model"=>$row["Model"], "make"=>$row["Make"], "picture"=>'data:'.$row["Picture_Type"].';base64,'. base64_encode($row["Picture"]));
            $final_result["rented_car"][]=$car;
        }
    }
    return json_encode($final_result);
}
function display_returned($connection){
    $query = "SELECT Picture_Type, Picture, Make, Model, YearMade, Size, Rental.ID as ID, rentDate, Rental.`CustomerID`, Rental.returnDate as returnDate "
    ."FROM Customer JOIN Rental ON Rental.CustomerID = Customer.ID " 
    ."JOIN Car ON Car.ID = Rental.carID " 
    ."JOIN CarSpecs ON Car.`CarSpecsID` = Carspecs.`ID` "
    ."WHERE Rental.returnDate IS NOT NULL AND Customer.ID ='" . $_SESSION["username"] . "'";
    $result = mysqli_query($connection, $query);
    $final_result=array();
    $final_result["returned_car"]=array();
    if ($result) {
        $row_count = mysqli_num_rows($result);
        for($i=0;$i<$row_count;++$i){
            $row = mysqli_fetch_array($result);
            
            $car = array("size"=>$row["Size"], "rental_ID"=>$row["ID"], "return_date"=>$row["returnDate"], "year"=>$row["YearMade"], "model"=>$row["Model"], "make"=>$row["Make"], "picture"=>'data:'.$row["Picture_Type"].';base64,'. base64_encode($row["Picture"]));
            $final_result["returned_car"][]=$car;
        }
    }
    return json_encode($final_result);
}

function rentCar($connection, $id){
    
    $query = "INSERT INTO Rental (rentDate, status, CustomerID, CarID)"
            ." VALUES (DATE(NOW()), 1, '" . $_SESSION["username"] . "' , $id)";
    $result = mysqli_query($connection, $query);
    $query2 = "UPDATE Car SET status = 2 WHERE ID = $id";
    $result2 = mysqli_query($connection, $query2);
}

function returnCar($connection, $id){
    $query = "UPDATE Rental SET status = 2 WHERE ID = $id";
    $result = mysqli_query($connection, $query);
    
    $query2 = "UPDATE Car JOIN Rental ON Rental.carID = Car.ID SET Car.status = 1, returnDate = DATE(NOW()) WHERE Rental.ID = $id";
    $result2 = mysqli_query($connection, $query2);
}



function logout() {
    // Unset all of the session variables.
    $_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
        );
    }

// Finally, destroy the session.
    session_destroy();
}


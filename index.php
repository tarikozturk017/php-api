<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

// Include the database connection
include 'DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();

// Get the requested route from the URL
$route = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Check for "product/id" route
if (preg_match('/^\/php-api\/product\/(\d+)$/', $route, $matches) && $method === 'GET') {
    $productId = $matches[1]; // Extract the product ID from the URL

    // Handle requests to retrieve the product with the specified ID
    $sql = "SELECT * FROM products WHERE rfid_num = :rfid_num";
    $statement = $conn->prepare($sql);
    $statement->bindParam(':rfid_num', $productId);

    if ($statement->execute()) {
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $response = ['status' => 1, 'data' => $data];
            echo json_encode($response);
        } else {
            echo json_encode(['status' => 0, 'message' => 'Product not found.']);
        }
    } else {
        echo json_encode(['status' => 0, 'message' => 'Failed to fetch the product!']);
    }
} elseif (preg_match('/^\/php-api\/product\/(\d+)\/edit$/', $route, $matches) && $method === 'PUT') {
    $productId = $matches[1]; // Extract the product ID from the URL

    // Handle requests to update the product with the specified ID
    $product = json_decode(file_get_contents('php://input'));
    
    $sql = "UPDATE products SET name = :name, category = :category, expiry_date = :expiry_date WHERE rfid_num = :rfid_num";
    $statement = $conn->prepare($sql);
    $statement->bindParam(':name', $product->name);
    $statement->bindParam(':category', $product->category);
    $statement->bindParam(':expiry_date', $product->expDate);
    $statement->bindParam(':rfid_num', $productId);

    if ($statement->execute()) {
        $response = ['status' => 1, 'message' => 'Record updated successfully!'];
    } else {
        $response = ['status' => 0, 'message' => 'Failed to update record!'];
    }
    echo json_encode($response);

    // echo $productId;
    
} elseif (preg_match('/^\/php-api\/product\/(\d+)\/delete$/', $route, $matches) && $method === 'DELETE') {
    $productId = $matches[1]; // Extract the product ID from the URL

    // Handle requests to delete the product with the specified ID
    $sql = "DELETE FROM products WHERE rfid_num = :rfid_num";
    $statement = $conn->prepare($sql);
    $statement->bindParam(':rfid_num', $productId);

    if ($statement->execute()) {
        $response = ['status' => 1, 'message' => 'Record deleted successfully!'];
    } else {
        $response = ['status' => 0, 'message' => 'Failed to delete record!'];
    }

    echo json_encode($response);
} 
else  {

    switch ($route) {
        case "/php-api/product/new":
            // Handle the POST request for creating a new product
            if ($method === "POST") {
                $product = json_decode(file_get_contents('php://input'));
    
                // Convert the string date to a valid date format
                $expDate = date_create($product->expDate)->format('Y-m-d');
        
                $sql = "INSERT INTO products(rfid_num, name, category, expiry_date) VALUES(:rfid_num, :name, :category, :expiry_date)";
                $statement = $conn->prepare($sql);
                $statement->bindParam(':rfid_num', $product->rfidNumber);
                $statement->bindParam(':name', $product->name);
                $statement->bindParam(':category', $product->category);
                $statement->bindParam(':expiry_date', $expDate);
        
                if($statement->execute()) {
                    $response = ['status' => 1, 'message' => 'Record created successfully!'];
                } else {
                    $response = ['status' => 0, 'message' => 'Failed to create record!'];
                }
                echo json_encode($response);
                break;
            } else {
                echo json_encode(['status' => 0, 'message' => 'Invalid request method for this route.']);
            }
            break;
    
        case "/php-api/product/list":
            // Handle the GET request for listing data
            if ($method === "GET") {
                $sql = "SELECT * FROM products";
                $statement = $conn->query($sql);
    
                if ($statement) {
                    $data = $statement->fetchAll(PDO::FETCH_ASSOC);
                    $response = ['status' => 1, 'data' => $data];
                    echo json_encode($response);
                } else {
                    echo json_encode(['status' => 0, 'message' => 'Failed to fetch records!']);
                }
            } else {
                echo json_encode(['status' => 0, 'message' => 'Invalid request method for this route.']);
            }
            break;
    
        default:
            echo json_encode(['status' => 0, 'message' => 'Invalid route.']);
            break;
    }

}
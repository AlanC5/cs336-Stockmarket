<?php

$host = "134.74.126.107";
$username = "username";
$password = "password";
$my_database = "stockmarket";

// Connection
$db = new mysqli($host, $username, $password, $my_database);

// Check Connection
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}


function list_bids_asks($db) {
    // Check if ticker exists
    $ticker = $_GET["ticker"];

    $result = $db->query("SELECT * from INSTRUMENT where TRADING_SYMBOL='$ticker' LIMIT 1");

    if(mysqli_num_rows($result) > 0) {
        #echo "Stock Ticker Exists";
        $quotes = $db->query("SELECT * FROM STOCK_QUOTE where TRADING_SYMBOL='$ticker' LIMIT 5");

        while ($row = $quotes->fetch_assoc()) {
            $rows[] = $row;
            $quote_time = $row["QUOTE_TIME"];
            $ask_price = $row["ASK_PRICE"];
            $ask_size = $row["ASK_SIZE"];
            $bid_price = $row["BID_PRICE"];
            $bid_size = $row["BID_SIZE"];
            //
            echo "<tr><td>$quote_time</td><td>$ask_price</td><td>$ask_size</td><td>$bid_price</td><td>$bid_size</td></tr>";
        }
    }
    else {
        echo "Does not exist";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    list_bids_asks($db);
}

?>

<?php

$host = "134.74.126.107";
$username = "username";
$password = "password";
$my_database = "F16336team3";

// Connection
$db = new mysqli($host, $username, $password, $my_database);

// Check Connection
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

date_default_timezone_set('US/Eastern');

function list_bids_asks($db) {
    // Check if ticker exists
    $ticker = $_GET["ticker"];

    $result = $db->query("SELECT * from INSTRUMENT where TRADING_SYMBOL='$ticker' LIMIT 1");
    // If ticker does exist then proceed to query DB to return the latest quotes
    if(mysqli_num_rows($result) > 0) {
        $quotes = $db->query("SELECT * FROM STOCK_QUOTE where TRADING_SYMBOL='$ticker' ORDER BY QUOTE_DATE DESC LIMIT 5");

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


// submit a bid or an ask
function submit_bid_ask_form($db) {
    // Check if ticker exists
    $ticker = $_POST["ticker"];
    $type = $_POST["type"];

    $result = $db->query("SELECT * from INSTRUMENT where TRADING_SYMBOL='$ticker' LIMIT 1");
    // If ticker does exist then proceed to match bid or ask
    if (mysqli_num_rows($result) > 0) {
        // Grab all the form data
        $row = $result->fetch_assoc();
        $instrumentID = $row["INSTRUMENT_ID"];
        $ticker = $row["TRADING_SYMBOL"];
        $price = (float)$_POST["price"];
        $size = (int)$_POST["size"];
        $time = $_POST["time"];
        $random = rand(0, 12000);

        // Check if it's a bid or an ask
        // BID TYPE
        if ($type == "bid") {
            // Insert the bid to STOCK_QUOTE
            $sql = "INSERT INTO STOCK_QUOTE VALUES ('$instrumentID', CURDATE(), $random, '$ticker', NOW(), 0, 0, $price, $size)";

            // Assume inserting a bid succeeds
            if ($db->query($sql) == TRUE) {

                // Look for asks that satisfies the bid
                // Price should always match, but for sizes there are two different situations

                // Ask size is greater or equal than the bid's
                $result = $db->query("SELECT * FROM STOCK_QUOTE WHERE TRADING_SYMBOL='$ticker' AND ASK_PRICE=$price AND ASK_SIZE>=$size LIMIT 1");

                // Ask size is smaller than the bid's
                $result_partial = $db->query("SELECT * FROM STOCK_QUOTE WHERE TRADING_SYMBOL='$ticker' AND ASK_PRICE=$price AND ASK_SIZE<$size LIMIT 1");

                // Assume that we find an ask which has equal or greater size
                if (mysqli_num_rows($result) > 0) {
                    $row = $result->fetch_assoc();
                    $sql = "INSERT INTO STOCK_TRADE VALUES ('$instrumentID', CURDATE(), $random, '$ticker', NOW(), $price, $size)";
                    if ($db->query($sql) == TRUE) {
                        echo "A transaction was made successfully, with price: $price, size: $size";
                    } else {
                        echo "A bid was added into STOCK_QUOTE table and a matched ask was found, but transaction failed.";
                    }

                // Assume that we find an ask with desired price but smaller size
                } else if (mysqli_num_rows($result_partial) > 0){
                    $row = $result_partial->fetch_assoc();
                    $partial_size = $row["ASK_SIZE"];
                    $sql = "INSERT INTO STOCK_TRADE VALUES ('$instrumentID', CURDATE(), $random, '$ticker', NOW(), $price, $partial_size)";
                    if ($db->query($sql) == TRUE) {
                        $remainder = $size - $partial_size;
                        echo "A bid was added into STOCK_QUOTE table. Only partial transactions were made, please put another bid with remaining size: $remainder.";
                    } else {
                        echo "A bid was added into STOCK_QUOTE table and a partial ask was found, but transaction failed.";
                    }

                // Does not find a match
                } else {
                    echo "An bid was added into STOCK_QUOTE table. No current asks match your bid. No transaction was made. If a matched ask appeared within the time frame you set, the system will automatically match your bid with that ask.";
                }

            // Fail to insert the bid
            } else {
                echo "Failed to insert the bid, try again";
            }


        // ASK Type
        } else {
            // Insert the ask to STOCK_QUOTE
            $sql = "INSERT INTO STOCK_QUOTE VALUES ('$instrumentID', CURDATE(), $random, '$ticker', NOW(), $price, $size, 0, 0)";

            // Assume inserting a ask succeeds
            if ($db->query($sql) == TRUE) {

                // Look for asks that satisfies the bid
                // Price should always match, but for sizes there are two different situations

                // Bid size is greater or equal than the ask's
                $result = $db->query("SELECT * FROM STOCK_QUOTE WHERE TRADING_SYMBOL='$ticker' AND BID_PRICE=$price AND BID_SIZE>=$size LIMIT 1");

                // Bid size is smaller than the ask's
                $result_partial = $db->query("SELECT * FROM STOCK_QUOTE WHERE TRADING_SYMBOL='$ticker' AND BID_PRICE=$price AND BID_SIZE<$size LIMIT 1");

                // Assume that we find an ask which has equal or greater size
                if (mysqli_num_rows($result) > 0) {
                    $row = $result->fetch_assoc();
                    $sql = "INSERT INTO STOCK_TRADE VALUES ('$instrumentID', CURDATE(), $random, '$ticker', NOW(), $price, $size)";
                    if ($db->query($sql) == TRUE) {
                        echo "A transaction was made successfully, with price: $price, size: $size";
                    } else {
                        echo "An ask was added into STOCK_QUOTE table. And a matched bid was found, but transaction failed.";
                    }

                // Assume that we find an ask with desired price but smaller size
                } else if (mysqli_num_rows($result_partial) > 0){
                    $row = $result_partial->fetch_assoc();
                    $size2 = $row["BID_SIZE"];
                    $sql = "INSERT INTO STOCK_TRADE VALUES ('$instrumentID', CURDATE(), $random, '$ticker', NOW(), $price, $size2)";
                    if ($db->query($sql) == TRUE) {
                        $newsize = $size - $size2;
                        echo "An bid with smaller size is found, partial transaction was made successfully, with price: $price, size: $size2. Please put another ask with remaining size: $newsize";
                    } else {
                        echo "An ask was added into STOCK_QUOTE table. And a matched bid was found, but transaction failed.";
                    }

                // Does not find a match
                } else {
                    echo "An ask was added into STOCK_QUOTE table. No current bids matched your ask. No transaction were made. But if there are any matched bid posted within the time frame you set, system will automatically match your ask with that bid.";
                }

            // Fail to insert the bid
            } else {
                echo "Failed to insert the bid";
            }
        }
    } else {
        echo "The Ticker you are looking for does not exist";
    }
}


// Taking GET or POST request from ajax call
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    list_bids_asks($db);
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    submit_bid_ask_form($db);
}


?>

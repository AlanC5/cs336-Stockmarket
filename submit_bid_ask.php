<?php

$host = "134.74.126.107";
$username = "F16336dliang";
$password = "23083903";
$my_database = "F16336dliang";

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


// submit a bid or an ask
function submit_bid_ask_form($db) {
    // Check if ticker exists
    $ticker = $_POST["ticker"];
    $type = $_POST["type"];
    $result = $db->query("SELECT * from INSTRUMENT where TRADING_SYMBOL='$ticker' LIMIT 1");
    if (mysqli_num_rows($result) > 0) {
        // grap all data from form
        $row = $result->fetch_assoc();
        $instrumentID = $row["INSTRUMENT_ID"];
        $ticker = $row["TRADING_SYMBOL"];
        $price = (float)$_POST["price"];
        $size = (int)$_POST["size"];
        $time = $_POST["time"];
        $random = rand(0, 12000);

        // check if it's a bid or an ask
        /////////////////////////// when type is a bid ///////////////////////
        if ($type == "bid") {
            // Insert the bid to STOCK_QUOTE
            $sql = "INSERT INTO STOCK_QUOTE VALUES ('$instrumentID', CURDATE(), $random, '$ticker', NOW(), 0, 0, $price, $size)";

            // Assume inserting a bid succeeds
            if ($db->query($sql) == TRUE) {

                // Look for asks that satisfies the bid
                // Price should always match, but for sizes there are two different situations
                $result = $db->query("SELECT * FROM STOCK_QUOTE WHERE ASK_PRICE=$price AND ASK_SIZE>=$size AND TIMESTAMPDIFF(MINUTE, QUOTE_TIME, NOW())<30 LIMIT 1"); // when an ask size is greater or equal than the bid's
                $result_partial = $db->query("SELECT * FROM STOCK_QUOTE WHERE ASK_PRICE=$price AND ASK_SIZE<$size AND TIMESTAMPDIFF(MINUTE, QUOTE_TIME, NOW())<30 LIMIT 1"); // when an ask size is smaller than the bid's

                // Assume that we find an ask which has equal or greater size
                if (mysqli_num_rows($result) > 0) {
                    $row = $result->fetch_assoc();
                    $sql = "INSERT INTO STOCK_TRADE VALUES ('$instrumentID', CURDATE(), $random, '$ticker', NOW(), $price, $size)";
                    if ($db->query($sql) == TRUE) {
                        echo "A transaction was made successfully, with price: $price, size: $size";
                    } else {
                        echo "A bid was added into STOCK_QUOTE table.";
                        echo "And a matched ask was found, but transaction failed.";
                    }

                // Assume that we find an ask with desired price but smaller size
                } else if (mysqli_num_rows($result_partial) > 0){
                    $row = $result->fetch_assoc();
                    $partial_size = $row["ASK_SIZE"];
                    $sql = "INSERT INTO STOCK_TRADE VALUES ('$instrumentID', CURDATE(), $random, '$ticker', NOW(), $price, partial_size)";
                    if ($db->query($sql) == TRUE) {
                        echo "A bid was added into STOCK_QUOTE table.";
                        $remainder = $size - $partial_size;
                        echo "Only partial transactions was made, please put another bid with remainded size: $remainder.";
                    } else {
                        echo "A bid was added into STOCK_QUOTE table.";
                        echo "Partial ask was found, but transaction failed.";
                    }

                // Does not find a match
                } else {
                    echo "An bid was added into STOCK_QUOTE table. ";
                    echo "No current asks matche your bid. No transaction was made. But if there is any matched ask is post with time frame you set, system will automatically match your bid with that ask.";
                }

            // Fail to insert the bid
            } else {
                echo "Failed to insert the bid, try again";
            }

        /////////////////////////// when type is an ask ///////////////////////
        } else {
            // Insert the ask to STOCK_QUOTE
            $sql = "INSERT INTO STOCK_QUOTE VALUES ('$instrumentID', CURDATE(), $random, '$ticker', NOW(), $price, $size, 0, 0)";

            // Assume inserting a ask succeeds
            if ($db->query($sql) == TRUE) {

                // Look for asks that satisfies the bid
                // Price should always match, but for sizes there are two different situations
                $result = $db->query("SELECT * FROM STOCK_QUOTE WHERE BID_PRICE=$price AND BID_SIZE<$size AND TIMESTAMPDIFF(MINUTE, QUOTE_TIME, NOW())<30 LIMIT 1"); // when an bid size is greater or equal than the ask's
                $result_partial = $db->query("SELECT * FROM STOCK_QUOTE WHERE BID_PRICE=$price AND BID_SIZE>=$size AND TIMESTAMPDIFF(MINUTE, QUOTE_TIME, NOW())<30 LIMIT 1"); // when an bid size is smaller than the ask's

                // Assume that we find an ask which has equal or greater size
                if (mysqli_num_rows($result) > 0) {
                    $row = $result->fetch_assoc();
                    $size2 = $row["BID_SIZE"];
                    $sql = "INSERT INTO STOCK_TRADE VALUES ('$instrumentID', CURDATE(), $random, '$ticker', NOW(), $price, $size2)";
                    if ($db->query($sql) == TRUE) {
                        echo "A bid with larger size is found, partial transaction was make successfully, with price: $price, size: $size2";
                        $newsize = $size - $size2;
                        echo "Please put a another ask with remainded size: $newsize";
                    } else {
                        echo "An ask was added into STOCK_QUOTE table.";
                        echo "And a matched bid was found, but transaction failed.";
                    }

                // Assume that we find an ask with desired price but smaller size
                } else if (mysqli_num_rows($result_partial) > 0){
                    $row = $result_partial->fetch_assoc();
                    $sql = "INSERT INTO STOCK_TRADE VALUES ('$instrumentID', CURDATE(), $random, '$ticker', NOW(), $price, $size)";
                    if ($db->query($sql) == TRUE) {
                        echo "An ask was added into STOCK_QUOTE table.";
                        echo "A transaction was made successfully, with price: $price, size: $size";
                    } else {
                        echo "An ask was added into STOCK_QUOTE table.";
                        echo "And a matched bid was found, but transaction failed.";
                    }

                // Does not find a match
                } else {
                    echo "An ask was added into STOCK_QUOTE table.";
                    echo "No current bids matche your ask. No transaction was made. But if there is any matched bid is post with time frame you set, system will automatically match your ask with that bid.";
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

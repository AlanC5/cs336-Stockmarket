<html>
<head>
    <title>E-Trade Form</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <style>
        #feed_table tr th {
            width: 200px;
            text-align: left;
        }

    </style>
</head>
<body>
<div id="form_container">
<form action="" method="POST">
    Ticker: <input type="text" id="ticker" name="ticker">
    <button id="Lookup" type="button" onclick="GetTicker();">Lookup</button><br><br>

    <input type="radio" name="type" id="bid" value="bid"> Bid<br>
    <input type="radio" name="type" id="ask" value="ask" checked> Ask<br><br>

    Price: <input type="number" name="price" id="price"><br><br>
    Size: <input type="number" name="size" id="size"><br><br>

    Time Frame (Minutes): <input type="number" name="time" id="time" min="0" max="30"><br />

    <input id="submit" name="submit" type="submit" value="Submit">
</form>
</div>
<div id="feed_container" style="visibility:hidden; height:450px; overflow:scroll;">
    <table id="feed_table">
        <tr>
            <th>
                Quote Time
            </th>
            <th>
                Ask Price
            </th>
            <th>
                Ask Size
            </th>
            <th>
                Buy Price
            </th>
            <th>
                Buy Size
            </th>
        </tr>
    </table>
</div>

<script type="text/javascript">

function GetTicker()
{
    var ticker = document.getElementById('ticker').value;
    // Check if value is empty
    if (ticker.length != 0) {
        var params = 'ticker=' + ticker;

        // Async request
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.onreadystatechange = function() {
            // Callback
            if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
                // Format and append to HTML
                if (this.responseText == "Does not exist") {
                    document.getElementById("feed_container").style.visibility = "hidden";
                    alert("Stock Ticker does not exists, please choose a different Stock Ticker");
                }
                else {
                    document.getElementById("feed_container").style.visibility = "visible";
                    document.getElementById("feed_table").innerHTML = "<tr><th>Quote Time</th><th>Ask Price</th><th>Ask Size</th><th>Buy Price</th><th>Buy Size</th></tr>" + this.responseText;
                }
        }
        xmlHttp.open("GET", "submit_bid_ask.php?" + params, true);
        xmlHttp.send();
    }
    else {
        alert("Cannot search for empty Ticker.");
    }
}


/////////////////////////// submit_bid_ask_form //////////////////////////////

// action on click
$('#submit').on("click", function(e) {
    // prevent it from opening new page
    e.preventDefault();
    console.log("submitting");

    // getting value for all form fields
    var ticker = document.getElementById('ticker').value;
    var price = document.getElementById('price').value;
    var size = document.getElementById('size').value;
    var time = document.getElementById('time').value;
    if (document.getElementById('bid').checked) {
        var type = document.getElementById('bid').value;
    } else {
        var type = document.getElementById('ask').value;
    }

    // Form validation
    if (ticker.length != 0 && price.length != 0 && size.length != 0 && time.length != 0 && type.length != 0) {
        if(type == "bid") {
            console.log("bid");
        } else {
            console.log("ask");
        }
        $.ajax({
            method: "POST",
            url: "submit_bid_ask.php",
            data: {
                ticker: ticker,
                type: type,
                price: price,
                size: size,
                time: time
            }
        })
        .done(function(msg) {
            alert("Result: " + msg);
        });
    } else {
        alert("One of the fields in the form is empty.");
    }
});

////////////////////////////////////////////////////////////////////////
</script>

</body>
</html>

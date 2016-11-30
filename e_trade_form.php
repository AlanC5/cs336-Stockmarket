<html>
<head>
    <title>E-Trade Form</title>
    <style>
        #feed_table tr th {
            width: 200px;
            text-align: left;
        }

    </style>
</head>
<body>
<div id="form_container">
<form action="submit_bid_ask.php" method="POST">
    Ticker: <input type="text" id="ticker" name="ticker">
    <button id="Lookup" type="button" onclick="GetTicker();">Lookup</button><br><br>

    <input type="radio" name="type" value="big" checked> Bid<br>
    <input type="radio" name="type" value="ask"> Ask<br><br>

    Amount: <input type="text" name="amount"><br><br>
    Time Frame (Minutes): <input type="number" name="time" min="0" max="30" value="0"><br />
<input type="submit" value="Submit">
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

</script>

</body>
</html>

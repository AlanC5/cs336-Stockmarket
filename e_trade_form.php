<html>
<body>

<form action="submit_bid_ask.php" method="POST">
    Ticker: <input type="text" id="ticker" name="ticker">
    <button id="Lookup" type="button" onclick="httpGetAsync();">Lookup</button><br><br>

    <input type="radio" name="type" value="big" checked> Bid<br>
    <input type="radio" name="type" value="ask"> Ask<br><br>

    Amount: <input type="text" name="amount"><br><br>
    Time Frame (Minutes): <input type="number" name="time" min="0" max="30" value="0"><br />
<input type="submit" value="Submit">
</form>

<script>


function httpGetAsync()
{
    var ticker = document.getElementById('ticker').value;
    // Check if value is empty
    if (ticker.length != 0) {
        var params = 'ticker=' + ticker;
        
        // Async request
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.onreadystatechange = function() {
            if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
                alert(this.responseText);
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

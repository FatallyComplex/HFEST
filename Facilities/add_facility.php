<?php require_once '../database.php';

date_default_timezone_set('America/New_York');

$stmt = $conn->prepare("SELECT DISTINCT Type FROM FACILITIES");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
<title>Add Facility</title>
<link href="https://fonts.googleapis.com/css?family=Montserrat|Open+Sans&display=swap" rel="stylesheet">
<style>
    body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        h1 {
            color: #333333;
            text-align: center;
            margin-top: 50px;
        }
        caption{
            font-size: 24px;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 50px;
        }
        first {
            display: inline-block;
            flex-direction: column;
            align-items: center;
            margin-top: 50px;
        }
        label {
            font-size: 18px;
            color: #666666;
            margin-bottom: 10px;
        }
        select {
            font-size: 16px;
            padding: 10px;
            border: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        button {
            font-size: 18px;
            color: white;
            background-color: #3f3f3f;
            padding: 5px 10px;
            margin-top: 10px;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }
        button:hover {
            background-color: #1c1c1c;
        }
        h2 {
            color: #444444;
            text-align: center;
            margin-top: 50px;
        }
        table {
            border-collapse: collapse;
            margin: 0 auto;
            max-width: 2400px;
            margin-top: 50px;
        }
        th, td {
            font-size: 24px;
            color: #666666;
            padding: 20px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            text-align: left;
        }
        
        th {
            background-color: #912338;
            font-weight: bold;
            color: white;
        }
        nav {
            display: flex;
            justify-content: flex-end;
            background-color: #912338;
            padding: 20px 10px;
            color: white;
        }
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        nav li {
            display: inline;
            margin-left: 20px;
        }
        nav a {
            color: white;
            text-decoration: none;
        }
        nav a:hover {
            text-decoration: underline;
        }
</style>
<a href="/HFESTS/">
        <img src="https://img.freepik.com/premium-vector/modern-letter-h-f-elegant-abstract-logo_649646-350.jpg?w=996" alt="HFESTS logo" style="position: absolute; top: 10px; left: 10px; width: 150px; height: auto;">
    </a>
</head>
<body>
<nav>
    <ul>
        <li><a href="../">Home</a></li>
        <li><a href="../Employees">Search Employees</a></li>
        <li><a href="../Facilities">Search Facilities</a></li>
        <li><a href="../Facilities">Back</a></li>
    </ul>
</nav>
    <h1>Add Facility</h1>
    <form method="POST" action="add_facility_action.php">


        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br>

        <div style="display: flex;">
                <div style="width: 50%; margin:25px; text-align: center;">
                    <label for="Address">Address</label><br>
                    <input type="text" name="Address" id="Address" style="width: 100%;" required><br>
                </div>
                <div style="width: 50%; margin:25px; text-align: center;">
                    <label for="PostalCode">Postal Code</label><br>
                    <input type="text" name="PostalCode" id="PostalCode" style="width: 100%;" oninput="getCityProvince()" pattern="[A-Z]\d[A-Z] \d[A-Z]\d|\d{5}" required><br>
                </div>
            </div>
            <div style="display: flex;">
                <div style="width: 50%; margin:25px; text-align: center;">
                    <label for="City">City</label><br>
                    <input type="text" name="City" id="City" style="width: 100%;" oninput="getFacilities()" readonly require><br>
                </div>
                <div style="width: 50%; margin: 25px; text-align: center;">
                    <label for="Province">Province</label><br>
                    <input type="text" name="Province" id="Province" style="width: 100%;" readonly required><br>
                </div>
            </div>

        <label for="PhoneNumber">Phone Number: <br>xxx-xxx-xxxx</label>
        <input type="text" id="PhoneNumber" name="PhoneNumber" pattern="\d{3}-\d{3}-\d{4}" required><br>

        <label for="WebAddress">Web Address:</label>
        <input type="text" id="WebAddress" name="WebAddress" pattern="www\.[a-zA-Z0-9]+\.[a-zA-Z]{2,3}" required><br>

        <label for="capacity">Capacity:</label>
        <input type="number" id="capacity" name="capacity" required min="1"><br>

        <label for="type">Type:</label>
        <select id="type" name="type" required>
        <option value="">Select Facility Type</option>
        <?php foreach($result as $type): ?>
            <option value="<?php echo $type['Type']; ?>"><?php echo $type['Type'];?></option>
        <?php endforeach?>
        </select><br>



        <input type="submit" value="Add Facility">
    </form>
    <script>

function getCityProvince() {
    var postalCode = document.getElementById("PostalCode").value;
    var firstLetter = postalCode.charAt(0);
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(this.responseText);
            document.getElementById("City").value = response.City;
            console.log(document.getElementById("City").value)
            document.getElementById("Province").value = response.Province;
            getFacilities(response.City);
        }
    };
    xhr.open("GET", "getCityProvince.php?firstLetter=" + firstLetter, true);
    xhr.send();
}
function getFacilities() {
  var city = document.getElementById("City").value;
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      var facilities = JSON.parse(this.responseText);
      var select = document.createElement("select");
      select.name = "FacilityId";
      select.id = "FacilityId";
      select.required = false; // make field optional by default
      var option = document.createElement("option");
      option.value = "";
      option.text = "";
      select.appendChild(option);
      for (var i = 0; i < facilities.length; i++) {
        var option = document.createElement("option");
        option.value = facilities[i].FacilityId;
        option.text = facilities[i].Name;
        select.appendChild(option);
      }
      var oldSelect = document.getElementById("FacilityId");
      oldSelect.parentNode.replaceChild(select, oldSelect);

      // toggle required attribute of start date field based on whether option is selected
      select.addEventListener('change', function() {
        var startDate = document.getElementById('StartDate');
        startDate.required = (this.value !== '');
      });
    }
  };
  xhr.open("GET", "getFacilities.php?city=" + city, true);
  xhr.send();
}

</script>
</body>
</html>



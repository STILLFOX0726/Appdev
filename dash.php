
<?php
$apiKey = "85c245a3efe7e1693efd88bb6251e51f";
$city = "Manila";
$apiUrl = "https://api.openweathermap.org/data/2.5/forecast?q=$city&appid=$apiKey&units=metric";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

$weatherDisplay = "";
if ($data && $data["cod"] == "200") {
    $currentTemp = round($data["list"][0]["main"]["temp"]);
    $conditionMain = strtolower($data["list"][0]["weather"][0]["main"]);

    switch ($conditionMain) {
        case 'clear':
            $bgColor = '#fdd835'; $textColor = '#000'; break;
        case 'clouds':
            $bgColor = '#90a4ae'; $textColor = '#000'; break;
        case 'rain':
        case 'drizzle':
            $bgColor = '#4fc3f7'; $textColor = '#000'; break;
        case 'thunderstorm':
            $bgColor = '#616161'; $textColor = '#fff'; break;
        case 'snow':
            $bgColor = '#e0f7fa'; $textColor = '#000'; break;
        case 'mist':
        case 'fog':
            $bgColor = '#cfd8dc'; $textColor = '#000'; break;
        default:
            $bgColor = '#007bff'; $textColor = '#fff'; break;
    }

    $weatherDisplay .= "<div class='weather-widget' style='background-color: $bgColor; color: $textColor;'>";
    $weatherDisplay .= "<div class='current-temp'>{$currentTemp} °C</div><div class='temp-graph'>";
    for ($i = 0; $i < 6; $i++) {
        $tempPoint = round($data["list"][$i]["main"]["temp"]);
        $weatherDisplay .= "<div class='dot' title='{$tempPoint}°C'></div>";
    }
    $weatherDisplay .= "</div><div class='forecast'>";
    $forecastDays = [];
    foreach ($data["list"] as $forecast) {
        $date = date("D", strtotime($forecast["dt_txt"]));
        if (!isset($forecastDays[$date])) {
            $forecastDays[$date] = [
                'max' => $forecast["main"]["temp_max"],
                'min' => $forecast["main"]["temp_min"],
                'icon' => $forecast["weather"][0]["icon"]
            ];
        }
        if (count($forecastDays) >= 3) break;
    }
    foreach ($forecastDays as $day => $info) {
        $weatherDisplay .= "<div class='day-forecast'>
            <img src='https://openweathermap.org/img/wn/{$info["icon"]}@2x.png' width='40'>
            <div class='day'>{$day}</div>
            <div class='temps'>".round($info["max"])."°C / ".round($info["min"])."°C</div>
        </div>";
    }
    $weatherDisplay .= "</div></div>";
} else {
    $weatherDisplay = "<p style='color:white;'>Unable to fetch weather data.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard with Forecast</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            font-family: Helvetica, sans-serif;
            background-color: #d2e7f7
        }
        
        .nav-links {
            display: flex;
            gap: 15px;
            padding: 20px;
            background-color: #4c3c4c;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .nav-links a:hover {
            background-color: #0056b3;
        }

        .dashboard-content {
            padding: 40px;
            display: flex;
            gap: 30px;
            justify-content: flex-start;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .weather-widget {
            padding: 20px;
            border-radius: 12px;
            width: 300px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .current-temp {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .temp-graph {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 40px;
        }

        .dot {
            width: 12px;
            height: 12px;
            background-color: white;
            border-radius: 50%;
            cursor: help;
        }

        .forecast {
            display: flex;
            justify-content: space-around;
            gap: 10px;
        }

        .day-forecast {
            text-align: center;
        }

        .day {
            font-weight: bold;
        }

        .temps {
            font-size: 14px;
        }

        .clock-date-box {
            background: #020024;
            background: linear-gradient(90deg,rgba(2, 0, 36, 1) 0%, rgba(9, 9, 121, 1) 35%, rgba(0, 212, 255, 1) 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            width: 300px;
            position: right;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .move-right {
            text-align: right; /* Align the text to the right */
            padding-right: 13in; /* Optional, adds some space from the edge */
        }
     .box-container{
            width: 550px; 
            height: 260px;
            object-fit: cover; 
            border-radius: 10px;
            position: relative; 
            top: 50;
            padding: 30px;
           
        }
    .sub-nav {
            background-color:#fffcfd;
            display: flex;
            justify-content: flex-end;
            padding: 14px 0;
            border-bottom: 1px solid #ddd;
            flex-wrap: wrap;
            gap: 30px;

        }

    .sub-nav a {
            color: #0047ab; 
            text-decoration: none;
            font-size: 17px;
            font-weight: 500;
            transition: color 0.3s ease;
}

    .sub-nav a:hover {
    color: #002d72; 
    text-decoration: underline;     

    }
    

    </style>
</head>
<body>

<div class="navbar">
    <div class="nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <div class="move-right"></div>
            <a href="register.php">Sign Up</a>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </div>
    
</div>
<header>

<div class="sub-nav">
    <div></div>
    <a href="#">Lauches</a>
    <a href="#">Product</a>
    <a href="#">New</a>
    <a href="#">Forums</a>
    <a href="#">Advertise</a>
    <a href="#">Assets for Sale</a>
    <a href="#">Deals</a>
    <a href="#">Digital</a>
  </div>
        </header>

    </div>

</div>

<div class="dashboard-content">
    <!-- Clock & Date Box -->
    <div class="clock-date-box">
        <h2>Today is:</h2>
        <p id="date"></p>
        <h2>Time Now:</h2>
        <p id="time"></p>
    </div>
    <!-- Weather Widget -->
    <?php echo $weatherDisplay; ?>

</div>

<!-- JavaScript Clock -->
<script>
    function updateClockBox() {
        const now = new Date();

        const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        const months = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        const day = days[now.getDay()];
        const date = now.getDate();
        const month = months[now.getMonth()];
        const year = now.getFullYear();

        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();
        const ampm = hours >= 12 ? "PM" : "AM";

        hours = hours % 12;
        hours = hours ? hours : 12;
        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        const formattedDate = `${day}, ${month} ${date}, ${year}`;
        const formattedTime = `${hours}:${minutes}:${seconds} ${ampm}`;

        document.getElementById("date").textContent = formattedDate;
        document.getElementById("time").textContent = formattedTime;
    }

    updateClockBox();
    setInterval(updateClockBox, 1000);
</script>
<div class="dashboard-content">
    <!-- box -->
    <div class="BOX">
    <img src="ezycourse.png" alt="ezycourse.png" style="width: 750px; height: 350px; object-fit: cover; border-radius: 8px;">
    </div>
        <div class = "nav-container">

    <div class="dashboard-content">

    <!-- Box -->
    <div class="box-container">
    <img src="face wash.avif" alt="face wash.avif" style="width: 500px; height: 250px; object-fit: cover; border-radius: 8px; position margin-right: 800px;position: relative; left: 750px;relative; top: -770px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
    <img src="beauty-in-the-beat.jpg" alt="beauty-in-the-beat.jpg" style="width: 500px; height: 332px; object-fit: cover; border-radius: 8px; position margin-right: 700px;position: relative; left: 715px;relative; top: -710px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); position: relative;  height: 332px; width: 550px; ">


        <h2></h2>
        <p></p>
        <h2></h2>
        <p></p>
        <style>
    
        </style>
    </div>



</div>


</body>
</html>

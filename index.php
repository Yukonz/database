<?php

$mysql_host = 'localhost';
$mysql_user = 'root';
$mysql_password = '123456';
$mysql_database = 'mydb';

$connect = new mysqli($mysql_host, $mysql_user, $mysql_password, $mysql_database);

if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}
echo "Connected successfully<br>";

$sql = "CREATE TABLE Users (
        id              INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        login           VARCHAR(30) NOT NULL,
        password        VARCHAR(30) NOT NULL,
        date_register   VARCHAR(30) NOT NULL,
        date_last_visit VARCHAR(30) NOT NULL,
        ip              INT(8) NOT NULL,
        active          BOOLEAN DEFAULT NULL
)";
$connect->query($sql);

$sql = "TRUNCATE TABLE Groups";
$connect->query($sql);

$sql = "CREATE TABLE Groups (
        id              INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        group_name      VARCHAR(30) NOT NULL
)";
$connect->query($sql);

$sql = "INSERT INTO Groups (group_name) VALUES ('temporary'), ('regular'), ('editors'), ('admin')";
$connect->query($sql);

$sql = "CREATE TABLE Partners (
        id              INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        partner_name    VARCHAR(30) NOT NULL
)";
$connect->query($sql);

$sql = "CREATE TABLE Items (
        id              INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        item_name       VARCHAR(30) NOT NULL,
        item_price      FLOAT(10) NOT NULL
)";
$connect->query($sql);

$sql = "CREATE TABLE Orders (
        id              INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        user_id         INT(6) UNSIGNED NOT NULL, 
        item_id         INT(6) UNSIGNED NOT NULL,
        partner_id      INT(6) UNSIGNED,
        FOREIGN KEY (user_id) REFERENCES Users(id),
        FOREIGN KEY (item_id) REFERENCES Items(id),
        FOREIGN KEY (partner_id) REFERENCES Partners(id)
)";
$connect->query($sql);

$sql = "CREATE TABLE Users_Groups (
        id              INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        user_id         INT(6) UNSIGNED NOT NULL, 
        group_id        INT(6) UNSIGNED NOT NULL,
        FOREIGN KEY (user_id) REFERENCES Users(id),
        FOREIGN KEY (group_id) REFERENCES Groups(id)
)";
$connect->query($sql);

echo "<br>";
echo mysqli_error($connect);


function generate_login($leight)
{
    $login = '';
    $symbols = "qwertyuiopasdfghjklzxcvbnm";
    for($i=0; $i<$leight; $i++){
        $login .= substr($symbols, mt_rand(0, strlen($symbols)-1), 1);
    }
    return $login;
}

function generate_password($leight)
{
    $password = '';
    $symbols = "qwertyuiopasdfghjklzxcvbnm1234567890_";
    for($i=0; $i<$leight; $i++){
        $password .= substr($symbols, mt_rand(0, strlen($symbols)-1), 1);
    }
    $password = md5($password);
    return $password;
}

function generate_ip($first_number)
{
    $ip = $first_number . "." . rand(0, 255) . "." . rand(0, 255) . "." . rand(0, 255);
    $ip = ip2long($ip);
    return $ip;
}

function generate_register_date()
{
    $register_date = rand(1, 30) . "." . rand(1, 12) . "." . rand(2013, 2014);
    return $register_date;
}

function generate_visit_date($register_date)
{
    $register_date = explode(".", $register_date);

    if ($register_date[2] == 2014){
        $visit_date_year = 2014;
        $visit_date_month = rand($register_date[1], 12);
        if ($visit_date_month == $visit_date_month) {
            $visit_date_day = rand($register_date[0], 30);
        } else $visit_date_day = rand(1, 30);
    } else {
        $visit_date_year = rand(2013, 2014);
        if ($visit_date_year == 2013) {
            $visit_date_month = rand($register_date[1], 12);
            if ($visit_date_month == $register_date[1]){
                $visit_date_day = rand($register_date[0], 30);
            } else $visit_date_day = rand(1, 30);
        } else {
            $visit_date_month = rand(1, 12);
            $visit_date_day = rand(1, 30);
        }
    }
    $visit_date = $visit_date_day . "." . $visit_date_month . "." . $visit_date_year;
    return $visit_date;
}


for ($i=0; $i<50; $i++){
    $login = generate_login(10);
    $password = generate_password(15);
    $ip = generate_ip(255);
    $reg_date = generate_register_date();
    $visit_date = generate_visit_date($reg_date);

    echo "<hr>Login: " . $login . "<br>";
    echo "Pass: " . $password . "<br>";
    echo "Ip: " . long2ip($ip) . "<br>";
    echo "Reg date: " . $reg_date . "<br>";
    echo "Visit date: " . $visit_date . "<br>";
}

<?php

require_once 'autoload.php';

$mysql_host = 'localhost';
$mysql_user = 'root';
$mysql_password = '123456';
$mysql_database = 'mydb';

$users_count = 1000;
$items_count = 2;
$partners_count = 10;
$orders_count = 1000;

$connect = new mysqli($mysql_host, $mysql_user, $mysql_password);

if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}
echo "Connected successfully<br>";

$sql = "DROP DATABASE IF EXISTS {$mysql_database}";
$connect->query($sql);
$sql = "CREATE DATABASE {$mysql_database}";
$connect->query($sql);

$connect = new mysqli($mysql_host, $mysql_user, $mysql_password, $mysql_database);

$sql = "CREATE TABLE Users (
        id              INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        login           VARCHAR(128) NOT NULL,
        password        VARCHAR(128) NOT NULL,
        date_register   VARCHAR(30) NOT NULL,
        date_last_visit VARCHAR(30) NOT NULL,
        ip              BIGINT(128) NOT NULL,
        active          BOOLEAN DEFAULT NULL
)";
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
        item_price      INT(10) NOT NULL
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


function generate_login($faker)
{
    $login = $faker->word . $faker->word;
    return $login;
}

function generate_password($faker)
{
    $password = $faker->word . "_" . rand(0, 9999);
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
    $register_day = rand(1, 30);
    if ($register_day < 10) $register_day = '0' . $register_day;
    $register_month = rand(1, 12);
    if ($register_month < 10) $register_month = '0' . $register_month;
    $register_year = rand(2013, 2014);
    $register_date = $register_day . "-" . $register_month . "-" . $register_year;

    return $register_date;
}

function generate_visit_date($register_date)
{
    $timestamp_register = strtotime($register_date);

    $current_date = new DateTime();
    $timestamp_current = $current_date->getTimestamp();

    $timestamp_visit = rand($timestamp_register, $timestamp_current);
    $visit_date = date("d-m-Y",$timestamp_visit);

    return $visit_date;
}

function add_users($connect, $users_count)
{
    $faker = Faker\Factory::create();
    $sql ="INSERT INTO Users (login, password, date_register, date_last_visit, ip, active) VALUES ";

    for ($i=0; $i<$users_count; $i++){

        $login = generate_login($faker);
        $password = generate_password($faker);
        $ip = generate_ip(255 - intval($i/10000));
        $reg_date = generate_register_date();
        $visit_date = generate_visit_date($reg_date);
        $active = rand(0, 1);

        $sql .= "('{$login}', 
                  '{$password}', 
                  '{$reg_date}', 
                  '{$visit_date}',
                  '{$ip}',
                  '{$active}')";
        if ($i<($users_count - 1)) $sql .= ", ";
    }
    $connect->query($sql);
}

function users_to_groups($connect, $users_count)
{
    $sql = "INSERT INTO Users_Groups (user_id, group_id) VALUES ";

    for ($i = 1; $i < ($users_count + 1); $i++) {
        $is_admin = rand(0, 20);
        if ($is_admin === 20) {
            $sql .= "('{$i}', 
                  '4'),";
        } else {
            $is_temporary = rand(0, 1);
            $is_regular = rand(0, 1);
            $is_editor = rand(0, 1);
            if ($is_temporary == 1) {
                $sql .= "('{$i}', '1'),";
            }
            if ($is_regular == 1) {
                $sql .= "('{$i}', '2'),";
            }
            if ($is_editor == 1) {
                $sql .= "('{$i}', '3'),";
            }
            if (($is_temporary + $is_regular + $is_editor) == 0) $sql .= "('{$i}', '1'),";
        }
        if ($i == ($users_count)) $sql = substr($sql, 0, -1);
    }
    $connect->query($sql);
}

function add_partners($connect, $partners_count)
{
    $sql = "INSERT INTO Partners (partner_name) VALUES ";
    for ($i = 1; $i < ($partners_count+1); $i++){
        $sql .= "('Partner{$i}'),";
    }
    $sql .= "('no partner')";
    $connect->query($sql);
    echo "<br>";
    echo mysqli_error($connect);
}

function add_items($connect, $items_count)
{
    $sql = "INSERT INTO Items (item_name, item_price) VALUES ";
    for ($i = 1; $i < ($items_count+1); $i++){
        $item_price = $i * 1000;
        $sql .= "('Item{$i}', '{$item_price}'),";
        if ($i == ($items_count)) $sql = substr($sql, 0, -1);
    }
    $connect->query($sql);
    echo "<br>";
    echo mysqli_error($connect);
}

function add_orders($connect, $orders_count, $users_count, $items_count, $partners_count)
{
    $sql = "INSERT INTO Orders (user_id, item_id, partner_id) VALUES ";
    for ($i = 1; $i < ($orders_count+1); $i++){
        $user_id = rand(1, $users_count);
        $item_id = rand(1, $items_count);
        $partner_id = rand(1, $partners_count+1);

        $sql .= "('{$user_id}', '{$item_id}', '{$partner_id}'),";
        if ($i == ($orders_count)) $sql = substr($sql, 0, -1);
    }
    $connect->query($sql);
    echo "<br>";
    echo mysqli_error($connect);
}

function get_purchases_no_partners($connect, $partners_count)
{
    $partner_id = $partners_count + 1;
    $purchases = get_purchases_by_user($connect, $partner_id);

    foreach ($purchases as $purchase){
        $user_id = $purchase['user_id'];
        $sql = "SELECT * FROM Users WHERE id={$user_id}";
        $result = $connect->query($sql);
        $result = $result->fetch_assoc();
        $total_items = $purchase['item1'] + $purchase['item2'];
        $total_usd = $purchase['item1']*1000 + $purchase['item2']*1000*2;

        echo "<hr>";
        echo "User: " . $result['login'] . "<br>";
        echo "Item1: " . $purchase['item1'] . "pcs | " . $purchase['item1']*1000 . "$<br>";
        echo "Item2: " . $purchase['item2'] . "pcs | " . $purchase['item2']*1000*2 . "$<br>";
        echo "Total: " . $total_items . " items<br>";
        echo "Total: " . $total_usd . "$<br>";
    }
}

function get_purchases_by_user($connect, $partner_id)
{
    $sql = "SELECT * FROM Orders WHERE partner_id={$partner_id}";
    $result = $connect->query($sql);

    while($row = $result->fetch_assoc()){
        $purchases[] = $row;
    };

    foreach ($purchases as $purchase){
        $users_with_purchase[] = $purchase['user_id'];
    }

    $users_unique = array_unique ($users_with_purchase);

    $purchases_by_users = [];

    foreach ($purchases as $purchase){
        foreach ($users_unique as $user_unique){
            if ($purchase['user_id'] == $user_unique){
                $user_id = $purchase['user_id'];
                if (!(isset($purchases_by_users[$user_unique]['item1']))) $purchases_by_users[$user_unique]['item1'] = 0;
                if (!(isset($purchases_by_users[$user_unique]['item2']))) $purchases_by_users[$user_unique]['item2'] = 0;
                $purchases_by_users[$user_unique]['user_id'] = $purchase['user_id'];
                if ($purchase['item_id']==1) {
                    $purchases_by_users[$user_unique]['item1']++;
                } else $purchases_by_users[$user_unique]['item2']++;
            }
        }
    }
    return $purchases_by_users;
}

function get_purchases_by_partner($connect, $partners_count)
{
    for ($i = 1; $i<=$partners_count; $i++){
        $sql = "SELECT * FROM Orders WHERE partner_id={$i}";
        $result = $connect->query($sql);
        while ($row = $result->fetch_assoc()) {
            $partner_purchases[$i][] = $row;
        };

        foreach ($partner_purchases[$i] as $partner_purchase){
            $partner_users[$i][] = $partner_purchase['user_id'];
        }

        $partner_users[$i] = array_unique($partner_users[$i]);
        $purchase_count[$i] = count($partner_users[$i]);

        $sql = "SELECT * FROM Partners WHERE id={$i}";
        $result = $connect->query($sql);

        $row = $result->fetch_assoc();
        $partner_names[$i] = $row['partner_name'];

        echo "<hr>Partner name: " . $partner_names[$i] . " | Purchased for users: " . $purchase_count[$i] . "<br>";
    }
    return $purchase_count;
}

function get_partner_usd_by_user($connect, $partners_count, $purchases_by_partner)
{
    for ($partner_id = 1; $partner_id<=$partners_count; $partner_id++){
        $sql = "SELECT * FROM Partners WHERE id={$partner_id}";
        $result = $connect->query($sql);
        $row = $result->fetch_assoc();
        $partner_name = $row['partner_name'];
        $purchases = get_purchases_by_user($connect, $partner_id);
        $total_usd_by_partner[$partner_id] = 0;
        echo "<hr>" . $partner_name . "<br>";
        foreach ($purchases as $purchase){
            $user_id = $purchase['user_id'];
            $sql = "SELECT * FROM Users WHERE id={$user_id}";
            $result = $connect->query($sql);
            $row = $result->fetch_assoc();
            $user_login = $row['login'];
            $total_usd = $purchase['item1']*1000 + $purchase['item2']*1000*2;
            $total_usd_by_partner[$partner_id] += $total_usd;
            echo "<br>User: " . $user_login . " | Total USD: " . $total_usd;
        }
        $average_usd = intval($total_usd_by_partner[$partner_id] / $purchases_by_partner[$partner_id]);
        echo "<br>Total: " . $total_usd_by_partner[$partner_id] . "$";
        echo "<br>Average by user: " . $average_usd . "$";
    }
}

add_users($connect, $users_count);
users_to_groups($connect, $users_count);
add_partners($connect, $partners_count);
add_items($connect, $items_count);
add_orders($connect, $orders_count, $users_count, $items_count, $partners_count);
get_purchases_no_partners($connect, $partners_count);
$purchases_by_partner = get_purchases_by_partner($connect, $partners_count);
get_partner_usd_by_user($connect, $partners_count, $purchases_by_partner);

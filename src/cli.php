<?php

use DTOs\User\registeruserdto;

require 'db_connection.php';
require "auth.php";
require "DTOs\User\registeruser_dto.php";
require "Models\user.php";

$auth = new auth($pdo);

do {
    echo "Are you need to register or login? Select 1 for register or 2 for login\n";
    $auth_type = readline();
    if ($auth_type == "1") {
        do {
            echo "Write your name\n";
            $user_name = readline();

            if ($user_name == "") {
                echo "Write your name\n";
                continue;
            }

            do {
                echo "Write your email\n";
                $user_email = readline();

                if (!$user_email || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                    echo "Wrong email format\n";
                    continue;
                }
                if ($auth->check_email($user_email)) {
                    echo "Email is already in use\n";
                    continue;
                }

                do {
                    echo "\nWrite your password\n";
                    $user_password = readline();

                    if (strlen($user_password) < 3 || strlen($user_password) >= 32) {
                        echo "Wrong password length\n";
                        continue;
                    }

                    $user_dto = new registeruserdto($user_name, $user_email, $user_password);

                    try{
                        $auth->create_user($user_dto);
                    } catch (Exception $e) {
                        echo "Error: " . $e->getMessage() . "\n";
                    }
                } while (strlen($user_password) < 3 || strlen($user_password) >= 32);
            } while (!$user_email || !filter_var($user_email, FILTER_VALIDATE_EMAIL));
        } while(!$user_name);
    } else if ($auth_type == "2") {
        do {
            echo "Write your email\n";
            $user_email = readline();

            if (!$user_email || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                echo "Wrong email format\n";
                continue;
            }

            echo "\nWrite your password\n";
            $user_password = readline();

            try {
                $user_dto = $auth->login($user_email, $user_password);
                echo "You are logged, " . $user_dto->name . "\n";
                break;
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
            }
        } while (!$user_email || !filter_var($user_email, FILTER_VALIDATE_EMAIL));
    } else {
        echo "Are you need to register or login? Select 1 for register or 2 for login\n";
    }
} while ($auth_type != 1 && $auth_type != 2);
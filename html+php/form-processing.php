<?php

// Function to return HTMX-compatible response
function returnHtmxResponse($success, $message)
{
    $class = $success ? "success" : "error";
    echo "<div style='text-align: center;'>";
    echo "<div class='$class'>$message</div>";
    echo "<a href='#' onclick='location.reload()' style='display: inline-block; margin-top: 1rem;'>↻</a>";
    echo "</div>";
    exit();
}

function sanitize($input) {
    return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
}

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $name = sanitize($_POST["name"]);
    $description = sanitize($_POST["description"]);
    $phone = sanitize($_POST["phone"]);
    $email = sanitize($_POST["email"]);


    // Basic validation
    if (
        $name === false ||
        $description === false ||
        $phone === false ||
        $email === false
    ) {
        returnHtmxResponse(false, "Неправильные данные");
    }

    // Prepare data for API
    $data = [
        "name" => $name
        "description" => $description,
        "email" => $email,
        "phone" => $phone
    ];

    // API endpoint URL
    $api_url = "https://YOUR_ACCOUNT.planfix.ru/webhook/json/..."; // ⚠️ меняйте урл на ваш, вебхук POST-запрос в формате json
    //$api_url = 'https://webhook.site/ //это для тестирования нужно сходить на сайт и сгенерить свежий урл

    // Initialize cURL session
    $ch = curl_init($api_url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

    // Execute cURL request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Close cURL session
    curl_close($ch);
 
    // сборка темы письма
    $rsubject = rawurlencode("Запрос от  " . $email );

    // Handle API response
    if ($http_code == 200) {
        $response_data = json_decode($response, true);
        if (isset($response_data["task"])) {
            returnHtmxResponse(
                true,
                '<a href="mailto:task+' . $response_data["task"] . "@YOUR_ACCOUNT.planfix.ru?subject=" . $rsubject . "&body=" . rawurlencode("Я отправил форму, но мне бы поскорее так как ...") . '">Отправить письмо для подтверждения/ускорения заявки № ' . $response_data["task"] . "</a>");
        } else {
            returnHtmxResponse(true, "Что-то пошло не так");
        }
    } else {
        returnHtmxResponse(false, "Возникла ошибка");
    }
} else {
    returnHtmxResponse(false, "Неравильный тип запроса");
}
?>

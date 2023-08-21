<?
	# ajax/order_payment.php

	# Создание заказа

	# Подключаем необходимые константы
	require_once($_SERVER['DOCUMENT_ROOT'] . "/options.php");

	if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

		$result = array();

		# Обрабатываем данные о заказе из POST запроса
		$user_email = strip_tags(htmlspecialchars($_POST["email"]));
		$user_phone = strip_tags(htmlspecialchars($_POST["phone"]));
		$order_id = strip_tags(htmlspecialchars($_POST["id"]));
		$description = "Оплата заказа №" . $order_id;
		$product_title = strip_tags(htmlspecialchars($_POST["product_title"]));
		$order_price = strip_tags(htmlspecialchars($_POST["price"]));
    $cash_items = base64_encode(json_encode([["name" => $product_title, "count" => 1, "price" => $order_price, "type" => "commodity"]]));
	
    $data = array(
      "method" => "initPayment",
      "params[paymentType]" => "card",
      "params[account]" => $user_email,
      "params[sum]" => $order_price,
      "params[projectId]" => UNITPAY_PROJECT_ID,
      "params[desc]" => $description,
      "params[secretKey]" => UNITPAY_SECRET_KEY,
      "params[method]" => "card",

      # Эти 3 параметра заполняются если подключена онлайн-касса и тербуется выставление чеков клиентам
      "params[customerEmail]" => $user_email,
      "params[customerPhone]" => $user_phone,
      "params[cashItems]" => $cash_items
    );

    # Отправляем GET запрос на создание платежа в unitpay, при этом unitpay сначала отправит GET запрос на наш handler.php с параметром method=check
    # для проверки готовы ли мы принять заказ и только потом создаст его
    $ch = curl_init(UNITPAY_URL . http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $payment_info = json_decode(curl_exec($ch));
    curl_close($ch);

    if ($payment_info->result->paymentId > 0) {

      # Если заказ создался и нам пришел его можем прикрепить номер заказа в unitpay к заказу в нашей системе

    }else {
      # Если заказ не создался записываем ошибку
      $result["error"]["message"] = $payment_info->error->message;
    }

    echo json_encode($result);
  }

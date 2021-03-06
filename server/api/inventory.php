<?php

$link = get_db_link();

if ($request['method'] === 'GET') {
  $category_id = $request['query']['categoryId'];
  if(!isset($category_id)){
    throw new ApiError("Missing categoryId from query param");
  }
  $response['body'] = [
    "inventory" => get_category_inventory($link,$category_id, $_SESSION['user_id']),
    "units" => get_units($link)
  ];
  send($response);
}
else if ($request['method'] === 'POST'){
  $item_name = $request['body']['itemName'];
  $amount = $request['body']['amount'];
  $unit_id = $request['body']['unitId'];
  $category_id = $request['body']['categoryId'];
  $notes = $request['body']['notes'];
  $new_item = add_new_item($link, $item_name, $amount, $unit_id, $category_id, $notes, $_SESSION['user_id']);
  $response['body'] = $new_item;
  send($response);
}

else if ($request['method'] === 'DELETE'){
  $item_id = $request['body']['itemId'];
  if (!isset($item_id)){
    throw new ApiError("Missing itemId from DELETE request");
  }
  delete_item($link, $item_id);
  $response['body'] = [
    'message' => "Item successfully deleted"
  ];
  send($response);
}

else if($request['method'] === 'PATCH'){
  $new_item_name = $request['body']['itemName'];
  $item_id = $request['body']['id'];
  $item_amount = $request['body']['amount'];
  $unit_id = $request['body']['unitId'];
  $notes = $request['body']['notes'];
  update_item($link, $item_id, $new_item_name, $item_amount, $unit_id, $notes);
  $response['body'] = [
    'item' => 'updated'
  ];
  send($response);
}

function get_category_inventory($link,$category_id, $user_id){
  $sql = "
  SELECT `i`.`itemName` as `itemName`,
  `i`.`itemId` as `id`,
  CONCAT(`i`.`amount`,' ',`u`.`unitName`) as `amountString`,
  `i`.`unitId`,
  `i`.`amount`,
  `i`.`notes`
  FROM `inventory` AS `i`
  JOIN `units` AS `u` ON `u`.`unitId` = `i`.`unitId`
  JOIN `categories` AS `c` ON `c`.`categoryId`=`i`.`categoryId`
  WHERE `i`.`userId` = $user_id AND `c`.`categoryId` = '{$category_id}'";
  $result = mysqli_query($link, $sql);
  return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function get_units($link){
  $sql = "SELECT * from units";
  $result = mysqli_query($link, $sql);
  return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function add_new_item($link, $item_name, $amount, $unit_id, $category_id, $notes, $user_id){
  $sql = "INSERT INTO `inventory`
  (`itemId`, `itemName`, `amount`, `userId`, `unitId`, `categoryId`, `notes`)
  VALUES
  (NULL,'$item_name', '$amount','$user_id','$unit_id','$category_id','$notes')";
  mysqli_query($link, $sql);
  $new_item_id = mysqli_insert_id($link);
  return get_inventory_item($link, $new_item_id);
}

function update_item($link, $item_id, $item_name, $amount, $unit_id, $notes){
  $sql = "UPDATE `inventory`
  SET `itemName` = '$item_name',
  `amount` = '$amount',
  `unitId` = '$unit_id',
  `notes` = '$notes'
  WHERE `inventory`.`itemId` = '$item_id'";
  mysqli_query($link, $sql);
}

function get_inventory_item($link, $item_id){
  $sql = "
  SELECT `i`.`itemName` as `itemName`,
  `i`.`itemId` as `id`,
  CONCAT(`i`.`amount`,' ',`u`.`unitName`) as `amountString`,
  `i`.`unitId`,
  `i`.`amount`,
  `i`.`notes`
  FROM `inventory` AS `i`
  JOIN `units` AS `u` ON `u`.`unitId` = `i`.`unitId`
  WHERE `i`.`itemId` = '$item_id' ";
  $result = mysqli_query($link, $sql);
  return mysqli_fetch_object($result);
}

function delete_item($link, $item_id){
  $sql = "
  DELETE
  FROM `inventory`
  WHERE `itemId` = '$item_id'";
  mysqli_query($link, $sql);
}

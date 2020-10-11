<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");

include "config.php";
include "database.php";

echo $_SERVER['REQUEST_METHOD'];
exit();

// Conexión con la base de datos
$dbConn = connect($db);

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if (isset($_GET['id']))
    {
      //Mostrar un post
      $sql = $dbConn->prepare("SELECT * FROM clientes where id=:id");
      $sql->bindValue(':id', $_GET['id']);
      $sql->execute();
      header("HTTP/1.1 200 OK");
      echo json_encode($sql->fetch(PDO::FETCH_ASSOC));
      exit();
	  } else {
      //Mostrar lista de post
      $sql = $dbConn->prepare("SELECT * FROM clientes");
      $sql->execute();
      $sql->setFetchMode(PDO::FETCH_ASSOC);
      header("HTTP/1.1 200 OK");
      echo json_encode($sql->fetchAll());
      exit();
	}
}

// Crear un nuevo registro
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
  parse_str(file_get_contents("php://input"), $input);
  $input = $_POST;
  $sql = "INSERT INTO clientes (nombre, apellidos, correo, telefono) VALUES (:nombre, :apellidos, :correo, :telefono)";
  $statement = $dbConn->prepare($sql);
  bindAllValues($statement, $input);
  $statement->execute();
  $postId = $dbConn->lastInsertId();
  if ($postId) {
    $input['id'] = $postId;
  }
  header("HTTP/1.1 200 OK");
  echo json_encode($input);
  exit();
}

//Actualizar
if ($_SERVER['REQUEST_METHOD'] == 'PUT')
{
  $response = array();
  $response['success'] = FALSE;
  if (isset($_GET['id'])) {
    parse_str(file_get_contents("php://input"), $input);
    $postId = $_GET['id'];
    $fields = getParams($input);
    $sql = "UPDATE clientes SET $fields WHERE id='$postId'";
    $statement = $dbConn->prepare($sql);
    bindAllValues($statement, $input);
    $result = $statement->execute();
    if ($result == TRUE) {
      $response['message'] = "El registro fue actualizado";
      $response['success'] = TRUE;
    } else {
      $response['message'] = "Hubo un error al actualizar el registro";
    }
  } else {
    $response['message'] = "No se definió el Id del registro a actualizar";
  }
  header("HTTP/1.1 200 OK");
  echo json_encode($response);
  exit();
}

//Borrar
if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
{
  $response = array();
  $response['success'] = FALSE;
  if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $statement = $dbConn->prepare("DELETE FROM clientes where id=:id");
    $statement->bindValue(':id', $id);
    $statement->execute();
    $result = $statement->execute();
    if ($result == TRUE) {
      $response['message'] = "El registro fue eliminado";
      $response['success'] = TRUE;
    } else {
      $response['message'] = "Hubo un error al eliminar el registro";
    }
  } else {
    $response['message'] = "No se definió el Id del registro a actualizar";
  }
  header("HTTP/1.1 200 OK");
  echo json_encode($response);
  exit();
}

//En caso de que ninguna de las opciones anteriores se haya ejecutado
header("HTTP/1.1 400 Bad Request");
?>

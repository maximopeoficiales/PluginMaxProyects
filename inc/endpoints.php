<?php

use mjohnson\utility\TypeConverter;
use Rakit\Validation\Validator;
//utilidades
function mfSendResponse($response, $message, $data = null, $status = 200)
{
     $json = true;
     $typeApp = $json ? "json" : "xml";
     $array = array(
          'RESPONSE' => $response,
          'DETAILS' => $message,
          'STATUS' => $status,
          'DATA' => $data,
     );
     header("Content-Type: text/$typeApp; charset=utf-8");
     // status_header(intval($status));
     if ($json)
          return $array;
     else
          print(mfArrayToXML($array));
}
function mfIsAuthorized($user, $password)
{
     if (true) {
          return true;
     } else {
          return false;
     }
}
function mfNotAuthorized()
{
     return mfSendResponse(0, "Error en la autenticacion", null, 400);
}
function mfXmlToArray($url)
{
     $xml = file_get_contents($url);
     $array = TypeConverter::xmlToArray($xml, TypeConverter::XML_MERGE);
     return $array;
}
function mfArrayToXML($data)
{

     $data = TypeConverter::toXml($data);
     return $data;
}
//funciones que retorna respuesta
function mfGetUnitWithMetadata($metadata, $unit)
{
     $value = "";
     foreach ($metadata as $key) {
          if ($key["key"] == "unit") {
               $valueUnit = $key["value"];
               $extract = explode(":", $valueUnit);
               if ($extract[0] == $unit) {
                    $value = $extract[1];
               }
          }
     }
     return $value;
}

function mfUpdateProductWithSku($sku, $dataUpdated)
{
     $woo = max_functions_getWoocommerce();
     $findMaterial = $woo->get("products", ["sku" => $sku]);
     $response = $woo->put("products/" . $findMaterial[0]->id, $dataUpdated);
     return $response;
}
function mfCreateProductWoo($data)
{

     $woo = max_functions_getWoocommerce();
     $material = $data["material"];
     $weight = number_format($material["weight"], 2, ".", "");
     $sku = $material["sku"];
     $dataSend = [
          'name' => $material["name"],
          'sku' => $sku,
          'weight' => $weight,
          "metadata" => [],
     ];
     if ($material["unit"] !== "kg") {
          $dataSend["meta_data"] = [
               [
                    "key" => "unit",
                    "value" => $material['unit'] . ":" . $weight
               ]
          ];
     }

     try {
          $response = $woo->post('products', $dataSend); //devuelve un objeto
          if (!$response->id == null) {
               return [
                    "value" => 1,
                    "data" => $response,
                    "message" => "Registro de Material Exitoso",
               ];
          }
     } catch (\Throwable $th) {
          return [
               "value" => 0,
               "message" => "EL SKU: $sku ya existe",
          ];
     }
}
function mfUpdateProductWoo($sku, $data)
{
     $material = $data["material"];
     //validations
     $params = ["sku" => $sku, "stock" => $material["stock"]];
     $validation = mfUtilityValidator($params, [
          'sku'                  => 'required|max:12',
          'stock'                  => 'required|max:5',
     ]);
     if (!$validation["validate"]) {
          return ["value" => $validation["value"], "message" => $validation["message"]];
     }
     //updated
     $dataUpdated = [
          "manage_stock" => true,
          "stock_quantity" => $material["stock"],
     ];
     try {
          $response = mfUpdateProductWithSku($sku, $dataUpdated);
          return [
               "value" => 2,
               "message" => "Material con sku: $sku actualizado",
               "data" => json_decode(json_encode($response), true)
          ];
          /*    } */
     } catch (\Throwable $th) {
          return [
               "value" => 0,
               "message" => "El material con el sku: $sku no existe",
          ];
     }
}
function mfCreateClientWoo($data)
{

     $woo = max_functions_getWoocommerce();
     $client = $data["client"];
     $email = $client["email"];
     $dataSend = [
          'first_name' => $client["name"],
          'email' => $email,
          "billing" => [
               'first_name' => $client["name"],
               'address_1' => $client["address"],
               'phone' => $client["telephone"],
               'email' => $email,
          ],
          "meta_data" =>  [
               [
                    "key" => "cd_cli",
                    "value" => $client['cd_cli']
               ]
          ]

     ];
     $exists = email_exists($email);
     if ($exists) {
          return [
               "value" => 0,
               "message" => "EL email: $email ya existe",
          ];
     } else {
          $response = $woo->post('customers', $dataSend); //devuelve un objeto
          if ($response->id !== null) {
               return [
                    "value" => 1,
                    "data" => $response,
                    "message" => "Registro de Cliente Exitoso",
               ];
          }
     }
}
function mfUpdateClientWoo($cd_cli, $data)
{
     global $wpdb;
     $table = $wpdb->base_prefix . 'usermeta';
     $sql = "SELECT user_id FROM $table WHERE meta_key = 'cd_cli' and meta_value= '%d' LIMIT 1";
     $result = $wpdb->get_results($wpdb->prepare($sql, $cd_cli));
     if (empty($result)) {
          return [
               "value" => 0,
               "message" => "EL ID_CLI: $cd_cli no existe",
          ];
     } else {
          $id_cliente = $result[0]->user_id;
          //actualizacion de cliente
          $woo = max_functions_getWoocommerce();
          $client = $data["client"];
          //validaciones
          $validation = mfUtilityValidator($client, [
               'name' => 'required|max:40',
               'telephone' => 'required|max:9',
               'email' => 'email|max:30',
               'address' => 'required|max:70',
          ]);
          if (!$validation["validate"]) {
               return ["value" => 0, "message" => $validation["message"]];
          }

          $email = $client["email"];
          $dataUpdated = [
               'first_name' => $client["name"],
               'email' => $email,
               "billing" => [
                    'first_name' => $client["name"],
                    'address_1' => $client["address"],
                    'phone' => $client["telephone"],
                    'email' => $email,
               ],
          ];
          $exists = email_exists($email);
          if ($exists) {
               return [
                    "value" => 0,
                    "message" => "EL email: $email ya esta registrado",
               ];
          } else {
               $response = $woo->put("customers/$id_cliente", $dataUpdated); //devuelve un objeto
               if ($response->id !== null) {
                    return [
                         "value" => 2,
                         "message" => "Todo Bien",
                         "data" => $response,
                    ];
               }
          }
     }
}

//callbacks de endpoints
function mfCreateMaterial($params)
{
     $data = mfXmlToArray("php://input"); //recogo data xml
     return  mfValidationGeneralAuth($data, $params, function ($data) {
          $material = $data["material"];
          $validateMaterial = mfValidateMaterialFields($material); //validacion de security
          if ($validateMaterial["validate"]) {
               // $created = mfCreateProductWoo($data);
               // return mfSendResponse($created["value"],$created["message"],$created["data"]);
               return mfSendResponse(1, "Todo Correcto");
          } else {
               return mfSendResponse(0, $validateMaterial["message"], null, 400);
          }
     }, ["security" => "required", "material" => "required"]);
}

function mfUpdateMaterial($params)
{
     $data = mfXmlToArray("php://input"); //recogo data xml
     return  mfValidationGeneralAuth($data, $params, function ($data, $params) {
          // $sku = $params["sku"];
          // $updated = mfUpdateProductWoo($sku, $data);
          // return mfSendResponse($updated["value"],$updated["message"],$updated["data"]);
          return mfSendResponse(1, "Todo Correcto");
     }, ["security" => "required", "material" => "required"]);
}
function mfCreateClient($params)
{
     $data = mfXmlToArray("php://input"); //recogo data xml
     return  mfValidationGeneralAuth($data, $params, function ($data) {
          $client = $data["client"];
          $validateClient = mfValidateClientFields($client); //validacion de security
          if ($validateClient["validate"]) {
               // $created = mfCreateClientWoo($data);
               // return mfSendResponse($created["value"], $created["message"], $created["data"]);
               return mfSendResponse(1, "Todo Correcto");
          } else {
               return mfSendResponse(0, $validateClient["message"], null, 400);
          }
     }, ["security" => "required", "client" => "required"]);
}
function mfUpdateClient($params)
{
     $data = mfXmlToArray("php://input"); //recogo data xml
     return  mfValidationGeneralAuth($data, $params, function ($data, $params) {
          $cd_cli = $params["cd_cli"];
          $updated = mfUpdateClientWoo($cd_cli, $data);
          return mfSendResponse($updated["value"], $updated["message"], $updated["data"]);
     }, ["security" => "required", "client" => "required"]);
}

//EndPoints
//------Materiales------
// http://maxco.punkuhr.test/wp-json/max_functions/v1/materials (POST)
add_action("rest_api_init", function () {
     register_rest_route("max_functions/v1", "/materials", array(
          "methods" => "POST",
          "callback" => "mfCreateMaterial",
          'args'            => array(),
     ));
});
// http://maxco.punkuhr.test/wp-json/max_functions/v1/materials/sku (PUT)
add_action("rest_api_init", function () {
     register_rest_route("max_functions/v1", "/materials/(?P<sku>\d+)", array(
          "methods" => "PUT",
          "callback" => "mfUpdateMaterial",
          'args'            => array(),
     ));
});
//------Clientes------
// http://maxco.punkuhr.test/wp-json/max_functions/v1/clients (POST)
add_action("rest_api_init", function () {
     register_rest_route("max_functions/v1", "/clients", array(
          "methods" => "POST",
          "callback" => "mfCreateClient",
          'args'            => array(),
     ));
});
// http://maxco.punkuhr.test/wp-json/max_functions/v1/clients/cd_cli (PUT)
add_action("rest_api_init", function () {
     register_rest_route("max_functions/v1", "/clients/(?P<cd_cli>\d+)", array(
          "methods" => "PUT",
          "callback" => "mfUpdateClient",
          'args'            => array(),
     ));
});

//validations
function mfValidationGeneralAuth($data, $params = null, $function, $validations = [])
{
     $validateBody = mfValidateDataEmpty($data, $validations); //validacion de data
     if ($validateBody["validate"]) {
          $security = $data["security"];
          $validateSecurity = mfValidateSecurityFields($security); //validacion de security
          if ($validateSecurity["validate"]) {
               if (mfIsAuthorized($security["user"], $security["pass"])) {
                    return $function($data, $params);
               } else {
                    return mfNotAuthorized();
               }
          } else {
               return mfSendResponse(0, $validateSecurity["message"], null, 400);
          }
     } else {
          return mfSendResponse(0, $validateBody["message"], null, 400);
     }
}
function mfValidateDataEmpty($data, $validations)
{
     $validator = new Validator;
     $validation = $validator->make($data, $validations);
     $validation->validate();
     if ($validation->fails()) {
          // handling errors
          $errors = $validation->errors();
          return ["validate" => false, "message" => $errors->firstOfAll()];
     } else {
          return ["validate" => true];
     }
}
function mfValidateSecurityFields($security)
{
     return mfUtilityValidator($security, [
          'user'                  => 'required|max:11',
          'pass'              => 'required|max:13',
     ]);
}
function mfValidateMaterialFields($material)
{
     return mfUtilityValidator($material, [
          'sku'                  => 'required|max:12',
          'name'              => 'required|max:40',
          'unit'              => 'required|max:3',
          'weight'              => 'required|max:6',
     ]);
}
function mfValidateClientFields($client)
{
     return mfUtilityValidator($client, [
          'cd_cli' => 'required|max:10',
          'name' => 'required|max:40',
          'telephone' => 'required|max:9',
          'email' => 'required|max:30|email',
          'address' => 'required|max:70',
     ]);
}


function mfUtilityValidator($params, $validations)
{
     $validator = new Validator;
     $validation = $validator->make($params, $validations);
     $validation->validate();
     if ($validation->fails()) {
          $errors = $validation->errors();
          return ["validate" => false, "message" => $errors->firstOfAll()];
     } else {
          return ["validate" => true];
     }
}

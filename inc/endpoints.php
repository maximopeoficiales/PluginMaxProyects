<?php

use mjohnson\utility\TypeConverter;
use Rakit\Validation\Validator;
use FluidXml\FluidXml;
//utilidades
function mfCreateXmlMultiObject($response, $details, $status, $data, $nameData = "data", $multidata = false)
{
     // $data = json_decode(json_encode($data), true);
     // $data = TypeConverter::toArray($data);
     $xml = new FluidXml("root");
     try {
          $xmlArray = [
               "RESPONSE" => $response,
               "DETAILS" => [],
               "STATUS" => $status,
               "DATA" => [],
          ];

          if (is_array($details)) {
               foreach ($details as $key => $value) {
                    array_push($xmlArray["DETAILS"], [$key => $value]);
               }
          } else {
               $xmlArray["DETAILS"] = $details;
          }

          if (is_array($data) || is_object($data)) {
               if ($multidata) {
                    $count = 1;
                    foreach ($data as $key1 => $value1) {
                         if (is_array($value1)) { //primera hay 2 elementos
                              $xmlArray["DATA"][$nameData . "-" . $count] = [];
                              $user = $xmlArray["DATA"][$nameData . "-" . $count]; //primer elemento
                              //creacion de datos de usuerio
                              foreach ($value1 as $key2 => $value2) {
                                   // $xmlArray["DATA"][$nameData."-".$count][$key2] = [];
                                   if (is_array($value2)) {
                                        $user[$key2] = [];
                                        foreach ($value2 as $key3 => $value3) {
                                             if (is_array($value3)) {
                                                  $user[$key2][$key3] = [];
                                                  foreach ($value3 as $key4 => $value4) {
                                                       if (is_array($value4)) {
                                                            $user[$key2][$key3] = [];
                                                            array_push($user[$key2][$key3], [$key4 => $value4]);
                                                       } else {
                                                            array_push($user[$key2][$key3], [$key4 => $value4]);
                                                       }
                                                  }
                                             } else {
                                                  array_push($user[$key2], [$key3 => $value3]);
                                             }
                                        }
                                   } else {
                                        array_push($user,   [$key2 => $value2]);
                                   }
                              }

                              $xmlArray["DATA"][$nameData . "-" . $count] = $user;
                              //-------------
                         } else {
                              array_push($xmlArray["DATA"][$nameData], [$key1 => $value1]);
                         }
                         $count++;
                    }
               } else {
                    $xmlArray["DATA"] = [$nameData => []];
                    foreach ($data as $key1 => $value1) {
                         if (is_array($value1) || is_object($value1)) { //primera capa

                              if (!empty($value1)) {
                                   $xmlArray["DATA"][$nameData][$key1] = [];
                                   $user = $xmlArray["DATA"][$nameData][$key1];
                                   foreach ($value1 as $key2 => $value2) {
                                        if (is_array($value2) || is_object($value2)) {
                                             $user[$key2] = [];
                                             foreach ($value2 as $key3 => $value3) {
                                                  if (is_string($value3)) {
                                                       array_push($user[$key2], [$key3 => $value3]);
                                                  }
                                             }
                                        } else if (is_string($value2)) {
                                             array_push($user, [$key2 => $value2]);
                                        }
                                   }
                                   $xmlArray["DATA"][$nameData][$key1] = $user;
                              }
                         } else if (is_string($value1) || is_int($value1)) {
                              if ($value1 !== "") {
                                   array_push($xmlArray["DATA"][$nameData], [$key1 => $value1]);
                              } else {
                                   // array_push($xmlArray["DATA"][$nameData], [$key1 => " "]);
                              }
                         }
                    }
               }
          } else if (is_string($data)) {
               $xmlArray["DATA"] = $data;
          }
          $xml->add($xmlArray);
     } catch (\Throwable $th) {
          $xml->add("Ocurrio en error en la creacion de xml");
     }
     return $xml->xml();
}

function mfSendResponse($response, $message, $status = 200, $data = null, $nameData = "data", $multidata = false)
{
     $json = false;
     $typeApp = $json ? "json" : "xml";
     $array = array(
          'RESPONSE' => $response,
          'DETAILS' => $message,
          'STATUS' => $status,
          'DATA' => $data,
     );

     header("Content-Type: text/$typeApp; charset=utf-8");
     // status_header(intval($status));
     if ($json) {
          return $array;
     } else {
          $xml = mfCreateXmlMultiObject($response, $message, $status, $data, $nameData, $multidata);
          print($xml);
     }
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
     return mfSendResponse(0, "Error en la autenticacion", 400);
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
               "data" => $response
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
                         "message" => "Se ha actualizado el Cliente con el id $cd_cli",
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
               $created = mfCreateProductWoo($data);
               return mfSendResponse($created["value"], $created["message"], 200, $created["data"], "material");
               // return mfSendResponse(1, "Todo Correcto");
          } else {
               return mfSendResponse(0, $validateMaterial["message"], 400);
          }
     }, ["security" => "required", "material" => "required"]);
}

function mfUpdateMaterial($params)
{
     $data = mfXmlToArray("php://input"); //recogo data xml
     return  mfValidationGeneralAuth($data, $params, function ($data, $params) {
          $sku = $params["sku"];
          $updated = mfUpdateProductWoo($sku, $data);
          return mfSendResponse($updated["value"], $updated["message"], 200, $updated["data"], "material");
          // return mfSendResponse(1, "Todo Correcto");
     }, ["security" => "required", "material" => "required"]);
}
function mfCreateClient($params)
{
     $data = mfXmlToArray("php://input"); //recogo data xml
     return  mfValidationGeneralAuth($data, $params, function ($data) {
          $client = $data["client"];
          $validateClient = mfValidateClientFields($client); //validacion de security
          if ($validateClient["validate"]) {
               $created = mfCreateClientWoo($data);
               return mfSendResponse($created["value"], $created["message"], 200, $created["data"], "client");
               // return mfSendResponse(1, "Todo Correcto");
          } else {
               return mfSendResponse(0, $validateClient["message"], 400);
          }
     }, ["security" => "required", "client" => "required"]);
}
function mfUpdateClient($params)
{
     $data = mfXmlToArray("php://input"); //recogo data xml
     return  mfValidationGeneralAuth($data, $params, function ($data, $params) {
          $cd_cli = $params["cd_cli"];
          $updated = mfUpdateClientWoo($cd_cli, $data);
          return mfSendResponse($updated["value"], $updated["message"], 200, $updated["data"], "client");
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
               return mfSendResponse(0, $validateSecurity["message"], 400, null);
          }
     } else {
          return mfSendResponse(0, $validateBody["message"],  400, null);
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
          'telephone' => 'required|min:9|max:9',
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

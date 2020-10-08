<?php

use mjohnson\utility\TypeConverter;
use Rakit\Validation\Validator;
use FluidXml\FluidXml;
//utilidades
function mfEncriptMD5($cadena)
{
     $hoy = str_replace(" ", "", gmdate('Y-m-d h:i:s ', time()));
     return  substr(md5($cadena . $hoy), 0, 10);
}
function mfCreateXmlMultiObject($response, $details, $status, $data, $nameData = "data", $multidata = false)
{
     // $data = json_decode(data);
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
                         $productN = $nameData . "-" . $count;
                         $xmlArray["DATA"][$productN] = [];
                         if (is_array($value1) || is_object($value1)) {
                              foreach ($value1 as $key2 => $value2) { //primera fase completa
                                   if (is_array($value2) || is_object($value2)) {
                                        $xmlArray["DATA"][$productN][$key2] = [];
                                        foreach ($value2 as $key3 => $value3) {
                                             if (is_array($value3) || is_object($value3)) {
                                                  $xmlArray["DATA"][$productN][$key2][$key3] = [];
                                                  foreach ($value3 as $key4 => $value4) {
                                                       if (is_array($value4) || is_object($value4)) {
                                                            foreach ($value4 as $key5 => $value5) {
                                                                 $xmlArray["DATA"][$productN][$key2][$key3][$key4] = [];
                                                                 if (is_array($value5) || is_object($value5)) {
                                                                 } else if (is_string($value5)  && $value5 !== "") {
                                                                      array_push($xmlArray["DATA"][$productN][$key2][$key3][$key4], [$key5 => $value5]);
                                                                 }
                                                            }
                                                       } else if (is_string($value4)  && $value4 !== "") {
                                                            array_push($xmlArray["DATA"][$productN][$key2][$key3], [$key4 => $value4]);
                                                       }
                                                  }
                                             } else if (is_string($value3)  && $value3 !== "") {
                                                  array_push($xmlArray["DATA"][$productN][$key2], [$key3 => $value3]);
                                             }
                                        }
                                   } else if (is_string($value2)  && $value2 !== "") {
                                        array_push($xmlArray["DATA"][$productN], [$key2 => $value2]);
                                   }
                              }
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

     header("Content-Type: application/$typeApp; charset=utf-8");
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
function mfAddNewFieldsMetadata($dataCurrent, $fields)
{
     $metadata = [];
     foreach ($fields as $value) {
          array_push($metadata, ["key" => $value, "value" => $dataCurrent[$value]]);
     }
     return $metadata;
}
//funciones que retorna respuesta
function mfUpdateMetadataMaterial($id_cliente, $data)
{
     for ($i = 0; $i < count($data); $i++) {
          $dato = $data[$i];
          global $wpdb;
          $table = $wpdb->base_prefix . 'postmeta';
          $sql = "UPDATE $table SET  meta_value = %s where post_id=$id_cliente AND meta_key=%s";
          $result = $wpdb->query($wpdb->prepare($sql, $dato["value"], $dato["key"]));
          $wpdb->flush();
          if (!$result) new Error("Error en la actualizacion de  datos");
     }
}
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
function mfGetIdMaterialWithSku($sku)
{
     $woo = max_functions_getWoocommerce();
     $findMaterial = $woo->get("products", ["sku" => $sku]);
     return $findMaterial[0]->id;
}
function mfUpdateMaterialWithSku($sku, $dataUpdated)
{
     $woo = max_functions_getWoocommerce();
     $findMaterial = $woo->get("products", ["sku" => $sku]);
     $response = $woo->put("products/" . $findMaterial[0]->id, $dataUpdated);
     return $response;
}
function mfCreateMaterialWoo($material)
{

     $woo = max_functions_getWoocommerce();
     $weight = number_format($material["peso"], 2, ".", "");
     $sku = $material["id_mat"];
     $dataSend = [
          'name' => $material["nomb"],
          'sku' => $sku,
          'weight' => $weight,
          "meta_data" => [],
     ];
     if ($material["und"] !== "kg") {
          $dataSend["meta_data"] = [
               [
                    "key" => "und",
                    "value" => $material['und'],
               ],
               [
                    "key" => "und_value",
                    "value" =>  $weight,
               ]
          ];
     }
     $id_soc = $material["id_soc"];
     if ($id_soc == "MAX") {
          $newfields = ["id_soc", "cent", "alm", "jprod"];
          foreach (mfAddNewFieldsMetadata($material, $newfields) as  $value) {
               array_push($dataSend["meta_data"], $value);
          }
          try {
               $response = $woo->post('products', $dataSend); //devuelve un objeto
               foreach ($dataSend["meta_data"] as  $mt) {
                    $response->{$mt["key"]} = $mt["value"];
               }
               $response->peso = $weight;
               if ($response->id !== null) {
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
     } else {
          return [
               "value" => 0,
               "message" => "El id_soc: $id_soc no coincide con nuestra sociedad",
          ];
     }
}
function mfUpdateMaterialWoo($sku, $material)
{
     try {

          $weight = number_format($material["peso"], 2, ".", "");
          // $sku = $material["sku"];
          $id_cliente = mfGetIdMaterialWithSku($sku);
          $metadata = [];
          $newfields = ["id_soc", "cent", "alm", "jprod", "und_value", "und"];
          foreach (mfAddNewFieldsMetadata($material, $newfields) as  $value) {
               array_push($metadata, $value);
          }
          mfUpdateMetadataMaterial($id_cliente, $metadata);
          $dataUpdated = [
               'name' => $material["nomb"],
               'weight' => $weight,
               "manage_stock" => true,
               "stock_quantity" => $material["stck"],
          ];
          if ($material["stck"] == 0) {
               $dataUpdated["manage_stock"] = false;
          }
          //updated
          $response = mfUpdateMaterialWithSku($sku, $dataUpdated);
          foreach ($metadata as  $value) {
               $response->{$value["key"]} = $value["value"];
          }
          $response->peso = $weight;
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
     $id_cli = mfEncriptMD5($email);
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
                    "value" => $id_cli
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
          $response->id_cli = $id_cli; //le devolvemos el id_cli 
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
     $sql = "SELECT user_id FROM $table WHERE meta_key = 'cd_cli' and meta_value= %d LIMIT 1";
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
function mfGetMaterial($params)
{
     $data = mfXmlToArray("php://input"); //recogo data xml
     return  mfValidationGeneralAuth($data, $params, function ($data, $params) {
          $after = $params->get_param("after");
          $before = $params->get_param("before");
          if ($after !== null) {
               try {
                    $woo = max_functions_getWoocommerce();
                    $filters = [
                         "order" => "asc",
                         "orderby" => "date",
                         "after" =>  str_replace(" ", "T", $after),
                         "per_page" => 100
                    ];
                    if ($params->get_param("before") !== null) {
                         $filters["before"] = str_replace(" ", "T", $before);
                    }
                    $response = $woo->get("products", $filters);
                    foreach ($response as $material) {
                         foreach ($material->meta_data  as  $mt) {
                              $material->{$mt->key} = $mt->value;
                         }
                    }
                    return mfSendResponse(1, "Todo correcto", 200, $response, "material", true);
               } catch (\Throwable $th) {
                    $error = ["ERROR" => "The date format is not valid example correct: 2020-07-29 10:01:60"];
                    return mfSendResponse(0, "Ocurrio un Error", 404, $error);
               }
          } else {
               $error = ["ERROR" => "Please send the parameters"];
               return mfSendResponse(0, "Ocurrio un Error", 404, $error);
          }
     }, ["security" => "required"]);
}
function mfGetClientWoo($after)
{
     global $wpdb;
     $clients = [];
     $table = $wpdb->base_prefix . 'users';
     $sql = "SELECT id FROM $table WHERE user_registered >= %s ";
     $resultIds = $wpdb->get_results($wpdb->prepare($sql, $after));
     if (empty($resultIds)) {
          return [
               "value" => 0,
               "message" => "There are no registered customers as of this date: $after",
          ];
     } else {
          for ($i = 0; $i < count($resultIds); $i++) {
               $idClient = $resultIds[$i]->id;
               $woo = max_functions_getWoocommerce();
               $currentClient = $woo->get("customers/$idClient");
               foreach ($currentClient->meta_data as $value) {
                    $currentClient->{$value->key} = $value->value;
               }
               array_push($clients, $currentClient);
          }
          return $clients;
     }
}
function mfGetClients($params)
{
     $data = mfXmlToArray("php://input"); //recogo data xml
     return  mfValidationGeneralAuth($data, $params, function ($data, $params) {
          try {
               $after = $params->get_param("after");
               return mfSendResponse(1, "Todo correcto", 200, mfGetClientWoo($after), "client", true);
          } catch (\Throwable $th) {
               $error = ["ERROR" => "The date format is not valid example correct: 2020-07-29 10:01:60"];
               return mfSendResponse(3, "Ocurrio un error", 200, $error);
          }
     }, ["security" => "required"]);
}
function mfCreateMaterial($params)
{
     $data = mfXmlToArray("php://input"); //recogo data xml
     return  mfValidationGeneralAuth($data, $params, function ($data) {
          $material = $data["material"];
          $validateMaterial = mfValidateMaterialFields($material); //validacion de security
          if ($validateMaterial["validate"]) {
               $created = mfCreateMaterialWoo($material);
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
          $material = $data["material"];
          $validateMaterial = mfValidateMaterialFields($material, true); //validacion de security
          if ($validateMaterial["validate"]) {
               $updated = mfUpdateMaterialWoo($sku, $material);
               return mfSendResponse($updated["value"], $updated["message"], 200, $updated["data"], "material");
               // return mfSendResponse(1, "Todo Correcto", 200, $material);
          } else {
               return mfSendResponse(0, $validateMaterial["message"], 400);
          }
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
//get materials
// http://maxco.punkuhr.test/wp-json/max_functions/v1/getmaterials?after=2020-09-10 22:00:22&before=2020-09-1409:08:59
add_action("rest_api_init", function () {
     register_rest_route("max_functions/v1", "/getmaterials", array(
          "methods" => "POST",
          "callback" => "mfGetMaterial",
          'args'            => array(),
     ));
});
//get clients for date
// http://maxco.punkuhr.test/wp-json/max_functions/v1/clients?after=2020-09-10 22:00:22&before=2020-09-1409:08:59
add_action("rest_api_init", function () {
     register_rest_route("max_functions/v1", "/getclients", array(
          "methods" => "POST",
          "callback" => "mfGetClients",
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
     register_rest_route("max_functions/v1", "/clients/(?P<cd_cli>[a-zA-Z0-9-]+)", array(
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
function mfValidateMaterialFields($material, $update = false)
{
     $validations = [
          'id_soc'                  => 'required|max:4',
          'id_mat'                  => 'required|max:12',
          'cent'                  => 'required|max:4',
          'alm'                  => 'required|max:4',
          'nomb'              => 'required|max:40',
          'und'              => 'required|max:3',
          'peso'              => 'required|max:6',
          'jprod'              => 'required|max:20',
     ];
     if ($update) {
          $validations["stck"] = 'required|max:5';
          $validations["id_mat"] = '';
     }

     return mfUtilityValidator($material, $validations);
}
function mfValidateClientFields($client)
{
     return mfUtilityValidator($client, [
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

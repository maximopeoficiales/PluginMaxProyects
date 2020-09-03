<?php

use Automattic\WooCommerce\Client;
use Dompdf\Dompdf;
use Dompdf\Options;

function getDataConfig()
{
     $args = array(
          'post_type' => 'max_functions_config',
          'posts_per_page' => 1,
          'orderby' => 'ID',
          'order' => 'ASC'
     );

     $datos = new WP_Query($args);
     $data = array();
     while ($datos->have_posts()) : $datos->the_post();
          $data["api_key_google_maps"] = get_field("api_key_google_maps");
          $data["consumer_key"] = get_field("consumer_key");
          $data["consumer_secret"] = get_field("consumer_secret");
          $data["latitud_tienda"] = get_field("latitud_tienda");
          $data["longitud_tienda"] = get_field("longitud_tienda");
     endwhile;
     return $data;
}
function max_functions_getWoocommerce()
{
     $credenciales = getDataConfig();
     $woocommerce = new Client(
          get_site_url(),
          $credenciales["consumer_key"],
          $credenciales["consumer_secret"],
          [
               'version' => 'wc/v3',
          ]
     );
     return $woocommerce;
}
function max_functions_get_current_user()
{

     //Los filtros predeterminados usan esto para determinar el usuario actual a partir de las cookies de la solicitud, si están disponibles.
     $user_id = apply_filters('determine_current_user', false);
     /* $user_data = get_userdata($user_id); */

     return [
          'user_id' => $user_id,

     ];
}
add_action("rest_api_init", function () {
     register_rest_route("max_functions/v1", "/get_current_user", array(
          "methods" => "GET",
          "callback" => "max_functions_get_current_user",
          'args'            => array(),
     ));
});

// template ojito del pedido 
add_action('admin_footer', 'order_preview_template');
function order_preview_template()
{
     $data = getDataConfig();
     if ($data["api_key_google_maps"] != "") {
?>
          <style>
               @media(max-width: 768px) {
                    .button_margin {
                         margin-top: 5px !important;
                    }
               }
          </style>

          <script type="text/template" id="tmpl-wc-modal-view-order">
               <div class="wc-backbone-modal wc-order-preview">
				<div class="wc-backbone-modal-content">
					<section class="wc-backbone-modal-main" role="main">
						<header class="wc-backbone-modal-header">
							<mark class="order-status status-{{ data.status }}"><span>{{ data.status_name }}</span></mark>
							<?php /* translators: %s: order ID */ ?>
							<h1><?php echo esc_html(sprintf(__('Order #%s', 'woocommerce'), '{{ data.order_number }}')); ?></h1>
							<button class="modal-close modal-close-link dashicons dashicons-no-alt">
								<span class="screen-reader-text"><?php esc_html_e('Close modal panel', 'woocommerce'); ?></span>
							</button>
						</header>
						<article>
							<?php do_action('woocommerce_admin_order_preview_start'); ?>

							<div class="wc-order-preview-addresses">
								<div class="wc-order-preview-address">
									<h2><?php esc_html_e('Billing details', 'woocommerce'); ?></h2>
									{{{ data.formatted_billing_address }}}

									<# if ( data.data.billing.email ) { #>
										<br>
										<strong><?php esc_html_e('Email', 'woocommerce'); ?></strong>
										<a href="mailto:{{ data.data.billing.email }}">{{ data.data.billing.email }}</a>
									<# } #>

									<# if ( data.data.billing.phone ) { #>
										<strong><?php esc_html_e('Phone', 'woocommerce'); ?></strong>
										<a href="tel:{{ data.data.billing.phone }}">{{ data.data.billing.phone }}</a>
									<# } #>

									<# if ( data.payment_via ) { #>
										<br>
										<strong><?php esc_html_e('Payment via', 'woocommerce'); ?></strong>
										{{{ data.payment_via }}}
									<# } #>
								</div>
								<# if ( data.needs_shipping ) { #>
									<div class="wc-order-preview-address">
										<h2><?php esc_html_e('Shipping details', 'woocommerce'); ?></h2>
										<# if ( data.ship_to_billing ) { #>
											{{{ data.formatted_billing_address }}}
										<# } else { #>
											<a href="{{ data.shipping_address_map_url }}" target="_blank">{{{ data.formatted_shipping_address }}}</a>
										<# } #>

										<# if ( data.shipping_via ) { #>
											<strong><?php esc_html_e('Shipping method', 'woocommerce'); ?></strong>
											{{ data.shipping_via }}
										<# } #>
									</div>
								<# } #>

								<# if ( data.data.customer_note ) { #>
									<div class="wc-order-preview-note">
										<strong><?php esc_html_e('Note', 'woocommerce'); ?></strong>
										{{ data.data.customer_note }}
									</div>
								<# } #>
							</div>

                                   {{{ data.item_html }}}
                                   <table cellspacing=" 0 " class="wc-order-preview-table">
                                   <tr class="wc-order-preview-table__item wc-order-preview-table__item - 3">
                                        <td class="wc- order-preview-table__column - product">
                                             <div class="wc-order-item-sku">Envio</div>
                                        </td>
                                        <td class="wc-order-preview- table__column - cantidad"></td>
                                        <td class="wc-order-preview-table__column - total">
                                             <span class="woocommerce-Price-amount amount">
                                                  <span class="woocommerce-Price-currencySymbol"></span>{{data.data.shipping_total}}
                                             </span>
                                        </td>
                                   </tr>
                                   <tr class="wc-order-preview-table__item wc-order-preview-table__item - 3">
                                        <td class="wc- order-preview-table__column - product">
                                             <div class="wc-order-item-sku"></div>
                                        </td>
                                        <td class="wc-order-preview- table__column - cantidad"></td>
                                        <td class="wc-order-preview-table__column - total">
                                             <span class="woocommerce-Price-amount amount">
                                                  <span class="woocommerce-Price-currencySymbol"></span>{{data.data.total}}
                                             </span>
                                        </td>
                                   </tr>
                                   </table>
							<!-- se agrego validacion para cuando no hayan datos de mapa -->			
                                   <?php do_action('woocommerce_admin_order_preview_end'); ?>
                                   <?php if ($data["api_key_google_maps"] != "") { ?>
                                       
                                   <# if( data.data.meta_data.length > 1 ) { #>
                                        <# if(data.data.meta_data[2].key=="ce_latitud"){#>
							<img src="https://maps.googleapis.com/maps/api/staticmap?center={{data.data.meta_data[2].value}},{{data.data.meta_data[3].value}}&zoom=16&size=600x500&markers=color:red%7C{{data.data.meta_data[2].value}},{{data.data.meta_data[3].value}}&key=<?php echo $data["api_key_google_maps"]; ?>" width="100%" height="400px" alt="imagen_mapa">
                                   <# } #>      
                                   <# } #>
                                   <?php } ?>
						</article>
						<footer>
                                   <form action="/" method="POST">
                                        <div class="inner">
                                             {{{ data.actions_html }}}
                                             <a class="button button-primary button-large" aria-label="<?php esc_attr_e('Edit this order', 'woocommerce'); ?>" href="<?php echo esc_url(admin_url('post.php?action=edit')); ?>&post={{ data.data.id }}"><?php esc_html_e('Edit', 'woocommerce'); ?></a>
                                                       <!--    <button class="button button-primary button-large" type="button" role="button" aria-label="Imprimir" onclick="imprimirDiv('#datos_modal');">Imprimir</button> -->
                                                       <button class="button button-primary button-large" type="submit" role="button" aria-label="Imprimir">Imprimir</button>
                                                       <input type="hidden" name="me_post_pdf" value="submitted">
                                                       <input type="hidden" name="id_order" value="{{data.data.id}}">
                                        </div>
                                   </form>	
						</footer>
					</section>
				</div>
			</div>
			<div class="wc-backbone-modal-backdrop modal-close"></div>
			
		</script>
          <!-- fin de modficacion -->
<?php
     }
}
// add the action 
add_action('woocommerce_admin_order_data_after_shipping_address', 'action_woocommerce_admin_order_data_after_shipping_address', 10, 3);
?>
<?php
// define the woocommerce_admin_order_data_after_shipping_address callback 
function action_woocommerce_admin_order_data_after_shipping_address()
{
     try {
          $data = getDataConfig();
          $woocommerce = max_functions_getWoocommerce();
          if ($data["api_key_google_maps"] != "") {
?>
               <?php if (!$_GET["post_type"]) {
                    $id_order = $_GET["post"];
                    $order = $woocommerce->get("orders/$id_order");

                    $coordenadas = array();
                    foreach ($order->meta_data as $e) {
                         if ($e->key == "ce_latitud") {
                              array_push($coordenadas, $e);
                         }
                         if ($e->key == "ce_longitud") {
                              array_push($coordenadas, $e);
                         }
                    }
                    if (isset($coordenadas) && $coordenadas[0]->value !== null) {
                         $latitud = $coordenadas[0]->value;
                         $longitud = $coordenadas[1]->value;
                         $link_google = "https://maps.google.com/?q=" . $latitud . "," . $longitud;


               ?>
                         <!-- CAMPO url Gmaps -->
                         <p id="url_mapa"><a href="<?= $link_google ?>"><?= $link_google ?></a></p>
                    <?php     } ?>
                    <!-- campo url gmaps -->
                    <!-- bloque de codigo -->
                    <!-- estilos para el modal -->
                    <?php if ($order->shipping->address_1 !== "") {
                         # code...
                    ?>
                         <style>
                              * {
                                   margin: 0;
                                   padding: 0;
                                   box-sizing: border-box;
                              }

                              .flex {
                                   width: 100%;
                                   height: 100%;
                                   display: flex;
                                   justify-content: space-between;
                                   align-items: center;
                              }

                              .textos {
                                   padding: 300px;
                                   color: #fff;
                                   text-align: center;
                              }

                              .modal {
                                   display: none;
                                   position: fixed;
                                   z-index: 1;
                                   overflow: auto;
                                   left: 0;
                                   top: 0;
                                   width: 100%;
                                   height: 100%;
                                   background: rgba(0, 0, 0, 0.452);
                              }

                              .contenido-modal {
                                   position: relative;
                                   background-color: #fefefe;
                                   margin: auto;
                                   width: 60%;
                                   box-shadow: 0 0 6px 0 rgba(0, 0, 0, .4);
                                   animation-name: modal;
                                   animation-duration: 1s;
                              }

                              @keyframes modal {
                                   from {
                                        top: -330px;
                                        opacity: 0;
                                   }

                                   to {
                                        top: 0;
                                        opacity: 1;
                                   }
                              }

                              .modal-header,
                              .footer {
                                   padding: 8px 16px;
                                   background: #34495e;
                                   color: #f2f2f2;
                              }

                              .modal-body {
                                   padding: 11px 16px;

                              }

                              span {
                                   font-size: 12px;
                              }

                              @media screen and (max-width:768px) {
                                   .contenido-modal {
                                        width: 90%;
                                   }


                              }
                         </style>
                         <!-- fin de estilos para el modal -->
                         <a href="#" id="abrir" class="button button-primary right">Imprimir Resumen</a>
                         <!-- modal de impresion -->

                         <div id="miModal" class="modal">
                              <div class="flex" id="flex">
                                   <div class="contenido-modal">
                                        <div id="wc-backbone-modal-dialog" id="miModal">
                                             <div class="wc-backbone-modal wc-order-preview">
                                                  <div class="wc-backbone-modal-content" tabindex="0">
                                                       <section class="wc-backbone-modal-main" role="main" id="datos_modal">
                                                            <header class="wc-backbone-modal-header">
                                                                 <mark class="order-status status-processing"><span><?= strtoupper($order->status) ?></span></mark>
                                                                 <h1 id="m_n_pedido">Pedido <?= $id_order ?></h1>
                                                                 <button class="modal-close modal-close-link dashicons dashicons-no-alt" id="close">
                                                                      <!-- <span class="screen-reader-text">Cerrar ventana modal</span> -->
                                                                 </button>
                                                            </header>
                                                            <article style="max-height: 481.5px;">

                                                                 <div class="wc-order-preview-addresses">
                                                                      <div class="wc-order-preview-address">
                                                                           <h2>Detalles del Cliente</h2>
                                                                           <strong>Nombre</strong>
                                                                           <span><?= $order->billing->first_name ?></span>
                                                                           <strong>Correo electrónico</strong>
                                                                           <a href="<?= $order->billing->email ?>"><?= $order->billing->email ?></a>
                                                                           <strong>Teléfono</strong>
                                                                           <a href="tel:<?= $order->billing->phone ?>"><?= $order->billing->phone ?></a><br>
                                                                           <strong>Pago mediante</strong>
                                                                           <span><?= strtoupper($order->payment_method_title) ?></span>

                                                                      </div>
                                                                      <div class="wc-order-preview-address">
                                                                           <h2>Detalles de envío</h2>
                                                                           <?php if (isset($coordenadas) && $coordenadas[0]->value !== null) { ?>
                                                                                <strong>Link de Google Maps</strong>
                                                                                <a href="<?= $link_google ?>" target="_blank"><?= $link_google ?></a>
                                                                           <?php
                                                                           } ?>
                                                                           <div style="margin-top: 5px;">
                                                                                <strong>Método de envío</strong>
                                                                                Se aplicarán costos de envío según tu dirección en la siguiente página.
                                                                           </div>
                                                                           <br>
                                                                           <strong>Nota Cliente:</strong>
                                                                           <span><?= $order->customer_note ?></span>
                                                                      </div>
                                                                 </div>

                                                                 <div id="m_tabla_datos">
                                                                      <div class="wc-order-preview-table-wrapper">
                                                                           <table cellspacing=" 0 " class="wc-order-preview-table">
                                                                                <thead>
                                                                                     <tr>
                                                                                          <th class="wc-order -preview-table__column - product">Producto</th>
                                                                                          <th class="wc-order-preview-table__column - amount">Cantidad</th>
                                                                                          <th class="wc-order-preview-table__column-- total">Total</th>
                                                                                     </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                     <?php
                                                                                     foreach ($order->line_items as $item) {
                                                                                     ?>
                                                                                          <tr class="wc-order-preview-table__item wc-order-preview-table__item - 3">
                                                                                               <td class="wc- order-preview-table__column - product">
                                                                                                    <?= $item->name  ?>
                                                                                                    <div class="wc-order-item-sku"></div>
                                                                                               </td>
                                                                                               <td class="wc-order-preview- table__column - cantidad"><?= $item->quantity ?></td>
                                                                                               <td class="wc-order-preview-table__column - total">
                                                                                                    <span class="woocommerce-Price-amount amount">
                                                                                                         <span class="woocommerce-Price-currencySymbol"> <?= $order->currency_symbol ?></span> <?= number_format($item->price * $item->quantity, 2, ".", "")  ?>
                                                                                                    </span>
                                                                                               </td>
                                                                                          </tr>
                                                                                     <?php } ?>
                                                                                     <?php if (isset($order->shipping_lines)) {
                                                                                          foreach ($order->shipping_lines as  $envio) {
                                                                                     ?>
                                                                                               <tr class="wc-order-preview-table__item wc-order-preview-table__item - 3">
                                                                                                    <td class="wc- order-preview-table__column - product">
                                                                                                         Envio
                                                                                                         <div class="wc-order-item-sku"></div>
                                                                                                    </td>
                                                                                                    <td class="wc-order-preview- table__column - cantidad"></td>
                                                                                                    <td class="wc-order-preview-table__column - total">
                                                                                                         <span class="woocommerce-Price-amount amount">
                                                                                                              <span class="woocommerce-Price-currencySymbol"> <?= $order->currency_symbol ?></span> <?= number_format($envio->total, 2, ".", "")  ?>
                                                                                                         </span>
                                                                                                    </td>
                                                                                               </tr>
                                                                                     <?php
                                                                                          }
                                                                                     } ?>
                                                                                     <tr class="wc-order-preview-table__item wc-order-preview-table__item - 3">
                                                                                          <td class="wc- order-preview-table__column - product">
                                                                                               <div class="wc-order-item-sku"></div>
                                                                                          </td>
                                                                                          <td class="wc-order-preview- table__column - cantidad"></td>
                                                                                          <td class="wc-order-preview-table__column - total">
                                                                                               <span class="woocommerce-Price-amount amount">
                                                                                                    <span class="woocommerce-Price-currencySymbol"> <?= $order->currency_symbol ?></span> <?= number_format($order->total, 2, ".", "")  ?>
                                                                                               </span>
                                                                                          </td>
                                                                                     </tr>
                                                                                </tbody>
                                                                           </table>
                                                                      </div>
                                                                 </div>
                                                                 <?php if (isset($coordenadas) && $coordenadas[0]->value !== null) { ?>
                                                                      <img src="https://maps.googleapis.com/maps/api/staticmap?center=<?= $latitud ?>,<?= $longitud ?>&zoom=16&size=600x400&markers=color:red%7C<?= $latitud ?>,<?= $longitud ?>&key=<?= $data["api_key_google_maps"] ?>" width="100%" height="400px" />
                                                                 <?php
                                                                 } ?>
                                                            </article>
                                                            <footer>
                                                                 <form action="/" method="POST">
                                                                      <div class="inner">
                                                                           <!--    <button class="button button-primary button-large" type="button" role="button" aria-label="Imprimir" onclick="imprimirDiv('#datos_modal');">Imprimir</button> -->
                                                                           <button class="button button-primary button-large" type="submit" role="button" aria-label="Imprimir">Imprimir</button>
                                                                           <input type="hidden" name="me_post_pdf" value="submitted">
                                                                           <input type="hidden" name="id_order" value="<?= $id_order ?>">
                                                                      </div>
                                                                 </form>
                                                            </footer>
                                                       </section>
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         </div>
                         <!-- fin de modal -->
                         <script>
                              let modal = document.getElementById('miModal');
                              let flex = document.getElementById('flex');
                              let abrir = document.getElementById('abrir');
                              let cerrar = document.getElementById('close');

                              abrir.addEventListener('click', function() {
                                   modal.style.display = 'block';
                                   document.querySelector("#wc-backbone-modal-dialog").style.display = "none";

                                   document.querySelector("#wc-backbone-modal-dialog").style.display = "";




                                   document.querySelector(".woocommerce-layout__header").style.display = 'none';
                                   /* if (isMobile()) { */
                                   document.querySelector("#wpadminbar").style.display = 'none';
                                   /* } */
                              });

                              cerrar.addEventListener('click', function(e) {
                                   e.preventDefault();
                                   modal.style.display = 'none';
                                   document.querySelector(".woocommerce-layout__header").style.display = '';
                                   /* if (isMobile()) { */
                                   document.querySelector("#wpadminbar").style.display = '';
                                   /* 	} */
                              });

                              window.addEventListener('click', function(e) {
                                   if (e.target == flex) {
                                        modal.style.display = 'none';
                                        document.querySelector(".woocommerce-layout__header").style.display = '';

                                   }
                              });
                         </script>
                         <!-- fin de bloque de codigo -->

               <?php }
               } ?>
     <?php
          }
     } catch (\Throwable $th) {
          //throw $th;
     }
};
function my_custom_js()
{

     if (shortcode_exists("woocommerce_checkout")) { /* detecto si es el checkout */
          $data = getDataConfig();
          if ($data["latitud_tienda"] != "") {
               wp_enqueue_script(
                    'custom_script',
                    esc_url(plugins_url('/js/myscripts.js', dirname(__FILE__)))
               );
               $coordenadas = array(
                    'latitud_tienda' => $data["latitud_tienda"],
                    'longitud_tienda' => $data["longitud_tienda"]
               );
               $datos = array(
                    'l10n_print_after' => 'data_coodenadas = ' . json_encode($coordenadas) . ';'
               );
               wp_localize_script('custom_script', 'data_coodenadas', $datos);
          }
     }
}
function my_custom_head_js()
{
     $data = getDataConfig();
     if (shortcode_exists("woocommerce_checkout")) {
          if ($data["api_key_google_maps"] != "") {
               wp_enqueue_script(
                    'google_maps_script',
                    "https://maps.googleapis.com/maps/api/js?key=" . $data["api_key_google_maps"]
               );
               wp_enqueue_script(
                    'font_awesome_js',
                    "https://kit.fontawesome.com/b5b7f00aae.js"
               );
          }
     }
}

add_action('wp_footer', 'my_custom_js');
add_action('wp_head', 'my_custom_head_js');
add_action('woocommerce_webhook_delivery', 'max_function_webhook_custom', 1, 5);
function max_function_webhook_custom($http_args, $response, $duration, $arg, $id)
{
     /* get_site_url(); */
     function arrayToXml($array, &$xml)
     {
          foreach ($array as $key => $value) {
               if (is_array($value)) {
                    if (is_int($key)) {
                         $key = "e";
                    }
                    $label = $xml->addChild($key);
                    arrayToXml($value, $label);
               } else {
                    $xml->addChild($key, $value);
               }
          }
          return $xml;
     }
     function getUrlWebhook($id_wb)
     {
          $woocommerce = max_functions_getWoocommerce();
          $data = $woocommerce->get("webhooks/$id_wb");
          return $data->delivery_url;
     }
     function execWebHook($data, $urlSend)
     {
          $array = json_decode($data, true);
          $xml = new SimpleXMLElement('<root/>');
          $newXml = arrayToXml($array, $xml);
          $curl = curl_init();
          curl_setopt_array($curl, array(
               CURLOPT_URL => $urlSend,
               CURLOPT_RETURNTRANSFER => true,
               CURLOPT_ENCODING => "",
               CURLOPT_MAXREDIRS => 10,
               CURLOPT_TIMEOUT => 0,
               CURLOPT_FOLLOWLOCATION => true,
               CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
               CURLOPT_CUSTOMREQUEST => "POST",
               CURLOPT_POSTFIELDS => $newXml->asXML(),
               CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/xml"
               ),
          ));
          $response = curl_exec($curl);
          curl_close($curl);
          echo $response;
     }
     $idWebHook = $http_args['headers']['X-WC-Webhook-ID'];
     $urlSend = getUrlWebhook($idWebHook);
     $data = $http_args["body"];
     execWebHook($data, $urlSend);
}
function action_woocommerce_after_checkout_billing_form($wccs_custom_checkout_field_pro)
{     ?>
     <div class="btn-group text-center" style="text-align:center; margin-bottom:10px;  margin-top:10px;display: flex;justify-content:center  !important;align-items:center !important;" role="group">
          <button type="button" class="btn btn-secondary mr-2" id="miubicacion"><i class="fas fa-map-marker-alt fa-fw"></i>Mi Ubicacion</button>
          <button type="button" class="btn btn-secondary ml-2" id="buscar"><i class="fas fa-search-location fa-fw"></i>Buscar</button>
     </div>
     <div style="width: 100%; height: 480px" id="map-canvas"></div>
<?php
};
add_action('woocommerce_after_checkout_billing_form', 'action_woocommerce_after_checkout_billing_form', 10, 1);

function me_post_pdf()
{
     if (isset($_POST['me_post_pdf']) && isset($_POST['id_order'])) {
          $options = new Options();
          $options->set('isHtml5ParserEnabled', true);
          $options->set('isRemoteEnabled', true);
          //obtengo api key
          $data = getDataConfig();
          $api_key_google = "";
          if ($data["api_key_google_maps"] !== "") {
               $api_key_google = $data["api_key_google_maps"];
          }
          //me conecto con woocommerce
          $id_order = $_POST['id_order'];
          $woo = max_functions_getWoocommerce();
          $order = $woo->get("orders/$id_order");

          // instantiate and use the dompdf class
          $dompdf = new Dompdf($options);
          //como lo incluyo el template order.php puede usar la data
          ob_start();
          include  __DIR__ . "/templates_pdf/order.php";
          $html = ob_get_clean();
          $dompdf->loadHtml($html);
          $dompdf->setPaper('A4', 'potrait');
          // Render the HTML as PDF
          $dompdf->render();
          // Output the generated PDF to Browser
          return $dompdf->stream("order_$id_order");
     }
}

add_action('init', 'me_post_pdf');

/* include __DIR__ . "/endpoints.php"; */

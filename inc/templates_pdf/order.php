<!doctype html>
<html lang="es">

<head>
     <!-- Required meta tags -->
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

     <!-- Bootstrap CSS -->
     <link rel="stylesheet" href="<?= __DIR__ ?>/css/bootstrap.min.css">
     <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
     <title>Orden</title>
</head>
<style>
     html {
          font-family: 'Montserrat', sans-serif;
     }

     @page {
          margin-top: 5px;
          margin-bottom: 5px;
     }

     body {
          margin: 0px;
          margin-top: 5px;
          margin-bottom: 5px;
     }
</style>
<?php $coordenadas = array();
foreach ($order->meta_data as $e) {
     if ($e->key == "ce_latitud") {
          array_push($coordenadas, $e);
     }
     if ($e->key == "ce_longitud") {
          array_push($coordenadas, $e);
     }
} ?>

<body>
     <div class="my-1" style="background-color: black; ">
          <h1 class="text-white text-center h5">Pedido N° <?= $order->id ?></h1>
     </div>

     <?php if ($order->shipping->address_1 !== "") {
     ?>
          <!-- ------------------ -->
          <h2 class="font-weight-bold h6 my-2">Detalles del Envío</h2>
          <div class="mb-2">
               <strong class="font-weight-bold small text-left">Direccion de Envio:</strong>
               <span class="small float-right""><?= $order->shipping->address_1 ?></span>
          </div>
          <div class=" mb-2">
                    <strong class="font-weight-bold small text-left">Nota:</strong>
                    <span class="small float-right"><?= $order->customer_note ?></span>
          </div>

          <hr>

          <!-- -------------------------- -->
          <h2 class="font-weight-bold h6 my-1">Detalles del Cliente</h2>
          <div class="mb-2">
               <strong class="font-weight-bold small text-left">Nombre:</strong>
               <span class="small float-right"><?php echo $order->billing->first_name . " " . $order->billing->last_name;  ?></span>
          </div>
          <div class="mb-2">
               <strong class="font-weight-bold small text-left">Correo Electrónico:</strong>
               <span class="small float-right"><a href="mailto:<?= $order->billing->email ?>"><?= $order->billing->email ?></a></span>

          </div>

          <div class="mb-2">
               <strong class="font-weight-bold small text-left">Telefono:</strong>
               <span class="small float-right"><a href="tel:<?= $order->billing->phone ?>"><?= $order->billing->phone ?></a></span>
          </div>

          <div class="mb-2">
               <strong class="font-weight-bold small text-left">Pago Mediante:</strong>
               <span class="small float-right"><?= $order->payment_method_title ?></span>
          </div>

          <hr>
          <!-- ------------------------ -->
          <table class="table table-bordered table-sm">
               <thead>
                    <tr>
                         <th>Producto</th>
                         <th class="text-right">Cantidad</th>
                         <th class="text-right">Total</th>
                    </tr>
               </thead>
               <tbody>
                    <?php
                    foreach ($order->line_items as $item) {
                    ?>
                         <tr>
                              <td scope="row"><?= $item->name  ?></td>
                              <td class="text-right"><?= $item->quantity ?></td>
                              <td class="text-right"><?= $order->currency_symbol ?> <?= number_format($item->price * $item->quantity, 2, ".", "")  ?></td>
                         </tr>
                    <?php } ?>
                    <?php if (isset($order->shipping_lines)) {
                         foreach ($order->shipping_lines as $envio) {
                    ?>
                              <tr>
                                   <td colspan="2">Envio</td>
                                   <td class="text-right"><?= $order->currency_symbol ?> <?= number_format($envio->total, 2, ".", "")  ?></td>
                              </tr>

                    <?php }
                    } ?>
                    <tr>
                         <td colspan="2"></td>
                         <td class="text-right"><?= $order->currency_symbol ?> <?= number_format($order->total, 2, ".", "")  ?></td>
                    </tr>
               </tbody>
          </table>
     <?php } else {
     ?>
          <h1 class="text-center font-weight-bold">No tiene compras que mostrar</h1>
     <?php }  ?>
     <?php if (isset($coordenadas) && $coordenadas[0]->value !== null) {
          $latitud = $coordenadas[0]->value;
          $longitud = $coordenadas[1]->value;
          $link_google = "https://maps.google.com/?q=" . $latitud . "," . $longitud;
     ?>
          <a href="<?= $link_google ?>">
               <img src="https://maps.googleapis.com/maps/api/staticmap?center=<?= $latitud  ?>,<?= $longitud  ?>&amp;zoom=16&amp;size=600x400&amp;markers=color:red%7C<?= $latitud  ?>,<?= $longitud  ?>&amp;key=<?= $api_key_google  ?>" width="100%" class="img-fluid">
          </a>
     <?php } ?>


</body>

</html>
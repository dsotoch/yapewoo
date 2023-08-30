<?php
date_default_timezone_set('America/Lima');

global $wpdb;

// Crear un controlador personalizado para escuchar una URL
function mi_controlador_personalizado() {

           try {
            global $wpdb;
            // Configurar la zona horaria a América/Lima

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $postData = file_get_contents('php://input');
                // Decodificar el contenido JSON en un array asociativo
                $datas = json_decode($postData, true);
                if ($datas !== null) {
                    $title = $datas['title'];
                    $yapero = $datas['yapero'];
                    $monto = $datas['monto'];
                    $montoDouble = floatval($monto);
                    if($title!=="CONFIRMACIONDEPAGO"){
                        return false;
                    }else{
                        $query = $wpdb->prepare(
                            "SELECT pagador, venta, fecha, estado FROM {$wpdb->prefix}pago_yape WHERE fecha = %s AND estado = 'PENDIENTE' AND pagador = %s ORDER BY id DESC LIMIT 1",
                            date('Y-m-d'),
                            $yapero
                        );
                        $data = $wpdb->get_row($query);
                       if($data!=null){
                        $order = wc_get_order( $data->venta );
                        if($montoDouble==floatval($order->get_total())){
            
                            if ($order) {
                                $updated_data = array(
                                    'estado' => 'COMPLETADO'
                                );
                                
                                $where = array(
                                    'venta' => $order->get_order_number();
                                );
                                
                                $result = $wpdb->update($wpdb->prefix.'pago_yape', $updated_data, $where);
                                if($result!=false){
                                    $order->update_status( 'completed' );
                                    // Realiza otras acciones o devuélve una respuesta si es necesario
                                    $response = array(
                                    'message' => 'Estado del pedido Completado'
                                    );
                        
                                    // Devuelve una respuesta en formato JSON
                                    wp_send_json($response);
                                }else{
                                    $response = array(
                                        'message' => 'No se ha cambiado el estado en BD'
                                        );
                            
                                        // Devuelve una respuesta en formato JSON
                                        wp_send_json($response);
                                }
                               
                            }else{
                
                            // Realiza otras acciones o devuélve una respuesta si es necesario
                            $response = array(
                                'message' => 'Estado del pedido ERRONEO NO SE ENCONTRO LA ORDEN'
                                );
                    
                                // Devuelve una respuesta en formato JSON
                                wp_send_json($response);
                            }
            
                        }else{
                            $order->add_order_note($yapero . ' Error en la verificación del monto---MONTO DE COMPRA INCOMPLETO!!', true);
                        }
                       }

                    }
                }
                
            }else{
            // Si no es una solicitud POST
            $response = array(
                'error' => 'Se requiere una solicitud POST'
            );

            // Devolver una respuesta de error en formato JSON
            wp_send_json_error($response);
    

            }
           } catch (\Throwable $th) {
                wp_send_json_error($th->getMessage());
           }
}
// Agregar un punto final personalizado para el controlador
add_action('wp_ajax_nopriv_yapepayments', 'mi_controlador_personalizado');

?>

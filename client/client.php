<?php
try
    {
        //ini_set("soap.wsdl_cache_enabled", "0"); 
        $options = [
            'trace' => 1,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE
        ];
        $client = new 
            SoapClient("http://192.168.0.15/~user15/soap/shop_soap/server/carservice1.wsdl",$options);


        //var_dump($client->__getFunctions());
        if (isset($_POST['getSelectData'])) {
            $dataSelect = $client->getInfoDropDownList();
            echo $dataSelect;
        }
         if (isset($_POST['getCarsData'])) {
            $dataCars = $client->listCars();
            echo $dataCars;
        }
         if (isset($_POST['getCarInfo'])) {
            $id = $_POST['getCarInfo']['car_id'];
            $param =  json_encode(['id'=>$id]);
            $dataCars = $client->fullInfoCar($param);
            echo $dataCars;
        }
        
        if (isset($_POST['orderInfo'])) {
            $id_car = $_POST['orderInfo']['car_id'];
            $name = $_POST['orderInfo']['name'];
            $sname = $_POST['orderInfo']['name'];
            $payment_method = $_POST['orderInfo']['payment_method'];
            $params =  json_encode([
                'id_car'=>$id_car,
                'name'=>$name,
                'sname'=>$sname,
                'payment_method'=>$payment_method
            ]);
            $answ = $client->order($params);
            echo $answ;   
        }
        
        if(isset($_POST['filterParams'])) {
            $year_from = $_POST['filterParams']['year_from'];
            $year_to = $_POST['filterParams']['year_to'];
            $id_model = $_POST['filterParams']['id_model'];
            $id_brand = $_POST['filterParams']['id_brand'];
            $engine_capacity_from = $_POST['filterParams']['engine_capacity_from'];
            $engine_capacity_to = $_POST['filterParams']['engine_capacity_to'];
            $speed_from = $_POST['filterParams']['speed_from'];
            $speed_to = $_POST['filterParams']['speed_to'];
            $price_from = $_POST['filterParams']['price_from'];
            $price_to = $_POST['filterParams']['price_to'];
            $id_color = $_POST['filterParams']['color'];
            $params =  json_encode(['year_from'=>$year_from,
                'year_to'=>$year_to,
                'id_model'=>$id_model,
                'id_brand'=>$id_brand,
                'engine_capacity_from'=>$engine_capacity_from,
                'engine_capacity_to'=>$engine_capacity_to,
                'speed_from'=>$speed_from,
                'speed_to'=>$speed_to,
                'price_from'=>$price_from, 
                'price_to'=>$price_to,
                'id_color'=>$id_color]);
            $cars = $client->filter($params);
            echo $cars;
        }
    }catch(SoapFault $exception){
       //$error =  $exception;
    }catch (Exception $exception) {
       // $error =  $exception;
    }
    


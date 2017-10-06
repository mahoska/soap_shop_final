<?php

class SoapCarClass{
    
    private $pdo;
    
    public function __construct()
    {
        try{
        $this->pdo = DbConnection::getInstance()->getLink(); 
        }catch(PDOException $err){
            //file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
        }
    }

    public function __destruct()
    {
        $this->pdo = null;
    }
    
    /*
     getting information for drop-down lists
     */
    public function getInfoDropDownList()
    {
       
        try{
            if(!$this->pdo)throw new PDOException();
           $collection = [];
            $sth = $this->pdo->query("SELECT id, name FROM brands"); 
            $collection ['brands'] = $this->getFetchAccoss($sth);
            $sth = $this->pdo->query("SELECT id, name FROM models"); 
            $collection ['models'] =  $this->getFetchAccoss($sth);
            $sth = $this->pdo->query("SELECT id, name FROM colors"); 
            $collection ['colors'] =  $this->getFetchAccoss($sth);
            $collection ['sucess'] = 1;
            return json_encode($collection);
        }catch(PDOException $err){
            file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
            $err_arr = ['sucess'=>0];
            return json_encode($err_arr);
        }
    }
    
    
    /*
     get a list of cars (returns only ID, make and model)
    */
    public  function listCars() 
    {
        try{
            $sth = $this->pdo->query("SELECT cars.id, brands.name as brand,"
                . " models.name as model, cars.img"
                . " FROM brands JOIN"
                . " (cars JOIN models ON models.id = cars.id_model)"
                . " ON brands.id = cars.id_brand");
            $collection['cars'] = $this->getFetchAccoss($sth);
            $collection ['sucess'] = 1;
            return json_encode($collection);
        }catch(PDOException $err){
            file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
            $err_arr = ['sucess'=>0];
            return json_encode($err_arr);
        }
     }

     /*
   get detailed information on ID (returns complex type
    from model name, year of manufacture, engine size, color, max speed, price)
      */
    public function fullInfoCar($param)
    {
        try{
            $param = json_decode($param, true);
            $id = (int) $param['id'];
            if($id > 0){
                $sth = $this->pdo->prepare("SELECT cars.id, models.name, cars.year_of_issue, "
                         . "cars.engine_capacity, cars.max_speed,cars.price, cars.img "
                         . " FROM models JOIN cars ON models.id = cars.id_model"
                         . " WHERE cars.id = :car_id");
                 $sth->execute(['car_id' => $id]);
                 $car['info'] = $sth->fetch(\PDO::FETCH_ASSOC);
                 $sth = $this->pdo->prepare("SELECT  colors.name "
                         . "FROM colors JOIN (color_car JOIN cars ON color_car.id_car=cars.id) "
                         . "ON color_car.id_color = colors.id "
                         . "WHERE cars.id = :car_id");
                 $sth->execute(['car_id' => $id]);
                 $colors = [];
                  while($res = $sth->fetch(\PDO::FETCH_NUM)){
                     $colors[] = $res[0];
                 }
                 $car['colors'] = $colors;
                 $car ['sucess'] = 1;
                 return  json_encode($car);
            }else{
                $err_arr = ['sucess'=>0, 'error'=>ERROR_PARAMS];
                return json_encode($err_arr);
            }
        }catch(PDOException $err){
            file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
            $err_arr = ['sucess'=>0];
            return json_encode($err_arr);
        }
    }
    
    /*
     search by parameters (the same complex type as parameters
     as in the previous query. The field "year of issue" is obligatory)
     * $model, $year, $engine_capacity, $color, $max_speed, $price
     */
    public function filter($params)
    {
        try{
            $params = json_decode($params, true);
            if($params['year_from']==""){
                $err_arr = ['sucess'=>0, 'error'=>ERROR_PARAMS];
                return json_encode($err_arr);
            }
            if(count($params)>0){
            $query = "SELECT cars.id, brands.name as brand, models.name as model, cars.img"
                . " FROM brands JOIN"
                . " (cars JOIN models ON models.id = cars.id_model)"
                . " ON brands.id = cars.id_brand WHERE (year_of_issue BETWEEN :year_from AND :year_to)";
            if($params['id_model']!=""){
                 $query .= " AND cars.id_model = :id_model";
            }else{
                 unset($params['id_model']);
            }
            if($params['id_brand']!=""){
                 $query .= " AND cars.id_brand = :id_brand";
            }else{
                 unset($params['id_brand']);
            }
            if($params['speed_from']!="" && $params['speed_to']!=""){
                 $query .= " AND (max_speed BETWEEN :speed_from AND :speed_to)";
            }else{
                 unset($params['speed_from']);
                 unset($params['speed_to']);
            }
            if($params['engine_capacity_from']!="" && $params['engine_capacity_to']!="")
            {
                $query .= " AND (engine_capacity BETWEEN :engine_capacity_from"
                   ." AND :engine_capacity_to)";
            }else{
                 unset($params['engine_capacity_from']);
                 unset($params['engine_capacity_to']);
            }
            if($params['price_from']!="" && $params['price_to']!=""){
                 $query .= " AND (price BETWEEN :price_from AND :price_to)";
            }else{
                 unset($params['price_from']);
                 unset($params['price_to']);
            }
            $color_id = "";
            if($params['id_color']!=""){
                $color_id = $params['id_color'];  
            } 
            unset($params['id_color']);
            $sth = $this->pdo->prepare($query);
            $sth->execute($params);
            $collection = [];
            $arr_id = []; 
            $temp = [];
             while($res = $sth->fetch(\PDO::FETCH_ASSOC)){
                $temp[] = $res;
                $arr_id[] = $res['id'];
            }
             $collection['cars'] = $temp;
             $str_id = implode(",", $arr_id);

           if($color_id!=""){
                 $query = "SELECT cars.id, brands.name as brand, models.name as model, cars.img"
                . " FROM brands JOIN"
                . " (cars JOIN models ON models.id = cars.id_model)"
                . " ON brands.id = cars.id_brand "
                . " WHERE cars.id IN "
                . " (SELECT id_car FROM color_car WHERE id_color = :id_color AND id_car IN ($str_id))";
                $sth = $this->pdo->prepare($query);
                $sth->execute(['id_color'=>$color_id]);
                $collection['cars'] = $this->getFetchAccoss($sth);
            }else{
                 unset($params['id_color']);
            }

            $collection ['sucess'] = 1;
            return json_encode($collection);

            }else{
                $err_arr = ['sucess'=>0, 'error'=>ERROR_PARAMS];
                return json_encode($err_arr);
            }
        }catch(PDOException $err){
            file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
            $err_arr = ['sucess'=>0];
            return json_encode($err_arr);
        }
    }

    /*
    function of pre-ordering a car
     (Car ID, name, surname of the buyer, payment method.
     Payment method enumeration of "credit card", "cash")
    */
    public function order($params)
    {
        try{
            $params = json_decode($params, true);
            $params['id_car'] = (int) $params['id_car'];
            if($params['id_car']>0){
              $sth = $this->pdo->prepare('INSERT INTO orders (id_car, surname, name, payment_method) '
                    . 'VALUES (:id_car, :sname, :name, :payment_method)');
              $sth->execute($params);
              $res = ['res'=>$this->pdo->lastInsertId(),'sucess'=>1];
              return json_encode($res);
            }else{
              $err_arr = ['sucess'=>0, 'error'=>ERROR_PARAMS];
              return json_encode($err_arr);
            }
        }catch(PDOException $err){
            file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
            $err_arr = ['sucess'=>0];
            return json_encode($err_arr);
        }
    }
    
    public function getFetchAccoss($sth){
        $collection = [];
         while($res = $sth->fetch(\PDO::FETCH_ASSOC)){
            $collection[] = $res;
        }
        return $collection;
    }
}

var error_str = "We apologize, we have temporary problems with the service. return to us later"
var app = new Vue({
    el: '#app',
    data: {
       models : {},
       brands : {},
       colors: {},
        model : '',
        brand: '',
        color: '',
        year_from: '',
        year_to: '',
        speed_from: '',
        speed_to: '',
        engine_capacity_from: '',
        engine_capacity_to: '',
        price_from: '',
        price_to: '',
        cars: {},
        carsAll: {},
        carItem: {
            info:{},
            colors:{}
        },
        shortInfoCars: false,
        fullInfoCar: false,
        fullInfoCarRedirect: false,
        first_name: "",
        last_name: "",
        payment_method: "cash",
        err: '',
        isOrder: "",
        err_year: "",
        err_work: "",
        err_param:"",
        is_not_err_work :true,
        is_not_err_param : true
        
    },
    created() {
        var self = this
        myAjax.post("client.php","getSelectData",
            function(dataSelect){
                var data = JSON.parse(dataSelect)
                if( data['sucess']== "1"){
                    self.models = data['models']
                    self.brands = data['brands']
                    self.colors = data['colors']
                }else{
                    self.err_work = error_str
                    self.is_not_err_work = false;
                }
        });     
        myAjax.post("client.php","getCarsData",
            function(dataCars){
                var cars = JSON.parse(dataCars)
                self.cars = cars['cars']
                if( cars['sucess']== "1"){
                    self.carsAll = cars['cars']
                    self.shortInfoCars = true
                }else{
                    self.err_work = error_str
                    self.is_not_err_work = false;
                }
        }); 
        
    },
    methods:{
        autoInfo(car_id){
            this.clearError()
            this.clearFilter()
            this.shortInfoCars = false
            this.fullInfoCar = true
            var self = this
            myAjax.post("client.php","getCarInfo[car_id] = "+ car_id,
                function(dataCar){
                    var data = JSON.parse(dataCar)
                    if( data['sucess']== "1"){
                        self.carItem.info  = data['info']
                        self.carItem.colors  = data['colors']
                        self.fullInfoCarRedirect = true
                    }else{
                        if(data['error']!=""){
                           self.err_param = data['error']
                           self.is_not_err_param = false;
                       } else{
                        self.err_work = error_str
                         self.is_not_err_work = false;
                        }
                       
                    }
            });
        },
        
        ordered(car_id){
            this.clearError()
            this.shortInfoCars = false
            this.fullInfoCar = true
            if (this.first_name == "" || this.last_name == "" ) {
		        this.err = "not all fields are filled"
            }else{
               this.err = ""
               var req_str = "orderInfo[car_id]="+ car_id+"&orderInfo[name]="+this.last_name+"&orderInfo[sname]="+this.first_name+"&orderInfo[payment_method]="+this.payment_method
               var self = this;
               myAjax.post("client.php",req_str,
                function(answ)
                {
                    var answer = JSON.parse(answ)
                    if(answer['sucess']=="1") {
                        self.isOrder = "Your order is accepted"
                        self.last_name = ""
                        self.first_name = ""
                        self.payment_method = "cash"
                        setTimeout(function () {
                           self.isOrder = ""
                        },1500);
                    }else{
                    if(answer['error']!=""){
                        self.isOrder ="Error order: "+ answer['error'];
                    }else{
                        self.err_work = error_str
                        self.is_not_err_work = false
                    }
                    }
                       
                });
            }
        },

        changeSelect(){
            this.clearError()
            if(this.fullInfoCarRedirect == true)this.cars =  this.carsAll 
            if(this.year_from!='' && this.year_to=='') 
                    this.year_to = this.year_from
            this.shortInfoCars = true
            this.fullInfoCar = false
            var self = this          
            if(this.year_to == "" || this.year_from == "") this.err_year = "this parameter is required"
            else{
                this.err_year = ""
                if(this.speed_from!='' && this.speed_to=='') 
                    this.speed_to = this.speed_from
                if(this.price_from!='' && this.price_to=='') 
                    this.price_to = this.price_from
                
                 if(this.engine_capacity_from!='' && this.engine_capacity_to=='') 
                    this.engine_capacity_to = this.engine_capacity_from
                var req_str = "filterParams[id_brand]="+this.brand+"&filterParams[id_model]="+this.model+
                        "&filterParams[year_from]="+this.year_from+"&filterParams[year_to]="+this.year_to+
                         "&filterParams[engine_capacity_from]="+this.engine_capacity_from+
                         "&filterParams[engine_capacity_to]="+this.engine_capacity_to+
                         "&filterParams[speed_from]="+this.speed_from+
                         "&filterParams[speed_to]="+this.speed_to+
                         "&filterParams[price_from]="+this.price_from+
                         "&filterParams[price_to]="+this.price_to+
                         "&filterParams[color]="+this.color
                myAjax.post("client.php",req_str,
                    function(dataCars){
                      var data = JSON.parse(dataCars)
                       if( data['sucess']== "1"){
                         self.cars = data['cars']
                           if(self.cars.length == 0)
                            self.err_param = "The search has not given any result" 
                         self.fullInfoCarRedirect = false
                       }else{
                        if(data['error']!=""){
                           self.err_year = data['error']
                       }else{
                        self.err_work = error_str
                         self.is_not_err_work = false;
                        }
                       
                       }
                    });
            }
            
        },
        
        clearFilter(){
            this.model = ''
            this.brand = ''
            this.color = ''
            this.year_from = ''
            this.year_to = ''
            this.speed_from = ''
            this.speed_to = ''
            this.engine_capacity_from = ''
            this.engine_capacity_to = ''
            this.price_from = ''
            this.price_to = '' 
            this.err_year = ''
            this.cars =  this.carsAll 
            this.err_param = ""
        },

        clearError(){
            this.err_param = ""
            this.err_work = ""
            this.err_year = ""
            this.err = ""
            this.is_not_err_work = true
            this.is_not_err_param = true
        }

    }
})

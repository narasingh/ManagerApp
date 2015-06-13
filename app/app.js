var app = angular.module('myApp', ['ngRoute']);
app.factory("services", ['$http', function($http) {
  var serviceBase = 'services/'
    var obj = {};
    obj.getCustomers = function(){
        return $http.get(serviceBase + 'customers');
    }
    obj.getCustomer = function(customerID){
        return $http.get(serviceBase + 'customer?id=' + customerID);
    }

    obj.insertCustomer = function (customer) {
    return $http.post(serviceBase + 'insertCustomer', customer).then(function (results) {
        return results;
    });
	};

	obj.updateCustomer = function (id,customer) {
	    return $http.post(serviceBase + 'updateCustomer', {id:id, customer:customer}).then(function (status) {
	        return status.data;
	    });
	};

	obj.deleteCustomer = function (id) {
	    return $http.delete(serviceBase + 'deleteCustomer?id=' + id).then(function (status) {
	        return status.data;
	    });
	};

    return obj;   
}]);

app.controller('listCtrl', function ($location, $scope, services) {
    services.getCustomers().then(function(data){
        $scope.customers = data.data;
    });

    $scope.deleteCust = function(customer, index){

        if(confirm("Are you sure to delete ?")){
           services.deleteCustomer(customer.customerNumber).then(function(response){

              if(response.status =="Success"){
               $scope.customers.splice(index, 1);
           }  
           })
        }

    };


});

app.controller('editCtrl', function ($scope, $rootScope, $location, $routeParams, services, customer) {
    var customerID = ($routeParams.customerID) ? parseInt($routeParams.customerID) : 0;
    $rootScope.title = (customerID > 0) ? 'Edit Customer' : 'Add Customer';
    $scope.buttonText = (customerID > 0) ? 'Update Customer' : 'Add New Customer';
      var original = customer.data;
      original._id = customerID;
      $scope.customer = angular.copy(original);
      $scope.customer._id = customerID;

      $scope.isClean = function() {
        return angular.equals(original, $scope.customer);
      }

      $scope.deleteCustomer = function(customer) {
        $location.path('/');
        if(confirm("Are you sure to delete customer number: "+$scope.customer._id)==true)
        services.deleteCustomer(customer.customerNumber);
      };

      $scope.saveCustomer = function(customer) {
        $location.path('/');
        if (customerID <= 0) {
            services.insertCustomer(customer);
        }
        else {
            services.updateCustomer(customerID, customer);
        }
    };
});

app.directive("customerDetails", function(services){

   return {
      restrict : 'EA',
      scope : {
          custid : '@'
      },
      replace : false,
      link : function(scope, element, attrs){

         element.on("mouseover", function(e){
            
            var customer = services.getCustomer(scope.custid),
                __self = $(this);



            customer.then(function(response){

               //scope.customer = response.data;
               //var customer = response.data;
               //    __self.append('<p>lorem</p>');
            });

         }).on("mouseout", function(){

            //   $(this).find("p").remove(); 

         })
      }
   }

});

//customer directive to validate address
app.directive("invalidAddress", function(){

   return {

      restrict : 'A',
      require : 'ngModel',
      scope : {},
      link : function(scope, elem, attrs, ctrl){

        ctrl.$parsers.unshift(checkForChars);
        ctrl.$formatters.unshift(checkForChars); 

         function checkForChars(value){

            if(value){
                var pattern = /^[a-zA-Z\s]+$/;
                var isValid = pattern.test(value);

                ctrl.$setValidity("invalidchars", isValid);
            }

            return isValid;  

         };       
    
      }  


   }

})
//end

app.directive("ngcartSummary", function(){

  return {

    restrict : 'EA',
    scope : {},
    controller : 'productCtrl',
    transclude : true,
    templateUrl : 'partials/summary.html'

  }

});


app.filter("searchTxt", function(){

  return function(args, string){
    if(!string) return args;   

    var res = [];
        string = string.toLowerCase();

    angular.forEach(args, function(item){

      if(item.customerName.toLowerCase().indexOf(string) !== -1 ){
         res.push(item)
      }    
    });

    return res;
  }  
})

app.config(['$routeProvider',
  function($routeProvider) {
    $routeProvider.
      when('/', {
        title: 'Customers',
        templateUrl: 'partials/customers.html',
        controller: 'listCtrl'
      })
      .when('/edit-customer/:customerID', {
        title: 'Edit Customers',
        templateUrl: 'partials/edit-customer.html',
        controller: 'editCtrl',
        resolve: {
          customer: function(services, $route){
            var customerID = $route.current.params.customerID;
            return services.getCustomer(customerID);
          }
        }
      })
      .when('/products', {
          title : 'Products',
          templateUrl : "partials/products.html",
          controller : 'productCtrl'
      })
      .otherwise({
        redirectTo: '/'
      });
}]);
app.run(['$location', '$rootScope', function($location, $rootScope) {
    $rootScope.$on('$routeChangeSuccess', function (event, current, previous) {
        $rootScope.title = current.$$route.title;
    });
}]);
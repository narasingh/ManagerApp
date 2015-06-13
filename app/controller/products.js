app.controller('productCtrl', function($location, $scope, products){

  var cart = new products("mcart");

   cart.getProducts()
           .then(function(response){

                var response = response.data.products;

                $scope.products = (response.length) ? response : {};  

           });  

     
   $scope.addToCart = function(prod){

      cart.addItems({
          productId : prod.ID,
          item : prod.Name,
          price : prod.Price,
          quantity : 1
      });
 
   }

   $scope.cartObj = cart;

});
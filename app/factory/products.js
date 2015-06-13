app.value("localStorage", window.localStorage);

app.factory("products", ["$http", "$location", "localStorage", function($http, $location, localStorage ){

	var serviceBase = 'services/';

	var MyCart = function(cartName){

		this.cartName = cartName;
		this.clearCart = false;
		this.items = [];

		//load saved items from local storage
		this.loadItems();

	}

	MyCart.prototype.loadItems = function(){

		var storedItems = (localStorage !== null) ?  localStorage[this.cartName + "_items"] : null;

		if(JSON !== null && storedItems){

			this.items = JSON.parse(storedItems);

		}

		return this.items;

	}

	MyCart.prototype.saveItems = function(){

		if(localStorage !== null && this.items.length){
			//save items
			//console.log(JSON.stringify(this.items));
			localStorage[this.cartName + "_items"] = JSON.stringify(this.items);
		}

	}

	MyCart.prototype.addItems = function(items){

		//handle errors

		if(items.price <=0 || items.quantity <=0 || !items.hasOwnProperty("price") || !(items.hasOwnProperty("quantity")) ){  throw new Error("minimum quantity or price is mismatched!");
		}	

		var quantity = this.toNumber(items.quantity);
		var price    = parseFloat(items.price);
		var found 	 = false;
		var item	 = [];	

		for(var i =0, len = this.items.length; i < len; i++){

			//update quantity if same product added 	
			if(this.items[i].productId == items.productId){

				found = true;

				this.items[i].quantity = this.items[i].quantity + quantity; 

				//remove item if quantity or price is 0
				if(quantity == 0 || price == 0) this.items[i].splice(i, 1);

			}

		}
		if(!found || this.items.length == 0){

				//add the new items to the list
				item = new newItems(items.item, items.productId, quantity, price);
				this.items.push(item);
		}

		//save items
		this.saveItems();

	}

	MyCart.prototype.totalAmount = function(){

		var total = 0,
			items = this.loadItems();

		console.log(this.items);

		for(var i=0, len = items.length; i< len; i ++){

		   total += items[i].price * items[i].quantity;	

		}

		return total;

	}

	MyCart.prototype.toNumber = function(value){

		return (typeof value !=="undefined" && value !== null) ? value * 1 : 0;

	}	

	//return list of products in json format
	MyCart.prototype.getProducts = function(){

		return $http.get(serviceBase + 'products');

	}

	var newItems = function(item, productId, quantity, price){

		if(item === "" || typeof item === "undefined" || ~~productId <= 0) throw "Product canot be added!";

		var __self = this;
		
		__self.item 		= item;
		__self.productId 	= productId;
		__self.quantity 	= quantity;
		__self.price 		= price;

		return __self;

	}

	return MyCart;


}]);
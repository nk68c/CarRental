$(document).ready(init);

function init() {
    
  
    $(".search_button").click(search);
    $("#logout-link").click(logout);
    displayRentedCars();
    displayReturnedCars();
    
}

function attach_listeners(){
    $(".car_rent").click(function(){
        var id = $(this).attr("id");
        rentCar(id);
    });
    //onclicklistener for return button
    $(".return_car").click(function(){
       var id = $(this).attr("data-rental-id");
       returnCar(id);
    });
}
function search(){
    var searchTerm = $("#find-car-input").val();
    displaySearchedCars(searchTerm);
}

function logout(){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "text",
        data: {type: "logout"},
        success: function(data){
            if($.trim(data)== "success"){
            window.location.assign("index.html");
            }
        }
    });
}

function get_cars(){
    $.ajax({
        cache: false,
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "cars"},
        success: function(data){
            displaySearchCars(data);
        }
    });
}
function displaySearchedCars(term){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "searched", term: term},
        success: function(data){
            displaySearchCars(data);
        }
    });
}

function displayRentedCars(){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "rented"},
        success: function(data){
            displayRentCars(data);
        }
    });
}
function displayReturnedCars(){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "returned"},
        success: function(data){
            displayReturnCars(data);
        }
    });
}

function returnCar(id){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "returnCar", id: id},
        success: function(data){
            displayRentCars(data);
            displayReturnedCars();
            alert("Car returned!");
        }
    });
}
function rentCar(id){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "rentCar", id: id},
        success: function(data){
            displaySearchCars(data);
            displayRentedCars();
            alert("Car successfully rented!");
        }
    });
}

function displaySearchCars(data){
    var html_maker=new htmlMaker($('#find-car-template').html());
    var html=html_maker.getHTML(data);
    $("#search_results").html(html);
    attach_listeners();
}
function displayRentCars(data){
    var html_maker=new htmlMaker($('#rented-car-template').html());
    var html=html_maker.getHTML(data);
    $("#rented_cars").html(html);
    attach_listeners();
}
function displayReturnCars(data){
    var html_maker=new htmlMaker($('#returned-car-template').html());
    var html=html_maker.getHTML(data);
    $("#returned_cars").html(html);
}

<?php 
   //Connecting to Redis server on localhost 
   $Redis = new Redis(); 

   $Redis->connect('127.0.0.1', 6379); 
   echo "Connection to server sucessfully"; 

   //check whether server is running or not 
   echo "Server is running: ".$Redis->ping(); 

   //set the data in redis string 
   $Redis->set("tutorial-name", "Redis tutorial"); 
   
   // Get the stored data and print it 
   echo "Stored string in redis:: " .$Redis->get("tutorial-name"); 
?>
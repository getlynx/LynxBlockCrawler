<?php 
   //Connecting to Redis server on localhost 
   $redis = new Redis(); 

   $redis->connect('127.0.0.1', 6379); 
   echo "Connection to server sucessfully"; 

   //check whether server is running or not 
   echo "Server is running: ".$redis->ping(); 

   //set the data in redis string 
   $redis->set("tutorial-name", "Redis tutorial"); 
   
   // Get the stored data and print it 
   echo "Stored string in redis:: " .$redis→get("tutorial-name"); 
?>
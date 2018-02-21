<?php

function get_inv($db)        
{
    $query = 'SELECT * FROM inventory';
    $statement = $db->prepare($query);    
    $statement->execute();
    $supply = $statement->fetchAll();
    $statement->closeCursor();   
    return $supply;
}

function minimize_inv($db,$Qy)
{
    $query = 'UPDATE inventory SET quantity = quantity-:Qy';
    $statement = $db->prepare($query);
    $statement->execute();
    $statement->bindValue(':Qy',$Qy);
    $statement->execute();    
    $statement->closeCursor(); 
}

function get_undeliver($db) 
{
    $query = 'SELECT * FROM undelivered_orders';
    $statement = $db->prepare($query);    
    $statement->execute();
    $undel = $statement->fetchAll();
    $statement->closeCursor();   
    return $undel;
}

function erase_undeliver($db, $orderID) 
{    
    $query = 'DELETE FROM undelivered_orders WHERE id = :order_id';
    $statement = $db->prepare($query);
    $statement->bindValue(':order_id', $orderID);
    $statement->execute();
    $statement->closeCursor();
}

function upgrade_inv($db, $Qy)
{
    $query = 'UPDATE inventory SET quantity= quantity -:Qy';    
    $statement = $db->prepare($query);
    $statement->bindValue(':Qy', $Qy);
    $statement->execute();    
    $statement->closeCursor();    
}

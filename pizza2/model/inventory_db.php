<?php

function add_undelivr_ord($db, $orderID, $flr_qy, $chs_qy)
{
    $query = 'INSERT INTO undelivered_orders VALUES (:order_id, :flr_qy, :chs_qy)';
    $statement = $db->prepare($query);
    $statement->bindValue(':order_id', $orderID);   
    $statement->bindValue(':flr_qy', $flr_qy);  
    $statement->bindValue(':chs_qy', $chs_qy);  
    $statement->execute();    
    $statement->closeCursor();
}


function refill_flour($db, $flr_qy) 
{    
    $query = 'UPDATE inventory SET quantity = quantity + :flr_qy where product_id = 11';    
    $statement = $db->prepare($query);
    $statement->bindValue(':flr_qy', $flr_qy);
    $statement->execute(); 
    $statement->closeCursor();
    
}
    
function refill_cheese($db, $chs_qy) 
{    
    $query = 'UPDATE inventory SET quantity= quantity + :chs_qy where product_id = 12';    
    $statement = $db->prepare($query);
    $statement->bindValue(':chs_qy', $chs_qy);
    $statement->execute();    
    $statement->closeCursor();
    
}

function reduce_inv_on_order($db,$q)
{
    $query = 'UPDATE inventory  SET quantity = quantity - :q';
    $statement = $db->prepare($query);
    $statement->bindValue(':q',$q);
    $statement->execute();    
    $statement->closeCursor(); 
}





function get_undelivr($db) {
    $query = 'SELECT * FROM undelivered_orders';
    $statement = $db->prepare($query);    
    $statement->execute();
    $orders_undel = $statement->fetchAll();
    $statement->closeCursor();   
    return $orders_undel;
}



function erase_undelivr($db, $orderID) 
{    
    $query = 'DELETE FROM undelivered_orders WHERE order_id = :order_id';
    $statement = $db->prepare($query);
    $statement->bindValue(':order_id', $orderID);
    $statement->execute();
    $statement->closeCursor();
}

function get_inventory_lists($db)
{
    $query = 'SELECT * FROM inventory';
    $statement = $db->prepare($query);
    $statement->execute();    
    $inv = $statement->fetchAll();
    $statement->closeCursor();
    return $inv;
}


?>



<?php

function set_server_day($db, $day)
{
    $query = ' UPDATE systemDay SET dayNumber = :today';
    $statement = $db->prepare($query);
    $statement->bindValue(':today', $day);
    $statement->execute();
    $statement->closeCursor();
}

function get_server_presentday($db)
{
    $query = 'select * from systemDay';
    $statement = $db->prepare($query);
    $statement->execute();
    $present = $statement->fetch();
    $statement->closeCursor();
    return $present['dayNumber']; 
}





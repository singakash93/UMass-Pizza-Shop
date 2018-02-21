<?php include '../../view/header.php'; ?>
<main>
    <section>
        <h1>Today is day <?php echo $current_day; ?></h1>
        <form action="index.php" method="post">
            <input type="hidden" name="action" value="next_day">
            <input type="submit" value="Advance to day <?php echo $current_day + 1; ?>" />
        </form>

        <form  action="index.php" method="post">
            <input type="hidden" name="action" value="initial_db">           
            <input type="submit" value="Initialize DB (making day = 1)" />
            <br>
        </form>
        <br>
        <h2>Today's Orders</h2>
        <?php if (count($todays_orders) > 0): ?>
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Room No</th>
                    <th>Status</th>
                </tr>

                <?php foreach ($todays_orders as $todays_order) : ?>
                    <tr>
                        <td><?php echo $todays_order['id']; ?> </td>
                        <td><?php echo $todays_order['room_number']; ?> </td>  
                        <td><?php echo $todays_order['status']; ?> <td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No Orders Today </p>
        <?php endif; ?>
        <h2>Supplies On Order</h2>
         <h2>Current Inventory</h2>
        <!-- <p>Flour Quantity: <?php echo $inventory_details[0]['quantity']; ?> </p>-->
         <!--<p>Cheese Quantity: <?php echo $inventory_details[1]['quantity']; ?> </p>-->
         
         <?php foreach ($inventory_details as $invn_details) : ?>
                <?php echo $invn_details['productname'];?>: <br>
                <?php echo $invn_details['quantity'];?> <br>
         <?php endforeach; ?>
         
    </section>

</main>
<?php include '../../view/footer.php'; ?>
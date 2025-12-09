<?php 
ob_start();
include 'header.php'; 

// --- ADD ORDER ---
if (isset($_POST['add_order'])) {
    $product_id = intval($_POST['product_id']);
    $customer_id = intval($_POST['customer_id']);
    $total_item = intval($_POST['total_item']);
    
    $conn->begin_transaction();
    try {
        $check_stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
        $check_stmt->bind_param("i", $product_id);
        $check_stmt->execute();
        $check_stmt->bind_result($current_quantity);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($current_quantity < $total_item) throw new Exception("Insufficient stock! Only {$current_quantity} item(s) available.");

        $stmt_order = $conn->prepare("INSERT INTO orders (product_id, customer_id, total_item) VALUES (?, ?, ?)");
        $stmt_order->bind_param("iii", $product_id, $customer_id, $total_item);
        if (!$stmt_order->execute()) throw new Exception("Error recording order: " . $stmt_order->error);
        $stmt_order->close();

        $stmt_update = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
        $stmt_update->bind_param("ii", $total_item, $product_id);
        if (!$stmt_update->execute()) throw new Exception("Error updating inventory: " . $stmt_update->error);
        $stmt_update->close();

        $conn->commit();
        $_SESSION['message'] = "Order recorded and inventory updated successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Order failed: " . $e->getMessage();
    }
    ob_end_clean();
    header("Location: order.php");
    exit();
}

// --- DELETE ORDER ---
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->begin_transaction();
    try {
        $get_stmt = $conn->prepare("SELECT product_id, total_item FROM orders WHERE id = ?");
        $get_stmt->bind_param("i", $id);
        $get_stmt->execute();
        $get_stmt->bind_result($product_id, $total_item);
        if (!$get_stmt->fetch()) throw new Exception("Order not found.");
        $get_stmt->close();

        $rollback_stmt = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
        $rollback_stmt->bind_param("ii", $total_item, $product_id);
        if (!$rollback_stmt->execute()) throw new Exception("Error rolling back inventory: " . $rollback_stmt->error);
        $rollback_stmt->close();

        $delete_stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $delete_stmt->bind_param("i", $id);
        if (!$delete_stmt->execute()) throw new Exception("Error deleting order: " . $delete_stmt->error);
        $delete_stmt->close();

        $conn->commit();
        $_SESSION['message'] = "Order deleted and inventory rolled back successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Deletion failed: " . $e->getMessage();
    }
    ob_end_clean();
    header("Location: order.php");
    exit();
}

// --- FETCH DATA ---
$res_table = $conn->query("
    SELECT o.id, o.total_item, p.name as product_name, p.model, c.name as customer_name
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.id
    LEFT JOIN customers c ON o.customer_id = c.id
    ORDER BY o.id DESC
");

$products_res = $conn->query("SELECT id, name, model, quantity FROM products WHERE status='Active' ORDER BY name ASC");
$customers_res = $conn->query("SELECT id, name FROM customers ORDER BY name ASC");
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Manage Orders</span>
        <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addOrderModal">
            <i class="fas fa-plus"></i> New Order
        </button>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success rounded-0"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger rounded-0"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <table class="table table-hover table-striped" id="orderTable">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="30%">Product</th>
                    <th width="15%">Total Item</th>
                    <th width="30%">Customer</th>
                    <th width="20%">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $res_table->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><b><?php echo $row['product_name']; ?></b> (<?php echo $row['model']; ?>)</td>
                    <td><?php echo $row['total_item']; ?></td>
                    <td><?php echo $row['customer_name']; ?></td>
                    <td>
                        <button class="btn btn-sm btn-info rounded-0"><i class="fas fa-eye"></i></button>
                        <a href="order.php?delete_id=<?php echo $row['id']; ?>" 
                           onclick="return confirm('Deleting this order will restore <?php echo $row['total_item']; ?> unit(s) of <?php echo $row['product_name']; ?>. Continue?');">
                            <button class="btn btn-sm btn-danger rounded-0"><i class="fas fa-trash"></i></button>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-dark text-white rounded-0">
                <h5 class="modal-title">Record New Order</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold">Product</label>
                        <select name="product_id" class="form-control rounded-0" required>
                            <option value="">Select Product</option>
                            <?php $products_res->data_seek(0); while($prod = $products_res->fetch_assoc()): ?>
                                <option value="<?php echo $prod['id']; ?>"><?php echo "{$prod['name']} ({$prod['model']}) - Stock: {$prod['quantity']}"; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Customer</label>
                        <select name="customer_id" class="form-control rounded-0" required>
                            <option value="">Select Customer</option>
                            <?php $customers_res->data_seek(0); while($cust = $customers_res->fetch_assoc()): ?>
                                <option value="<?php echo $cust['id']; ?>"><?php echo $cust['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Quantity Ordered</label>
                        <input type="number" name="total_item" class="form-control rounded-0" required min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_order" class="btn btn-primary-custom">Place Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
include 'footer.php'; 
ob_end_flush();
?>
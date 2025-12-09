<?php 
ob_start();
include 'header.php'; 

if (isset($_POST['add_purchase'])) {
    $product_id = intval($_POST['product_id']);
    $supplier_id = intval($_POST['supplier_id']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);
    $total_cost = $quantity * $price;

    $conn->begin_transaction();
    
    try {
        $sql_purchase = "INSERT INTO purchases (product_id, supplier_id, quantity, price, total_cost) VALUES (?, ?, ?, ?, ?)";
        $stmt_purchase = $conn->prepare($sql_purchase);
        $stmt_purchase->bind_param("iiidd", $product_id, $supplier_id, $quantity, $price, $total_cost);
        
        if (!$stmt_purchase->execute()) {
            throw new Exception("Error recording purchase: " . $stmt_purchase->error);
        }
        $stmt_purchase->close();

        $sql_update = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ii", $quantity, $product_id);

        if (!$stmt_update->execute()) {
            throw new Exception("Error updating inventory: " . $stmt_update->error);
        }
        $stmt_update->close();

        $conn->commit();
        $_SESSION['message'] = "Purchase recorded and inventory updated successfully!";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Purchase failed. Details: " . $e->getMessage();
    }
    
    ob_end_clean();
    header("Location: purchase.php");
    exit();
}

$sql = "SELECT pu.id, pu.quantity, pu.price, pu.total_cost, p.name AS product_name, s.name AS supplier_name
        FROM purchases pu
        LEFT JOIN products p ON pu.product_id = p.id
        LEFT JOIN suppliers s ON pu.supplier_id = s.id
        ORDER BY pu.id DESC";
$result = $conn->query($sql);

$products_res = $conn->query("SELECT id, name, model, quantity FROM products WHERE status = 'Active' ORDER BY name ASC");
$suppliers_res = $conn->query("SELECT id, name FROM suppliers WHERE status = 'Active' ORDER BY name ASC");

$products = $products_res->fetch_all(MYSQLI_ASSOC);
$suppliers = $suppliers_res->fetch_all(MYSQLI_ASSOC);
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Purchase Records</span>
        <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">
            <i class="fas fa-plus"></i> Record Purchase
        </button>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success rounded-0"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger rounded-0"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <table class="table table-hover table-striped" id="purchaseTable">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="30%">Product</th>
                    <th width="20%">Supplier</th>
                    <th width="10%">Qty</th>
                    <th width="15%">Price</th>
                    <th width="15%">Total Cost</th>
                    <th width="5%">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo $row['supplier_name']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td>$<?php echo number_format($row['price'], 2); ?></td>
                        <td>$<?php echo number_format($row['total_cost'], 2); ?></td>
                        <td>
                            <button class="btn btn-sm btn-info rounded-0"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addPurchaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-dark text-white rounded-0">
                <h5 class="modal-title">Record New Purchase</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="purchase.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold">Product</label>
                        <select name="product_id" class="form-control rounded-0" required>
                            <option value="">Select Product</option>
                            <?php foreach ($products as $prod): ?>
                                <option value="<?php echo $prod['id']; ?>"><?php echo $prod['name']; ?> (Stock: <?php echo $prod['quantity']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Supplier</label>
                        <select name="supplier_id" class="form-control rounded-0" required>
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $sup): ?>
                                <option value="<?php echo $sup['id']; ?>"><?php echo $sup['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Quantity Received</label>
                        <input type="number" name="quantity" class="form-control rounded-0" required min="1">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Unit Purchase Price</label>
                        <input type="number" step="0.01" name="price" class="form-control rounded-0" required min="0.01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_purchase" class="btn btn-primary-custom">Save Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<?php ob_end_flush(); ?>
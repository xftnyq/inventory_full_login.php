<?php 
ob_start();
include 'header.php'; 

if (isset($_POST['add_supplier'])) {
    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO suppliers (name, mobile, address, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $mobile, $address, $status);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Supplier added successfully!";
    } else {
        $_SESSION['error'] = "Error adding supplier: " . $stmt->error;
    }
    $stmt->close();
    
    ob_end_clean();
    header("Location: supplier.php");
    exit();
}

if (isset($_POST['edit_supplier'])) {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE suppliers SET name = ?, mobile = ?, address = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $mobile, $address, $status, $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Supplier updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating supplier: " . $stmt->error;
    }
    $stmt->close();
    
    ob_end_clean();
    header("Location: supplier.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    $check_products_stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE supplier_id = ?");
    $check_products_stmt->bind_param("i", $id);
    $check_products_stmt->execute();
    $check_products_stmt->bind_result($check_products);
    $check_products_stmt->fetch();
    $check_products_stmt->close();
    
    $check_purchases_stmt = $conn->prepare("SELECT COUNT(*) FROM purchases WHERE supplier_id = ?");
    $check_purchases_stmt->bind_param("i", $id);
    $check_purchases_stmt->execute();
    $check_purchases_stmt->bind_result($check_purchases);
    $check_purchases_stmt->fetch();
    $check_purchases_stmt->close();

    if ($check_products > 0 || $check_purchases > 0) {
        $_SESSION['error'] = "Cannot delete: Supplier is linked to existing products ({$check_products}) or purchases ({$check_purchases}).";
    } else {
        $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Supplier deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting supplier: " . $stmt->error;
        }
        $stmt->close();
    }
    
    ob_end_clean();
    header("Location: supplier.php");
    exit();
}

$sql = "SELECT * FROM suppliers ORDER BY id DESC";
$result = $conn->query($sql);
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Supplier List</span>
        <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="fas fa-plus"></i> Add Supplier
        </button>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success rounded-0"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger rounded-0"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <table class="table table-hover table-striped" id="supplierTable">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="30%">Name</th>
                    <th width="20%">Mobile</th>
                    <th width="30%">Address</th>
                    <th width="5%">Status</th>
                    <th width="10%">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['mobile']; ?></td>
                        <td><?php echo $row['address']; ?></td>
                        <td><span class="badge bg-<?php echo ($row['status'] == 'Active' ? 'success' : 'secondary'); ?>"><?php echo $row['status']; ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-info rounded-0 edit-btn"
                                data-bs-toggle="modal" data-bs-target="#editSupplierModal"
                                data-id="<?php echo $row['id']; ?>"
                                data-name="<?php echo $row['name']; ?>"
                                data-mobile="<?php echo $row['mobile']; ?>"
                                data-address="<?php echo $row['address']; ?>"
                                data-status="<?php echo $row['status']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="supplier.php?delete_id=<?php echo $row['id']; ?>" 
                                onclick="return confirm('Are you sure you want to delete this supplier?');" style="text-decoration: none;">
                                <button class="btn btn-sm btn-danger rounded-0"><i class="fas fa-trash"></i></button>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-dark text-white rounded-0">
                <h5 class="modal-title">Add New Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="supplier.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold">Name</label>
                        <input type="text" name="name" class="form-control rounded-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Mobile</label>
                        <input type="text" name="mobile" class="form-control rounded-0">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Address</label>
                        <textarea name="address" class="form-control rounded-0"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Status</label>
                        <select name="status" class="form-control rounded-0">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_supplier" class="btn btn-primary-custom">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-dark text-white rounded-0">
                <h5 class="modal-title">Edit Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="supplier.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_supplier_id">
                    <div class="mb-3">
                        <label class="fw-bold">Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control rounded-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Mobile</label>
                        <input type="text" name="mobile" id="edit_mobile" class="form-control rounded-0">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Address</label>
                        <textarea name="address" id="edit_address" class="form-control rounded-0"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Status</label>
                        <select name="status" id="edit_status" class="form-control rounded-0">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="edit_supplier" class="btn btn-primary-custom">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.edit-btn').on('click', function() {
            $('#edit_supplier_id').val($(this).data('id'));
            $('#edit_name').val($(this).data('name'));
            $('#edit_mobile').val($(this).data('mobile'));
            $('#edit_address').val($(this).data('address'));
            $('#edit_status').val($(this).data('status')); 
        });
    });
</script>

<?php include 'footer.php'; ?>
<?php ob_end_flush(); ?>
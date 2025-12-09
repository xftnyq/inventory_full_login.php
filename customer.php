<?php 
// 1. START OUTPUT BUFFERING: This ensures that header() redirects work 
// even if content (like from header.php) is sent before the redirect.
ob_start();

include 'header.php'; 

// --- CRUD LOGIC ---

// A. Handle ADD Customer
if (isset($_POST['add_customer'])) {
    // SECURITY: Input values are sanitized securely by bind_param later.
    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO customers (name, mobile, address) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $mobile, $address);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Customer added successfully!";
    } else {
        $_SESSION['error'] = "Error adding customer: " . $stmt->error;
    }
    $stmt->close();
    
    // Clear buffer and redirect 
    ob_end_clean();
    header("Location: customer.php");
    exit();
}

// B. Handle EDIT Customer
if (isset($_POST['edit_customer'])) {
    $id = intval($_POST['id']);
    // SECURITY: Input values are sanitized securely by bind_param later.
    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("UPDATE customers SET name = ?, mobile = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $mobile, $address, $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Customer updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating customer: " . $stmt->error;
    }
    $stmt->close();
    
    // Clear buffer and redirect
    ob_end_clean();
    header("Location: customer.php");
    exit();
}

// C. Handle DELETE Customer
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    // Check data integrity: prevent deletion if customer has existing orders.
    // NOTE: This initial check uses a simple query for COUNT(*), which is fine here.
    $check_orders_stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ?");
    $check_orders_stmt->bind_param("i", $id);
    $check_orders_stmt->execute();
    $check_orders_stmt->bind_result($check_orders);
    $check_orders_stmt->fetch();
    $check_orders_stmt->close();

    if ($check_orders > 0) {
        $_SESSION['error'] = "Cannot delete: Customer is linked to existing orders ({$check_orders} order(s)).";
    } else {
        $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Customer deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting customer: " . $stmt->error;
        }
        $stmt->close();
    }
    
    // Clear buffer and redirect
    ob_end_clean();
    header("Location: customer.php");
    exit();
}

// --- DATA FETCHING FOR VIEW ---
$sql = "SELECT * FROM customers ORDER BY id DESC";
$result = $conn->query($sql);
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Customer List</span>
        <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="fas fa-plus"></i> Add Customer
        </button>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success rounded-0"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger rounded-0"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <table class="table table-hover table-striped" id="customerTable">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="25%">Name</th>
                    <th width="15%">Mobile</th>
                    <th width="40%">Address</th>
                    <th width="15%">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['mobile']; ?></td>
                        <td><?php echo $row['address']; ?></td>
                        <td>
                            <button class="btn btn-sm btn-info rounded-0 edit-btn"
                                data-bs-toggle="modal" data-bs-target="#editCustomerModal"
                                data-id="<?php echo $row['id']; ?>"
                                data-name="<?php echo $row['name']; ?>"
                                data-mobile="<?php echo $row['mobile']; ?>"
                                data-address="<?php echo $row['address']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="customer.php?delete_id=<?php echo $row['id']; ?>" 
                                onclick="return confirm('Are you sure you want to delete this customer?');" style="text-decoration: none;">
                                <button class="btn btn-sm btn-danger rounded-0"><i class="fas fa-trash"></i></button>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-dark text-white rounded-0">
                <h5 class="modal-title">Add New Customer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="customer.php" method="POST">
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
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_customer" class="btn btn-primary-custom">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-dark text-white rounded-0">
                <h5 class="modal-title">Edit Customer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="customer.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_customer_id">
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
                </div>
                <div class="modal-footer">
                    <button type="submit" name="edit_customer" class="btn btn-primary-custom">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Initialize DataTables
        // $('#customerTable').DataTable(); 
        
        $('.edit-btn').on('click', function() {
            // Get data attributes and populate the edit modal fields
            $('#edit_customer_id').val($(this).data('id'));
            $('#edit_name').val($(this).data('name'));
            $('#edit_mobile').val($(this).data('mobile'));
            $('#edit_address').val($(this).data('address'));
        });
    });
</script>

<?php include 'footer.php'; ?>
<?php ob_end_flush(); // Send all buffered content to the browser at the end. ?>
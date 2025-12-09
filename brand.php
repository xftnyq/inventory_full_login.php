<?php 
ob_start();

include 'header.php'; 

// --- CRUD LOGIC ---

// A. Handle ADD Brand
if (isset($_POST['add_brand'])) {
    $category_id = intval($_POST['category_id']);
    $name = $_POST['name'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO brands (category_id, name, status) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $category_id, $name, $status);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Brand added successfully!";
    } else {
        $_SESSION['error'] = "Error adding brand: " . $stmt->error;
    }
    $stmt->close();
    
    ob_end_clean();
    header("Location: brand.php");
    exit();
}

// B. Handle EDIT Brand
if (isset($_POST['edit_brand'])) {
    $id = intval($_POST['id']);
    $category_id = intval($_POST['category_id']);
    $name = $_POST['name'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE brands SET category_id = ?, name = ?, status = ? WHERE id = ?");
    $stmt->bind_param("issi", $category_id, $name, $status, $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Brand updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating brand: " . $stmt->error;
    }
    $stmt->close();
    
    ob_end_clean();
    header("Location: brand.php");
    exit();
}

// C. Handle DELETE Brand
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    $check_products_stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE brand_id = ?");
    $check_products_stmt->bind_param("i", $id);
    $check_products_stmt->execute();
    $check_products_stmt->bind_result($check_products);
    $check_products_stmt->fetch();
    $check_products_stmt->close();

    if ($check_products > 0) {
        $_SESSION['error'] = "Cannot delete: Brand is linked to existing products ({$check_products} product(s)).";
    } else {
        $stmt = $conn->prepare("DELETE FROM brands WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Brand deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting brand: " . $stmt->error;
        }
        $stmt->close();
    }
    
    ob_end_clean();
    header("Location: brand.php");
    exit();
}

// --- DATA FETCHING FOR VIEW & FORMS ---
$sql = "SELECT b.id, b.name, b.status, b.category_id, c.name as category_name
        FROM brands b
        LEFT JOIN categories c ON b.category_id = c.id
        ORDER BY b.id DESC";
$result = $conn->query($sql);

$categories_res = $conn->query("SELECT id, name FROM categories WHERE status = 'Active' ORDER BY name ASC");
$categories = $categories_res->fetch_all(MYSQLI_ASSOC);
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Brand List</span>
        <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addBrandModal">
            <i class="fas fa-plus"></i> Add Brand
        </button>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success rounded-0"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger rounded-0"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <table class="table table-hover table-striped" id="brandTable">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="30%">Name</th>
                    <th width="30%">Category</th>
                    <th width="20%">Status</th>
                    <th width="15%">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['category_name']; ?></td>
                        <td><span class="badge bg-<?php echo ($row['status'] == 'Active' ? 'success' : 'secondary'); ?>"><?php echo $row['status']; ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-info rounded-0 edit-btn"
                                data-bs-toggle="modal" data-bs-target="#editBrandModal"
                                data-id="<?php echo $row['id']; ?>"
                                data-name="<?php echo $row['name']; ?>"
                                data-category-id="<?php echo $row['category_id']; ?>"
                                data-status="<?php echo $row['status']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="brand.php?delete_id=<?php echo $row['id']; ?>" 
                                onclick="return confirm('Are you sure you want to delete this brand?');" style="text-decoration: none;">
                                <button class="btn btn-sm btn-danger rounded-0"><i class="fas fa-trash"></i></button>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addBrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-dark text-white rounded-0">
                <h5 class="modal-title">Add New Brand</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="brand.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold">Category</label>
                        <select name="category_id" class="form-control rounded-0" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Brand Name</label>
                        <input type="text" name="name" class="form-control rounded-0" required>
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
                    <button type="submit" name="add_brand" class="btn btn-primary-custom">Save Brand</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editBrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-dark text-white rounded-0">
                <h5 class="modal-title">Edit Brand</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="brand.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_brand_id">
                    <div class="mb-3">
                        <label class="fw-bold">Category</label>
                        <select name="category_id" id="edit_category_id" class="form-control rounded-0" required>
                            <option value="">Select Category</option>
                            <?php 
                            if ($categories_res->num_rows > 0) {
                                $categories_res->data_seek(0);
                                $edit_categories = $categories_res->fetch_all(MYSQLI_ASSOC);
                                foreach ($edit_categories as $cat): 
                            ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                            <?php 
                                endforeach; 
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Brand Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control rounded-0" required>
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
                    <button type="submit" name="edit_brand" class="btn btn-primary-custom">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.edit-btn').on('click', function() {
            $('#edit_brand_id').val($(this).data('id'));
            $('#edit_name').val($(this).data('name'));
            $('#edit_category_id').val($(this).data('category-id'));
            $('#edit_status').val($(this).data('status')); 
        });
    });
</script>

<?php include 'footer.php'; ?>
<?php ob_end_flush(); ?>
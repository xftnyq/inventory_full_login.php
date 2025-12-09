<?php 
ob_start();

include 'header.php'; 

if (isset($_POST['add_category'])) {
    $name = $_POST['name'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO categories (name, status) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $status);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Category added successfully!";
    } else {
        $_SESSION['error'] = "Error adding category: " . $stmt->error;
    }
    $stmt->close();
    
    ob_end_clean();
    header("Location: category.php");
    exit();
}

if (isset($_POST['edit_category'])) {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE categories SET name = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $status, $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Category updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating category: " . $stmt->error;
    }
    $stmt->close();
    
    ob_end_clean();
    header("Location: category.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    $check_products_stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $check_products_stmt->bind_param("i", $id);
    $check_products_stmt->execute();
    $check_products_stmt->bind_result($check_products);
    $check_products_stmt->fetch();
    $check_products_stmt->close();
    
    $check_brands_stmt = $conn->prepare("SELECT COUNT(*) FROM brands WHERE category_id = ?");
    $check_brands_stmt->bind_param("i", $id);
    $check_brands_stmt->execute();
    $check_brands_stmt->bind_result($check_brands);
    $check_brands_stmt->fetch();
    $check_brands_stmt->close();

    if ($check_products > 0 || $check_brands > 0) {
        $_SESSION['error'] = "Cannot delete: Category is linked to existing products ({$check_products}) or brands ({$check_brands}).";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Category deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting category: " . $stmt->error;
        }
        $stmt->close();
    }
    
    ob_end_clean();
    header("Location: category.php");
    exit();
}

$sql = "SELECT * FROM categories ORDER BY id DESC";
$result = $conn->query($sql);
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Category List</span>
        <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus"></i> Add Category
        </button>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success rounded-0"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger rounded-0"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <table class="table table-hover table-striped" id="categoryTable">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="60%">Name</th>
                    <th width="20%">Status</th>
                    <th width="15%">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><span class="badge bg-<?php echo ($row['status'] == 'Active' ? 'success' : 'secondary'); ?>"><?php echo $row['status']; ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-info rounded-0 edit-btn"
                                data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                data-id="<?php echo $row['id']; ?>"
                                data-name="<?php echo $row['name']; ?>"
                                data-status="<?php echo $row['status']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="category.php?delete_id=<?php echo $row['id']; ?>" 
                                onclick="return confirm('Are you sure you want to delete this category?');" style="text-decoration: none;">
                                <button class="btn btn-sm btn-danger rounded-0"><i class="fas fa-trash"></i></button>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-dark text-white rounded-0">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="category.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold">Category Name</label>
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
                    <button type="submit" name="add_category" class="btn btn-primary-custom">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-dark text-white rounded-0">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="category.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_category_id">
                    <div class="mb-3">
                        <label class="fw-bold">Category Name</label>
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
                    <button type="submit" name="edit_category" class="btn btn-primary-custom">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.edit-btn').on('click', function() {
            $('#edit_category_id').val($(this).data('id'));
            $('#edit_name').val($(this).data('name'));
            $('#edit_status').val($(this).data('status')); 
        });
    });
</script>

<?php include 'footer.php'; ?>
<?php ob_end_flush(); ?>
<?php 
ob_start(); 
include 'header.php'; 

if (isset($_POST['add_product'])) {
    $category_id = intval($_POST['category_id']);
    $brand_id = intval($_POST['brand_id']);
    $supplier_id = intval($_POST['supplier_id']);
    $name = $_POST['name']; 
    $model = $_POST['model'];
    $quantity = intval($_POST['quantity']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO products (category_id, brand_id, supplier_id, name, model, quantity, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissis", $category_id, $brand_id, $supplier_id, $name, $model, $quantity, $status);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Product added successfully!";
    } else {
        $_SESSION['error'] = "Error adding product: " . $stmt->error;
    }
    $stmt->close();
    
    ob_end_clean(); 
    header("Location: product.php");
    exit();
}

if (isset($_POST['edit_product'])) {
    $id = intval($_POST['id']);
    $category_id = intval($_POST['category_id']);
    $brand_id = intval($_POST['brand_id']);
    $supplier_id = intval($_POST['supplier_id']);
    $name = $_POST['name'];
    $model = $_POST['model'];
    $quantity = intval($_POST['quantity']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE products SET category_id = ?, brand_id = ?, supplier_id = ?, name = ?, model = ?, quantity = ?, status = ? WHERE id = ?");
    $stmt->bind_param("iiissisi", $category_id, $brand_id, $supplier_id, $name, $model, $quantity, $status, $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Product updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating product: " . $stmt->error;
    }
    $stmt->close();
    
    ob_end_clean();
    header("Location: product.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Product deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting product: " . $stmt->error;
    }
    $stmt->close();
    
    ob_end_clean();
    header("Location: product.php");
    exit();
}

$sql = "SELECT p.id, p.name AS product_name, p.model, p.quantity, p.status, 
                c.name AS category_name, b.name AS brand_name, s.name AS supplier_name,
                p.category_id, p.brand_id, p.supplier_id
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        ORDER BY p.id ASC";
$result = $conn->query($sql);

$categories_res = $conn->query("SELECT id, name FROM categories WHERE status = 'Active' ORDER BY name ASC");
$brands_res = $conn->query("SELECT id, name, category_id FROM brands WHERE status = 'Active' ORDER BY name ASC");
$suppliers_res = $conn->query("SELECT id, name FROM suppliers WHERE status = 'Active' ORDER BY name ASC");

$categories = $categories_res->fetch_all(MYSQLI_ASSOC);
$brands = $brands_res->fetch_all(MYSQLI_ASSOC);
$suppliers = $suppliers_res->fetch_all(MYSQLI_ASSOC);
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Product List</span>
        <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fas fa-plus"></i> Add Product
        </button>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success rounded-0"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger rounded-0"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <table class="table table-hover table-striped" id="productTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Brand Name</th>
                    <th>Product Name</th>
                    <th>Model</th>
                    <th>Qty</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['category_name']; ?></td>
                        <td><?php echo $row['brand_name']; ?></td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo $row['model']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td><?php echo $row['supplier_name']; ?></td>
                        <td><span class="badge bg-<?php echo ($row['status'] == 'Active' ? 'success' : 'secondary'); ?>"><?php echo $row['status']; ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-info rounded-0 edit-btn"
                                data-bs-toggle="modal" data-bs-target="#editProductModal"
                                data-id="<?php echo $row['id']; ?>"
                                data-category-id="<?php echo $row['category_id']; ?>"
                                data-brand-id="<?php echo $row['brand_id']; ?>"
                                data-supplier-id="<?php echo $row['supplier_id']; ?>"
                                data-name="<?php echo $row['product_name']; ?>"
                                data-model="<?php echo $row['model']; ?>"
                                data-quantity="<?php echo $row['quantity']; ?>"
                                data-status="<?php echo $row['status']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="product.php?delete_id=<?php echo $row['id']; ?>" 
                                onclick="return confirm('Are you sure you want to delete this product?');" style="text-decoration: none;">
                                <button class="btn btn-sm btn-danger rounded-0"><i class="fas fa-trash"></i></button>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-dark text-white rounded-0">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="product.php" method="POST">
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
                        <label class="fw-bold">Brand</label>
                        <select name="brand_id" class="form-control rounded-0" required>
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $br): ?>
                                <option value="<?php echo $br['id']; ?>"><?php echo $br['name']; ?></option>
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
                        <label class="fw-bold">Product Name</label>
                        <input type="text" name="name" class="form-control rounded-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Product Model</label>
                        <input type="text" name="model" class="form-control rounded-0">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Starting Quantity</label>
                        <input type="number" name="quantity" class="form-control rounded-0" required min="0">
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
                    <button type="submit" name="add_product" class="btn btn-primary-custom">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-dark text-white rounded-0">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="product.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_product_id">
                    <div class="mb-3">
                        <label class="fw-bold">Category</label>
                        <select name="category_id" id="edit_category_id" class="form-control rounded-0" required>
                            <option value="">Select Category</option>
                            <?php $categories_res->data_seek(0); foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Brand</label>
                        <select name="brand_id" id="edit_brand_id" class="form-control rounded-0" required>
                            <option value="">Select Brand</option>
                            <?php $brands_res->data_seek(0); foreach ($brands as $br): ?>
                                <option value="<?php echo $br['id']; ?>"><?php echo $br['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Supplier</label>
                        <select name="supplier_id" id="edit_supplier_id" class="form-control rounded-0" required>
                            <option value="">Select Supplier</option>
                            <?php $suppliers_res->data_seek(0); foreach ($suppliers as $sup): ?>
                                <option value="<?php echo $sup['id']; ?>"><?php echo $sup['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Product Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control rounded-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Product Model</label>
                        <input type="text" name="model" id="edit_model" class="form-control rounded-0">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Quantity</label>
                        <input type="number" name="quantity" id="edit_quantity" class="form-control rounded-0" required min="0">
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
                    <button type="submit" name="edit_product" class="btn btn-primary-custom">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#productTable').DataTable({
            responsive: true,
            pageLength: 10,
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });
        
        $('.edit-btn').on('click', function() {
            $('#edit_product_id').val($(this).data('id'));
            $('#edit_name').val($(this).data('name'));
            $('#edit_model').val($(this).data('model'));
            $('#edit_quantity').val($(this).data('quantity'));
            $('#edit_category_id').val($(this).data('category-id'));
            $('#edit_brand_id').val($(this).data('brand-id'));
            $('#edit_supplier_id').val($(this).data('supplier-id'));
            $('#edit_status').val($(this).data('status')); 
        });
    });
</script>

<?php include 'footer.php'; ?>
<?php ob_end_flush(); ?>
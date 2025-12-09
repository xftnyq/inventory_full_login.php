<?php 
include 'header.php'; 

$total_products_res = @$conn->query("SELECT SUM(quantity) FROM products");
$total_products = $total_products_res ? ($total_products_res->fetch_row()[0] ?? 0) : 0;

$total_suppliers_res = @$conn->query("SELECT COUNT(id) FROM suppliers");
$total_suppliers = $total_suppliers_res ? ($total_suppliers_res->fetch_row()[0] ?? 0) : 0;

$total_customers_res = @$conn->query("SELECT COUNT(id) FROM customers");
$total_customers = $total_customers_res ? ($total_customers_res->fetch_row()[0] ?? 0) : 0;

$low_stock_res = @$conn->query("SELECT COUNT(id) FROM products WHERE quantity < 10");
$low_stock_count = $low_stock_res ? ($low_stock_res->fetch_row()[0] ?? 0) : 0;

$total_brands_res = @$conn->query("SELECT COUNT(id) FROM brands");
$total_brands_count = $total_brands_res ? ($total_brands_res->fetch_row()[0] ?? 0) : 0;

$latest_products_res = @$conn->query("SELECT name, model, quantity FROM products ORDER BY id DESC LIMIT 5");
$latest_orders_res = @$conn->query("SELECT o.id, p.name AS product_name, o.total_item FROM orders o LEFT JOIN products p ON o.product_id = p.id ORDER BY o.id DESC LIMIT 5");
?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                Item List
                <a href="product.php" class="text-primary small">View All</a>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Model</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($latest_products_res && $latest_products_res->num_rows > 0) {
                            while($row = $latest_products_res->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>'.$row['name'].'</td>';
                                echo '<td>'.$row['model'].'</td>';
                                echo '<td>'.$row['quantity'].'</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="3" class="text-center text-muted">No products found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                Asset List (Latest Orders)
                <a href="order.php" class="text-primary small">View All</a>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($latest_orders_res && $latest_orders_res->num_rows > 0) {
                            while($row = $latest_orders_res->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>#'.$row['id'].'</td>';
                                echo '<td>'.$row['product_name'].'</td>';
                                echo '<td>'.$row['total_item'].'</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="3" class="text-center text-muted">No recent orders.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <h5 class="card-title fs-6">Item Summary</h5>
            <div class="d-flex justify-content-around mt-3">
                <div>
                    <i class="fas fa-boxes fa-2x text-primary-custom"></i>
                    <p class="mb-0 mt-2 fw-bold"><?php echo $total_products; ?></p>
                    <small class="text-muted">Total Items</small>
                </div>
                <div>
                    <i class="fas fa-users fa-2x text-secondary"></i>
                    <p class="mb-0 mt-2 fw-bold"><?php echo $total_suppliers; ?></p>
                    <small class="text-muted">Total Suppliers</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3 text-center">
            <h5 class="card-title fs-6">Product Summary</h5>
            <div class="d-flex justify-content-around mt-3">
                <div>
                    <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                    <p class="mb-0 mt-2 fw-bold"><?php echo $low_stock_count; ?></p>
                    <small class="text-muted">Low Stock</small>
                </div>
                <div>
                    <i class="fas fa-tags fa-2x text-info"></i>
                    <p class="mb-0 mt-2 fw-bold"><?php echo $total_brands_count; ?></p>
                    <small class="text-muted">Total Brands</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <h5 class="card-title fs-6">Total Items</h5>
            <div class="d-flex justify-content-around mt-3">
                <div>
                    <i class="fas fa-cubes fa-2x text-warning"></i>
                    <p class="mb-0 mt-2 fw-bold"><?php echo $total_products; ?></p>
                    <small class="text-muted">Total Products</small>
                </div>
                <div>
                    <i class="fas fa-handshake fa-2x text-success"></i>
                    <p class="mb-0 mt-2 fw-bold"><?php echo $total_customers; ?></p>
                    <small class="text-muted">Total Customers</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <h5 class="card-title fs-6">Actions</h5>
            <div class="d-flex justify-content-around mt-3">
                <a href="order.php" class="btn btn-primary-custom btn-sm">New Order</a>
                <a href="purchase.php" class="btn btn-secondary-custom btn-sm">New Purchase</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<?php
// ---------------------------------------------
// DATABASE CONNECTION
// ---------------------------------------------
$mysqli = new mysqli("localhost", "root", "", "ak-store");

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

/* -----------------------------------------------
   FUNCTION: ADD PRODUCT WITH STOCK
------------------------------------------------ */
function addStockItem($name, $category, $price, $quantity, $imgFile) {
    global $mysqli;

    $img = $imgFile['name'];
    $target = "images/" . basename($img);

    // Upload image first
    if (move_uploaded_file($imgFile['tmp_name'], $target)) {
        
        // Insert into DB
        $stmt = $mysqli->prepare(
            "INSERT INTO stock (name, category, price, img, quantity) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssdsi", $name, $category, $price, $img, $quantity);

        return $stmt->execute();
    }

    return false;
}


/* -----------------------------------------------
   HANDLE FORM SUBMIT
------------------------------------------------ */
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'];
    $category = $_POST['category'];
    $price    = $_POST['price'];
    $quantity = $_POST['quantity'];

    if (addStockItem($name, $category, $price, $quantity, $_FILES['img'])) {
        $message = "<p style='color:green;'>Stock added successfully!</p>";
    } else {
        $message = "<p style='color:red;'>Image upload failed.</p>";
    }
}


/* -----------------------------------------------
   FETCH ALL STOCK ITEMS
------------------------------------------------ */
$items = $mysqli->query("SELECT * FROM stock");

?>

<!-- -----------------------------------------------------
     ADD STOCK FORM
------------------------------------------------------ -->
<h2>Add New Stock</h2>
<?= $message ?>

<form method="POST" enctype="multipart/form-data">
    <label>Name:</label>
    <input type="text" name="name" required><br><br>

    <label>Category:</label>
    <select name="category" required>
        <option value="men">Men</option>
        <option value="women">Women</option>
        <option value="kids">Kids</option>
        <option value="sales">Sales</option>
    </select><br><br>

    <label>Price:</label>
    <input type="number" step="0.01" name="price" required><br><br>

    <label>Quantity:</label>
    <input type="number" name="quantity" required><br><br>

    <label>Image:</label>
    <input type="file" name="img" required><br><br>

    <button type="submit">Add Stock</button>
</form>

<hr>

<!-- -----------------------------------------------------
     DISPLAY STOCK ITEMS
------------------------------------------------------ -->
<h2>Available Stock</h2>

<div style="display:flex; flex-wrap:wrap; gap:20px;">

<?php while($row = $items->fetch_assoc()): ?>
    <div style="border:1px solid #ccc; padding:10px; width:200px;">
        
        <img src="images/<?= $row['img'] ?>" 
             style="width:100%; height:200px; object-fit:cover;">
        
        <strong><?= $row['name'] ?></strong><br>

        Category: <?= ucfirst($row['category']) ?><br>

        Price: $<?= $row['price'] ?><br>

        Quantity: <?= $row['quantity'] ?><br>
    </div>
<?php endwhile; ?>

</div>

<?php
session_start();

echo '<pre>';
var_dump($_SESSION);
echo '</pre>';

// Your role check here
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    echo "Redirecting because role is missing or not user";
    exit; // Don't redirect yet, just exit for debug
    // header("Location: login.html");
    // exit;
}
?>


<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

$conn = new mysqli("localhost", "root", "", "ak-store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Approve/Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'];
    
    if (isset($_POST['approve'])) {
        $conn->query("UPDATE orders SET status='Approved' WHERE id=$orderId");
    } elseif (isset($_POST['delete'])) {
        $conn->query("DELETE FROM orders WHERE id=$orderId");
    }
}

// Fetch orders
$sql = "
    SELECT signup.orders.*, signup.user.firstname 
    FROM signup.orders 
    JOIN signup.user ON signup.orders.user_id = signup.user.id
    ORDER BY signup.orders.order_date DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Orders</title>
    <style>
        body {
            background-color: #fff8e1;
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
            font-size: 2.5rem;
        }

        table {
            margin: 0 auto;
            width: 95%;
            border-collapse: collapse;
            font-size: 1rem;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            border: 1px solid #999;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #f0e68c;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        form {
            display: inline;
        }

        button {
            padding: 6px 12px;
            margin: 2px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .approve {
            background-color: #28a745;
            color: white;
        }

        .delete {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>

<h1>🧾 All Orders</h1>

<table>
    <tr>
        <th>ID</th>
        <th>User</th>
        <th>Product</th>
        <th>Qty</th>
        <th>Total</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td><?= htmlspecialchars($row['firstname']) ?></td>
        <td><?= htmlspecialchars($row['product_name']) ?></td>
        <td><?= htmlspecialchars($row['quantity']) ?></td>
        <td>Rs. <?= htmlspecialchars($row['total_price']) ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
        <td><?= htmlspecialchars($row['order_date']) ?></td>
        <td>
            <?php if ($row['status'] !== 'Approved'): ?>
                <form method="POST">
                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                    <button type="submit" name="approve" class="approve">Approve</button>
                </form>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                <button type="submit" name="delete" class="delete" onclick="return confirm('Delete this order?')">Delete</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>

<?php $conn->close(); ?>

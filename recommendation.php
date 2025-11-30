<?php
session_start();
require_once 'db_connect.php';

/** Helper: safe html escape */
function h($s){ return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

/** 1) Redirect if total sold across products > 30 */
$totalSold = 0;
$resTotal = $conn->query("SELECT SUM(sold) AS total_sold FROM recommendation_clothes");
if ($resTotal !== false) {
    $rowTotal = $resTotal->fetch_assoc();
    $totalSold = (int)($rowTotal['total_sold'] ?? 0);
} else {
    error_log("recommendation.php: SUM(sold) query failed: " . $conn->error);
}
if ($totalSold > 30) {
    header("Location: index.php");
    exit;
}

/** 2) Session initialization & restart logic */
if (!isset($_SESSION['step'])) $_SESSION['step'] = 1;
if (!isset($_SESSION['recommendation'])) $_SESSION['recommendation'] = [];

if (isset($_GET['restart']) && $_GET['restart'] == 1) {
    $_SESSION['step'] = 1;
    $_SESSION['recommendation'] = [];
    header("Location: recommendation.php");
    exit;
}

/** 3) Handle POST submissions */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $k => $v) {
        if (in_array($k, ['skin_tone','color','body_type','gender','category'])) {
            $_SESSION['recommendation'][$k] = trim($v);
        }
    }
    $_SESSION['step'] = ($_SESSION['step'] ?? 1) + 1;
    header("Location: recommendation.php");
    exit;
}

$step = $_SESSION['step'] ?? 1;
$rec = $_SESSION['recommendation'] ?? [];

/** 4) Fetch recommendations at final step (step 6) */
$recommendedClothes = [];
if ($step === 6) {
    $skin_tone = $rec['skin_tone'] ?? '';
    $color = $rec['color'] ?? '';
    $body_type = $rec['body_type'] ?? '';
    $gender = $rec['gender'] ?? '';
    $category = $rec['category'] ?? 'all';

    if ($category === 'all') {
        $sql = "SELECT * FROM recommendation_clothes WHERE skin_tone=? AND color=? AND body_type=? AND gender=?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssss", $skin_tone, $color, $body_type, $gender);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) $recommendedClothes[] = $row;
                $res->free();
            }
            $stmt->close();
        }
    } else {
        $sql = "SELECT * FROM recommendation_clothes WHERE skin_tone=? AND color=? AND body_type=? AND gender=? AND category=?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssss", $skin_tone, $color, $body_type, $gender, $category);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) $recommendedClothes[] = $row;
                $res->free();
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Style Your Dream Style</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
    nav {
        background: #111;
        padding: 8px 16px;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
    }

    nav ul {
        list-style: none;
        display: flex;
        gap: 12px;
        margin: 0;
        padding: 0;
    }

    nav a {
        color: #fff;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 4px;
        font-weight: bold;
        transition: 0.3s;
    }

    nav a.active,
    nav a:hover {
        background: #d4af37;
        color: #000;
    }

    .user-info {
        position: absolute;
        right: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #d4af37;
    }

    .user-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 2px solid #d4af37;
        background: url('uploads/user_icon.png') center/cover no-repeat;
        cursor: pointer;
    }

    .container {
        max-width: 1000px;
        margin: 24px auto;
        background: #fff;
        /* White quiz container */
        padding: 22px;
        border-radius: 8px;
        border: 2px solid #d4af37;
    }

    h2,
    h3 {
        text-align: center;
        color: #d4af37;
        margin-top: 0;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 12px;
        align-items: center;
    }

    .cards {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        justify-content: center;
    }

    .card {
        width: 200px;
        background: #f8f8f8;
        border-radius: 8px;
        padding: 10px;
        box-shadow: 0 2px 8px rgba(212, 175, 55, 0.4);
        text-align: center;
        transition: 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(212, 175, 55, 0.6);
    }

    .card img {
        width: 100%;
        height: 140px;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid #d4af37;
    }

    select,
    button {
        padding: 10px;
        font-size: 16px;
        width: 60%;
        border-radius: 6px;
        border: 2px solid #d4af37;
        background: #000;
        color: #d4af37;
        cursor: pointer;
        transition: 0.3s;
    }

    button:hover {
        background: #d4af37;
        color: #000;
        border: 2px solid #fff;
    }

    .product-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        justify-content: center;
        margin-top: 18px;
    }

    .product {
        width: 220px;
        padding: 10px;
        border-radius: 8px;
        text-align: center;
        background: #f8f8f8;
        box-shadow: 0 2px 8px rgba(212, 175, 55, 0.4);
    }

    .product img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid #d4af37;
    }

    .add-cart {
        margin-top: 8px;
        padding: 8px 12px;
        background: #d4af37;
        color: #000;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: 0.3s;
    }

    .add-cart:hover {
        background: #fff;
        color: #d4af37;
    }

    .small {
        font-size: 0.9rem;
        color: #666;
    }

    .center {
        text-align: center;
    }

    @media (max-width:600px) {

        select,
        button {
            width: 92%;
        }

        nav .user-info {
            right: 8px;
        }
    }
    </style>
</head>

<body>
    <nav>
        <ul>
            <li><a href="main.php">Home</a></li>
            <li><a class="active" href="recommendation.php">Recommend</a></li>
            <li><a href="men.php">Men</a></li>
            <li><a href="women.php">Women</a></li>
            <li><a href="kids.php">Kids</a></li>
            <li><a href="sale.php">Sale</a></li>
            <li><a href="cart.php">Cart</a></li>
        </ul>
        <?php if(isset($_SESSION['user_name'])): ?>
        <li class="user-menu" style="position:relative;">
            <a href="#" id="userIcon" style="font-size:18px;">👤</a>
            <div id="dropdown"
                style="display:none; position:absolute; top:35px; right:0; background:#fff; border:1px solid #FFD700; border-radius:5px; min-width:160px; z-index:1000;">
                <a href="notification.php" style="display:block; padding:8px 12px; text-decoration:none; color:#000;">🔔
                    Notifications</a>
                <a href="logout.php"
                    style="display:block; padding:8px 12px; text-decoration:none; color:#000;">Logout</a>
            </div>
        </li>
        <?php else: ?>
        <li><a class="login" href="login.html">Login</a></li>
        <li><a class="signup" href="signup.html">Signup</a></li>
        <?php endif; ?>

    </nav>

    <div class="container">
        <h2>Style Your Dream Style</h2>

        <?php if ($step === 1): ?>
        <!-- Step 1: Skin Tone -->
        <form method="POST">
            <label class="small">Select your skin tone:</label>
            <div class="cards">
                <?php
                $tones = [
                    'fair'=>['label'=>'Fair','img'=>'uploads/skin_fair.jpg'],
                    'medium'=>['label'=>'Medium','img'=>'uploads/skin_medium.jpg'],
                    'olive'=>['label'=>'Olive','img'=>'uploads/skin_olive.jpg'],
                    'dark'=>['label'=>'Dark','img'=>'uploads/skin_dark.jpg'],
                ];
                foreach($tones as $key=>$info):
                ?>
                <label class="card">
                    <img src="<?php echo h($info['img']); ?>" alt="<?php echo h($info['label']); ?>">
                    <div><?php echo h($info['label']); ?></div>
                    <input type="radio" name="skin_tone" value="<?php echo h($key); ?>" required
                        <?php if(isset($rec['skin_tone']) && $rec['skin_tone']==$key) echo 'checked'; ?>>
                </label>
                <?php endforeach; ?>
            </div>
            <button type="submit">Next</button>
        </form>

        <?php elseif ($step === 2): ?>
        <!-- Step 2: Color -->
        <form method="POST">
            <label class="small">Choose a color that suits your skin tone:</label>
            <?php
            $colorsByTone = [
                'fair'=>[['value'=>'White','label'=>'White','img'=>'uploads/color_white.jpg'],
                         ['value'=>'Pastel Pink','label'=>'Pastel Pink','img'=>'uploads/color_pastel_pink.jpg'],
                         ['value'=>'Light Blue','label'=>'Light Blue','img'=>'uploads/color_light_blue.jpg'],
                         ['value'=>'Beige','label'=>'Beige','img'=>'uploads/color_beige.jpg']],
                'medium'=>[['value'=>'Coral','label'=>'Coral','img'=>'uploads/color_coral.jpg'],
                           ['value'=>'Olive','label'=>'Olive','img'=>'uploads/color_olive.jpg'],
                           ['value'=>'Navy','label'=>'Navy','img'=>'uploads/color_navy.jpg'],
                           ['value'=>'Turquoise','label'=>'Turquoise','img'=>'uploads/color_turquoise.jpg']],
                'olive'=>[['value'=>'Orange','label'=>'Orange','img'=>'uploads/color_orange.jpg'],
                          ['value'=>'Warm Brown','label'=>'Warm Brown','img'=>'uploads/color_warm_brown.jpg'],
                          ['value'=>'Khaki','label'=>'Khaki','img'=>'uploads/color_khaki.jpg'],
                          ['value'=>'Mustard','label'=>'Mustard','img'=>'uploads/color_mustard.jpg']],
                'dark'=>[['value'=>'Bright Yellow','label'=>'Bright Yellow','img'=>'uploads/color_bright_yellow.jpg'],
                         ['value'=>'Royal Blue','label'=>'Royal Blue','img'=>'uploads/color_royal_blue.jpg'],
                         ['value'=>'Hot Pink','label'=>'Hot Pink','img'=>'uploads/color_hot_pink.jpg'],
                         ['value'=>'White','label'=>'White','img'=>'uploads/color_white.jpg']],
            ];
            $chosenTone = $rec['skin_tone'] ?? '';
            $options = $colorsByTone[$chosenTone] ?? [];
            ?>
            <div class="cards">
                <?php foreach($options as $opt): ?>
                <label class="card">
                    <img src="<?php echo h($opt['img']); ?>" alt="<?php echo h($opt['label']); ?>">
                    <div><?php echo h($opt['label']); ?></div>
                    <input type="radio" name="color" value="<?php echo h($opt['value']); ?>" required
                        <?php if(isset($rec['color']) && $rec['color']==$opt['value']) echo 'checked'; ?>>
                </label>
                <?php endforeach; ?>
            </div>
            <button type="submit">Next</button>
        </form>

        <?php elseif ($step === 3): ?>
        <!-- Step 3: Body Type -->
        <form method="POST">
            <label class="small">Select your body type:</label>
            <div class="cards">
                <?php
                $bodies = [
                    'rectangle'=>['label'=>'Rectangle','img'=>'uploads/body_rectangle.jpg'],
                    'inverted_triangle'=>['label'=>'Inverted Triangle','img'=>'uploads/body_inverted_triangle.jpg'],
                    'hourglass'=>['label'=>'Hourglass','img'=>'uploads/body_hourglass.jpg'],
                    'pear'=>['label'=>'Pear','img'=>'uploads/body_pear.jpg'],
                    'apple'=>['label'=>'Apple','img'=>'uploads/body_apple.jpg']
                ];
                foreach($bodies as $k=>$v):
                ?>
                <label class="card">
                    <img src="<?php echo h($v['img']); ?>" alt="<?php echo h($v['label']); ?>">
                    <div><?php echo h($v['label']); ?></div>
                    <input type="radio" name="body_type" value="<?php echo h($k); ?>" required
                        <?php if(isset($rec['body_type']) && $rec['body_type']==$k) echo 'checked'; ?>>
                </label>
                <?php endforeach; ?>
            </div>
            <button type="submit">Next</button>
        </form>

        <?php elseif ($step === 4): ?>
        <!-- Step 4: Gender -->
        <form method="POST">
            <label class="small">Select your gender:</label>
            <div class="cards">
                <?php
                $genders = [
                    'male'=>['label'=>'Male','img'=>'uploads/gender_male.jpg'],
                    'female'=>['label'=>'Female','img'=>'uploads/gender_female.jpg'],
                    'kids'=>['label'=>'Kids','img'=>'uploads/gender_kids.jpg']
                ];
                foreach($genders as $k=>$v):
                ?>
                <label class="card">
                    <img src="<?php echo h($v['img']); ?>" alt="<?php echo h($v['label']); ?>">
                    <div><?php echo h($v['label']); ?></div>
                    <input type="radio" name="gender" value="<?php echo h($k); ?>" required
                        <?php if(isset($rec['gender']) && $rec['gender']==$k) echo 'checked'; ?>>
                </label>
                <?php endforeach; ?>
            </div>
            <button type="submit">Next</button>
        </form>

        <?php elseif ($step === 5): ?>
        <!-- Step 5: Category -->
        <form method="POST">
            <label class="small">Select category:</label>
            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                <?php
                $categories = [
                    'top'=>['label'=>'Top','img'=>'uploads/top_sample.jpg'],
                    'bottom'=>['label'=>'Bottom','img'=>'uploads/bottom_sample.jpg'],
                    'footwear'=>['label'=>'Footwear','img'=>'uploads/footwear_sample.jpg'],
                    'all'=>['label'=>'All','img'=>'uploads/all_sample.jpg']
                ];
                foreach($categories as $k=>$v):
                ?>
                <label class="card" style="width:150px;">
                    <img src="<?php echo h($v['img']); ?>" alt="<?php echo h($v['label']); ?>" style="height:110px;">
                    <div><?php echo h($v['label']); ?></div>
                    <input type="radio" name="category" value="<?php echo h($k); ?>" required
                        <?php if(isset($rec['category']) && $rec['category']==$k) echo 'checked'; ?>>
                </label>
                <?php endforeach; ?>
            </div>
            <button type="submit">See Recommendations</button>
        </form>

        <?php elseif ($step === 6): ?>
        <!-- Step 6: Show Recommendations -->
        <h3 class="center">Recommended Clothes for You</h3>
        <div class="product-grid">
            <?php if (!empty($recommendedClothes)): ?>
            <?php foreach ($recommendedClothes as $row):
                    $product_name = $row['product_name'] ?? $row['name'] ?? 'No Name';
                    $img = !empty($row['image']) ? 'uploads/' . $row['image'] : 'uploads/all_sample.jpg';
                    $price = $row['price'] ?? 0;
                    $sold = (int)($row['sold'] ?? 0);
                    $category = $row['category'] ?? 'N/A';
                    $product_id = $row['id'] ?? $row['product_id'] ?? 0;
                    $table_name = 'recommendation_clothes';
                    $rating = $sold<20?2:($sold<30?3:($sold<40?4:5));
                ?>
            <div class="product">
                <img src="<?php echo h($img); ?>" alt="<?php echo h($product_name); ?>">
                <h3><?php echo h($product_name); ?></h3>
                <p>Rs. <?php echo number_format($price,2); ?></p>
                <p class="small">Category: <?php echo h(ucfirst($category)); ?></p>
                <p class="small">
                    <?php for($i=1;$i<=5;$i++): ?>
                    <?php echo ($i<=$rating)?"⭐":"☆"; ?>
                    <?php endfor; ?>
                </p>
                <form method="POST" action="add_to_cart.php">
                    <input type="hidden" name="product_id" value="<?php echo (int)$product_id; ?>">
                    <input type="hidden" name="table" value="<?php echo h($table_name); ?>">
                    <input type="hidden" name="redirect_back" value="cart.php">
                    <button type="submit" class="add-cart">Add to Cart</button>
                </form>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p class="center">No recommendations found for your selections.</p>
            <?php endif; ?>
        </div>
        <p class="center" style="margin-top:16px;">
            <a href="recommendation.php?restart=1"><button>Take Quiz Again</button></a>
        </p>
        <?php endif; ?>
    </div>
    <!-- usericon -->
    <script>
    const userIcon = document.getElementById('userIcon');
    const dropdown = document.getElementById('dropdown');
    if (userIcon) {
        userIcon.addEventListener('click', function(e) {
            e.preventDefault();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', function(e) {
            if (!userIcon.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }
    </script>
</body>

</html>
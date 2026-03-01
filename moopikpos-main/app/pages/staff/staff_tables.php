<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
staff_require_login();
require __DIR__ . '/staff_layout.php';

// อัปเดตสถานะโต๊ะอัตโนมัติ (เช็คจากตาราง orders ว่ามีบิลค้างอยู่ไหม)
$update_tables = $pdo->query(" 
    UPDATE tables t 
    SET status = CASE 
        WHEN EXISTS (SELECT 1 FROM orders o WHERE o.table_no = t.table_no AND o.status != 'completed' AND o.payment_status = 'unpaid') 
        THEN 'occupied' 
        ELSE 'available' 
    END
");

// ดึงข้อมูลโต๊ะทั้งหมดมาแสดง
$stmt = $pdo->query("SELECT * FROM tables ORDER BY id ASC");
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

staff_layout_start('แผนผังโต๊ะ', 'แผนผังโต๊ะ (Table Status)', 'สถานะโต๊ะปัจจุบันในร้าน');
?>

<style>
    .table-box {
        height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        border-radius: 10px;
        text-decoration: none;
        color: white !important;
        font-size: 1.5rem;
        font-weight: bold;
        transition: transform 0.2s;
    }
    .table-box:hover {
        transform: scale(1.05);
    }
    .box-available { background-color: #28a745; box-shadow: 0 4px 6px rgba(40,167,69,0.3); }
    .box-occupied { background-color: #dc3545; box-shadow: 0 4px 6px rgba(220,53,69,0.3); }
</style>

<div class="container mt-4">
    <h2>แผนผังโต๊ะ (Table Status)</h2>
    <hr>
    <div class="row mt-4">
        <?php foreach($tables as $table): ?>
            <div class="col-md-3 col-sm-4 col-6 mb-4">
                <?php if($table['status'] === 'available'): ?>
                    <a href="staff_order.php?table=<?= $table['table_no'] ?>" class="table-box box-available">
                        <?= $table['table_no'] ?>
                        <span style="font-size: 0.9rem; font-weight: normal;">ว่าง (Available)</span>
                    </a>
                <?php else: ?>
                    <a href="staff_requests.php?table=<?= $table['table_no'] ?>" class="table-box box-occupied">
                        <?= $table['table_no'] ?>
                        <span style="font-size: 0.9rem; font-weight: normal;">มีลูกค้า (Occupied)</span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php staff_layout_end(); ?>
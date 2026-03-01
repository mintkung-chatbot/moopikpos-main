<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/admin_auth.php';
staff_require_login();

require __DIR__ . '/staff_layout.php';

$selectedTableNo = '';
if (isset($_GET['table'])) {
    $candidateTableNo = trim((string) $_GET['table']);
    if ($candidateTableNo !== '') {
        $tableCheckStmt = $pdo->prepare("SELECT table_no FROM tables WHERE table_no = ? LIMIT 1");
        $tableCheckStmt->execute([$candidateTableNo]);
        $validTable = $tableCheckStmt->fetchColumn();
        if ($validTable !== false) {
            $selectedTableNo = (string) $validTable;
        }
    }
}

// ดึงข้อมูลเหมือนเดิม
$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
$products = $pdo->query("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status = 'active' ORDER BY p.category_id, p.id")->fetchAll();

$extraHead = '<style>
.menu-card { cursor: pointer; transition: 0.2s; border: none; border-radius: 15px; overflow: hidden; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.menu-card:active { transform: scale(0.95); }
.menu-img { height: 120px; object-fit: cover; width: 100%; }
.cart-panel { background: white; height: calc(100vh - 69px); position: fixed; right: 0; top: 69px; width: 350px; box-shadow: -5px 0 15px rgba(0,0,0,0.05); display: flex; flex-direction: column; z-index: 1000; }
.cart-items { flex-grow: 1; overflow-y: auto; padding: 15px; background: #f8f9fa; }
.cart-item { background: white; border-radius: 10px; padding: 10px; margin-bottom: 10px; position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
@media (max-width: 768px) {
    .cart-panel { width: 100%; height: auto; max-height: 70vh; bottom: 0; top: auto; border-top-left-radius: 20px; border-top-right-radius: 20px; }
    .cart-items { max-height: 300px; display: none; }
    .cart-panel.open .cart-items { display: block; }
    body { padding-bottom: 80px; }
}
</style>';

staff_layout_start('สั่งอาหารหน้าร้าน', 'รับออเดอร์หน้าร้าน', 'เลือกเมนู ใส่ตัวเลือก และยืนยันออเดอร์', $extraHead);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 col-lg-9 p-4">
            <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                <div class="alert alert-success py-2">บันทึกออเดอร์เรียบร้อยแล้ว</div>
            <?php endif; ?>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'empty'): ?>
                <div class="alert alert-danger py-2">ไม่สามารถส่งออเดอร์ว่างได้</div>
            <?php endif; ?>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'missing_table'): ?>
                <div class="alert alert-danger py-2">กรุณาระบุเบอร์โต๊ะสำหรับออเดอร์ทานที่ร้าน</div>
            <?php endif; ?>

            <!-- 🔔 Active Orders Status Widget -->
            <div class="alert alert-info p-3 mb-4" id="activeOrdersWidget" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-fire"></i> สถานะออเดอร์ที่ทำ</h5>
                    <div class="badge bg-danger" id="readyBadge" style="display:none; font-size: 1rem;">
                        <i class="fas fa-bell"></i> <span id="readyCount">0</span> พร้อมแล้ว
                    </div>
                </div>
                
                <!-- Cooking Orders -->
                <div class="mb-3" id="cookingOrders" style="display:none;">
                    <strong class="text-primary">🍳 กำลังทำ:</strong>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <!-- Items will be inserted here -->
                    </div>
                </div>

                <!-- Ready Orders -->
                <div id="readyOrders" style="display:none;">
                    <strong class="text-success">✅ พร้อมแล้ว (ส่งให้ลูกค้า):</strong>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <!-- Items will be inserted here -->
                    </div>
                </div>
            </div>

            <h4 class="mb-4"><i class="fas fa-utensils text-primary"></i> เลือกรายการอาหาร</h4>
            
            <ul class="nav nav-pills mb-4" id="pills-tab">
                <li class="nav-item"><a class="nav-link active rounded-pill" href="#" onclick="filterCat('all')">ทั้งหมด</a></li>
                <?php foreach ($cats as $c): ?>
                    <li class="nav-item"><a class="nav-link rounded-pill" href="#" onclick="filterCat('<?php echo $c['name']; ?>')"><?php echo $c['name']; ?></a></li>
                <?php endforeach; ?>
            </ul>

            <div class="row g-3">
                <?php foreach ($products as $p): ?>
                <div class="col-6 col-md-4 col-lg-3 menu-item" data-cat="<?php echo $p['cat_name']; ?>">
                    <div class="menu-card h-100" onclick='openOptionModal(<?php echo json_encode($p); ?>)'>
                        <img src="<?php echo strpos($p['image_url'], 'http') === 0 ? $p['image_url'] : 'uploads/'.$p['image_url']; ?>" class="menu-img">
                        <div class="p-3">
                            <h6 class="fw-bold mb-1"><?php echo $p['name']; ?></h6>
                            <div class="d-flex justify-content-between text-primary fw-bold">
                                <span><?php echo number_format($p['price'], 0); ?>.-</span>
                                <i class="fas fa-plus-circle fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-md-4 col-lg-3 cart-panel" id="cartPanel">
            <div class="p-3 bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-shopping-basket"></i> รายการที่สั่ง</h5>
                <span class="badge bg-white text-primary" id="cartCount">0</span>
            </div>
            
            <div class="cart-items" id="cartItems">
                <div class="text-center text-muted mt-5">ยังไม่มีรายการ</div>
            </div>

            <div class="p-3 bg-white border-top">
                <form action="<?php echo staff_escape(staff_url('process_order.php')); ?>" method="POST" id="checkoutForm">
                    <input type="hidden" name="cart_data" id="cartDataInput">
                    
                    <div class="mb-2">
                        <select name="order_type" id="orderTypeInput" class="form-select form-select-sm mb-1">
                            <option value="dine_in">🍽️ ทานที่ร้าน</option>
                            <option value="takeaway">🥡 กลับบ้าน</option>
                        </select>
                        <div id="tableNoGroup">
                            <input
                                type="text"
                                name="table_no"
                                id="tableNoInput"
                                class="form-control form-control-sm mb-1"
                                placeholder="เบอร์โต๊ะ"
                                value="<?php echo htmlspecialchars($selectedTableNo, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo $selectedTableNo !== '' ? 'readonly' : ''; ?>
                            >
                            <?php if ($selectedTableNo !== ''): ?>
                                <div class="form-text mt-0 mb-1">เลือกจากผังโต๊ะแล้ว (โต๊ะ <?php echo htmlspecialchars($selectedTableNo, ENT_QUOTES, 'UTF-8'); ?>)</div>
                            <?php endif; ?>
                        </div>
                        <input type="text" name="customer_info" class="form-control form-control-sm" placeholder="ชื่อลูกค้า">
                    </div>

                    <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                        <span>รวมสุทธิ</span>
                        <span class="text-primary" id="totalPrice">0 ฿</span>
                    </div>
                    <button type="button" onclick="submitOrder()" class="btn btn-success w-100 py-2 fw-bold">✅ ยืนยันออเดอร์</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="optionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">ชื่อเมนู</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalProductId">
                <input type="hidden" id="modalBasePrice">
                <input type="hidden" id="modalProductName">

                <div class="mb-3" id="spicyOption">
                    <label class="fw-bold mb-2">🌶️ ระดับความเผ็ด</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="spicy" id="spicy1" value="ไม่เผ็ด">
                        <label class="btn btn-outline-danger" for="spicy1">ไม่เผ็ด</label>

                        <input type="radio" class="btn-check" name="spicy" id="spicy2" value="เผ็ดปกติ" checked>
                        <label class="btn btn-outline-danger" for="spicy2">เผ็ดปกติ</label>

                        <input type="radio" class="btn-check" name="spicy" id="spicy3" value="เผ็ดมาก">
                        <label class="btn btn-outline-danger" for="spicy3">เผ็ดมาก 🔥</label>
                    </div>
                </div>

                <div class="mb-3" id="toppingOption">
                    <label class="fw-bold mb-2">🍳 เพิ่มเติม</label>
                    <div class="list-group">
                        <label class="list-group-item">
                            <input class="form-check-input me-1 topping-check" type="checkbox" value="ไข่ดาว" data-price="10">
                            ไข่ดาว (+10.-)
                        </label>
                        <label class="list-group-item">
                            <input class="form-check-input me-1 topping-check" type="checkbox" value="ไข่เจียว" data-price="15">
                            ไข่เจียว (+15.-)
                        </label>
                        <label class="list-group-item">
                            <input class="form-check-input me-1 topping-check" type="checkbox" value="พิเศษ" data-price="20">
                            พิเศษ (+20.-)
                        </label>
                    </div>
                </div>
                
                <div class="mb-3 d-none" id="sweetOption">
                    <label class="fw-bold mb-2">🥤 ความหวาน</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="sweet" id="sw1" value="หวานน้อย">
                        <label class="btn btn-outline-success" for="sw1">หวานน้อย</label>

                        <input type="radio" class="btn-check" name="sweet" id="sw2" value="หวานปกติ" checked>
                        <label class="btn btn-outline-success" for="sw2">ปกติ</label>

                        <input type="radio" class="btn-check" name="sweet" id="sw3" value="หวานมาก">
                        <label class="btn btn-outline-success" for="sw3">หวานมาก</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">📝 หมายเหตุ</label>
                    <input type="text" class="form-control" id="modalNote" placeholder="เช่น ไม่ใส่ผัก, แยกน้ำ">
                </div>
                
                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded">
                    <span>จำนวน</span>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-secondary btn-sm rounded-circle" onclick="adjustModalQty(-1)">-</button>
                        <span class="mx-3 fw-bold fs-5" id="modalQty">1</span>
                        <button class="btn btn-primary btn-sm rounded-circle" onclick="adjustModalQty(1)">+</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100 py-2" onclick="addToCart()">
                    เพิ่มลงตะกร้า (<span id="modalTotalPrice">0</span>.-)
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let cart = []; // เก็บข้อมูลสินค้าในตะกร้า
    let currentModalBasePrice = 0;
    let modalInstance;
    let lastReadyCheckTime = 0;
    const selectedTableNo = <?php echo json_encode($selectedTableNo, JSON_UNESCAPED_UNICODE); ?>;

    // ==================== Auto-poll Ready Orders ====================
    function pollReadyOrders() {
        const apiUrl = '<?php echo staff_url("api/check_ready_orders.php"); ?>';
        fetch(apiUrl + `?last_check=${lastReadyCheckTime}`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const widget = document.getElementById('activeOrdersWidget');
                    const cookingContainer = document.querySelector('#cookingOrders > .d-flex');
                    const readyContainer = document.querySelector('#readyOrders > .d-flex');
                    
                    console.log('✅ API response:', data); // Debug log

                    // Show widget if there are orders
                    if (data.cooking_count > 0 || data.ready_count > 0) {
                        widget.style.display = 'block';
                    }

                    // Show cooking orders
                    if (data.cooking_count > 0) {
                        document.getElementById('cookingOrders').style.display = 'block';
                        cookingContainer.innerHTML = data.cooking_orders.map(o => `
                            <span class="badge bg-info text-dark" style="font-size: 0.9rem; padding: 8px 12px;">
                                <i class="fas fa-hourglass-start"></i> #${o.table_no}
                            </span>
                        `).join('');
                    } else {
                        document.getElementById('cookingOrders').style.display = 'none';
                    }

                    // Show ready orders with notification
                    if (data.ready_count > 0) {
                        document.getElementById('readyOrders').style.display = 'block';
                        document.getElementById('readyBadge').style.display = 'inline-block';
                        document.getElementById('readyCount').innerText = data.ready_count;
                        
                        readyContainer.innerHTML = data.ready_orders.map(o => `
                            <span class="badge bg-success" style="font-size: 0.9rem; padding: 8px 12px; animation: pulse 1s infinite;">
                                <i class="fas fa-check-circle"></i> #${o.table_no} - ${o.customer_name}
                            </span>
                        `).join('');

                        // Play sound + show notification if NEW ready orders
                        if (data.new_ready_count > 0) {
                            console.log('🔔 New ready orders:', data.new_ready_count); // Debug
                            playReadySound();
                            showReadyNotification(data.new_ready_count, data.new_ready_orders);
                        }
                    } else {
                        document.getElementById('readyOrders').style.display = 'none';
                        document.getElementById('readyBadge').style.display = 'none';
                    }

                    if (data.current_time) {
                        lastReadyCheckTime = data.current_time;
                    }
                } else {
                    console.warn('❌ API error:', data); // Debug
                }
            })
            .catch(error => {
                console.error('❌ Poll error:', error.message);
                console.error('Full error:', error);
            });
    }

    // Play notification sound
    function playReadySound() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            // Ding-ding sound (higher pitch)
            oscillator.frequency.value = 1000;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.4, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
            
            // Second ding
            setTimeout(() => {
                const osc2 = audioContext.createOscillator();
                const gain2 = audioContext.createGain();
                osc2.connect(gain2);
                gain2.connect(audioContext.destination);
                osc2.frequency.value = 1200;
                gain2.gain.setValueAtTime(0.4, audioContext.currentTime);
                gain2.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                osc2.start(audioContext.currentTime);
                osc2.stop(audioContext.currentTime + 0.3);
            }, 150);
        } catch (e) {
            console.log('Audio context not available');
        }
    }

    // Show toast notification
    function showReadyNotification(count, orders) {
        let toastContainer = document.getElementById('notification-toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'notification-toast-container';
            toastContainer.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                z-index: 9999;
            `;
            document.body.appendChild(toastContainer);
        }

        const orderList = orders.map(o => `<div>${o.table_no} - ${o.customer_name}</div>`).join('');
        const toast = document.createElement('div');
        toast.className = 'alert alert-success alert-dismissible fade show';
        toast.role = 'alert';
        toast.innerHTML = `
            <strong style="font-size: 1.2rem;">🎉 ออเดอร์พร้อมแล้ว!</strong><br>
            จำนวน: <strong>${count}</strong> รายการ<br>
            ${orderList}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        toast.style.cssText = `
            min-width: 350px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        `;
        
        toastContainer.appendChild(toast);
        
        // Auto-dismiss after 8 seconds
        setTimeout(() => {
            if (toast.parentNode) toast.remove();
        }, 8000);
    }

    // Pulse animation for CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    `;
    document.head.appendChild(style);

    // Start polling every 4 seconds
    setInterval(pollReadyOrders, 4000);
    
    // Initial check
    pollReadyOrders();

    // ==================== Original Functions ====================

    function syncTableInputByOrderType() {
        const orderTypeInput = document.getElementById('orderTypeInput');
        const tableNoInput = document.getElementById('tableNoInput');
        const tableNoGroup = document.getElementById('tableNoGroup');
        if (!orderTypeInput || !tableNoInput || !tableNoGroup) return;

        const isDineIn = orderTypeInput.value === 'dine_in';
        tableNoGroup.style.display = isDineIn ? 'block' : 'none';
        tableNoInput.required = isDineIn;

        if (isDineIn) {
            if (selectedTableNo !== '') {
                tableNoInput.value = selectedTableNo;
            }
            if (!tableNoInput.hasAttribute('readonly')) {
                tableNoInput.disabled = false;
            }
            return;
        }

        tableNoInput.value = '';
        tableNoInput.required = false;
    }

    document.getElementById('orderTypeInput')?.addEventListener('change', syncTableInputByOrderType);
    syncTableInputByOrderType();


    // 1. เปิด Modal เมื่อกดที่เมนู
    function openOptionModal(product) {
        modalInstance = new bootstrap.Modal(document.getElementById('optionModal'));
        
        // เซ็ตค่าพื้นฐาน
        document.getElementById('modalTitle').innerText = product.name;
        document.getElementById('modalProductId').value = product.id;
        document.getElementById('modalProductName').value = product.name;
        document.getElementById('modalBasePrice').value = product.price;
        document.getElementById('modalQty').innerText = 1;
        document.getElementById('modalNote').value = '';
        
        currentModalBasePrice = parseFloat(product.price);
        
        // รีเซ็ตตัวเลือก
        document.querySelectorAll('.topping-check').forEach(c => c.checked = false);
        document.getElementById('spicy2').checked = true; // Default เผ็ดปกติ
        document.getElementById('sw2').checked = true; // Default หวานปกติ

        // Logic ซ่อน/แสดง Option ตามหมวดหมู่ (ใช้ชื่อหมวดหมู่เช็คแบบง่ายๆ)
        const isDrink = product.cat_name.includes('เครื่องดื่ม');
        if (isDrink) {
            document.getElementById('spicyOption').classList.add('d-none');
            document.getElementById('toppingOption').classList.add('d-none');
            document.getElementById('sweetOption').classList.remove('d-none');
        } else {
            // อาหาร
            document.getElementById('spicyOption').classList.remove('d-none');
            document.getElementById('toppingOption').classList.remove('d-none');
            document.getElementById('sweetOption').classList.add('d-none');
        }

        updateModalPrice();
        modalInstance.show();
    }

    // 2. คำนวณราคาใน Modal (รวมท็อปปิ้ง)
    function updateModalPrice() {
        let price = currentModalBasePrice;
        let qty = parseInt(document.getElementById('modalQty').innerText);

        // บวกค่าท็อปปิ้ง
        document.querySelectorAll('.topping-check:checked').forEach(c => {
            price += parseFloat(c.dataset.price);
        });

        document.getElementById('modalTotalPrice').innerText = (price * qty).toLocaleString();
    }
    
    // Event Listener เวลาติ๊กท็อปปิ้ง ให้คำนวณราคาใหม่
    document.querySelectorAll('.topping-check').forEach(el => {
        el.addEventListener('change', updateModalPrice);
    });

    function adjustModalQty(change) {
        let q = parseInt(document.getElementById('modalQty').innerText) + change;
        if(q < 1) q = 1;
        document.getElementById('modalQty').innerText = q;
        updateModalPrice();
    }

    // 3. เพิ่มลงตะกร้า (เก็บข้อมูลแบบละเอียด)
    function addToCart() {
        const id = document.getElementById('modalProductId').value;
        const name = document.getElementById('modalProductName').value;
        const qty = parseInt(document.getElementById('modalQty').innerText);
        const noteText = document.getElementById('modalNote').value;
        
        // รวบรวม Options ที่เลือก
        let options = [];
        let pricePerUnit = currentModalBasePrice;

        // เช็คว่าเปิด Option ไหนอยู่
        if (!document.getElementById('spicyOption').classList.contains('d-none')) {
            options.push(document.querySelector('input[name="spicy"]:checked').value);
        }
        if (!document.getElementById('sweetOption').classList.contains('d-none')) {
            options.push(document.querySelector('input[name="sweet"]:checked').value);
        }
        
        // ท็อปปิ้ง
        document.querySelectorAll('.topping-check:checked').forEach(c => {
            options.push(c.value);
            pricePerUnit += parseFloat(c.dataset.price);
        });

        // สร้าง Object สินค้า
        const item = {
            id: id,
            name: name,
            price: pricePerUnit,
            qty: qty,
            options: options.join(', '), // แปลง array เป็น string "เผ็ดปกติ, ไข่ดาว"
            note: noteText
        };

        cart.push(item);
        renderCart();
        modalInstance.hide();
    }

    // 4. แสดงผลตะกร้าด้านขวา
    function renderCart() {
        const container = document.getElementById('cartItems');
        const countSpan = document.getElementById('cartCount');
        const totalSpan = document.getElementById('totalPrice');
        
        container.innerHTML = '';
        let total = 0;
        let count = 0;

        cart.forEach((item, index) => {
            let itemTotal = item.price * item.qty;
            total += itemTotal;
            count += item.qty;

            container.innerHTML += `
                <div class="cart-item">
                    <div class="d-flex justify-content-between fw-bold">
                        <span>${item.name}</span>
                        <span>${itemTotal}.-</span>
                    </div>
                    <div class="text-muted small">
                        ${item.options ? '<span class="text-primary">'+item.options+'</span>' : ''}
                        ${item.note ? '<br><span class="text-danger">Note: '+item.note+'</span>' : ''}
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">x${item.qty} (${item.price}/หน่วย)</small>
                        <button class="btn btn-sm btn-outline-danger py-0" onclick="removeFromCart(${index})">ลบ</button>
                    </div>
                </div>
            `;
        });

        countSpan.innerText = count;
        totalSpan.innerText = total.toLocaleString() + ' ฿';
        
        // ถ้าไม่มีของ
        if(cart.length === 0) {
            container.innerHTML = '<div class="text-center text-muted mt-5">ยังไม่มีรายการ</div>';
        }
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function filterCat(cat) {
        document.querySelectorAll('.menu-item').forEach(el => {
            if (cat === 'all' || el.dataset.cat === cat) el.style.display = 'block';
            else el.style.display = 'none';
        });
        document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
        event.target.classList.add('active');
    }

    // 5. ส่งข้อมูลไป PHP (แปลงเป็น JSON)
    function submitOrder() {
        const orderTypeInput = document.getElementById('orderTypeInput');
        const tableNoInput = document.getElementById('tableNoInput');

        if(cart.length === 0) {
            alert('กรุณาเลือกรายการอาหารก่อน');
            return;
        }
        if (orderTypeInput && orderTypeInput.value === 'dine_in') {
            const tableNo = tableNoInput ? tableNoInput.value.trim() : '';
            if (tableNo === '') {
                alert('กรุณาระบุเบอร์โต๊ะสำหรับออเดอร์ทานที่ร้าน');
                if (tableNoInput) tableNoInput.focus();
                return;
            }
        }
        if(!confirm('ยืนยันการสั่งออเดอร์?')) return;

        // เอาข้อมูล Cart ใส่ลงใน input hidden ที่เตรียมไว้
        document.getElementById('cartDataInput').value = JSON.stringify(cart);
        document.getElementById('checkoutForm').submit();
    }
</script>

<?php staff_layout_end(); ?>